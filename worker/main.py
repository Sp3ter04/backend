"""FastAPI entry point for the WhisperX alignment worker.

Endpoints
---------
GET  /health         liveness probe
POST /align          enqueue alignment (returns 202)
POST /align-sync     run alignment and return the result inline (debug)
"""
from __future__ import annotations

import logging
import os
import re
from pathlib import Path
from typing import Optional

import requests
from dotenv import load_dotenv
from fastapi import BackgroundTasks, FastAPI, HTTPException
from pydantic import BaseModel, Field

from align import AlignConfig, align_audio

load_dotenv()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(name)s: %(message)s",
)
log = logging.getLogger("worker")

CFG = AlignConfig(
    device=os.environ.get("WHISPERX_DEVICE", "cpu"),
    language=os.environ.get("WHISPERX_LANGUAGE", "pt"),
)
AUDIO_BASE = Path(os.environ.get("AUDIO_BASE_PATH", "../storage/app/public")).resolve()
LARAVEL_URL = os.environ.get("LARAVEL_INTERNAL_URL", "").rstrip("/")
INTERNAL_TOKEN = os.environ.get("INTERNAL_API_TOKEN", "")

app = FastAPI(title="WhisperX Alignment Worker")


class AlignRequest(BaseModel):
    exercise_id: str = Field(..., description="UUID of the exercise to update")
    audio_path: str = Field(..., description="Path relative to AUDIO_BASE_PATH")
    transcript: str = Field(..., description="Canonical sentence (exercises.content)")
    callback_url: Optional[str] = Field(
        None, description="Override LARAVEL_INTERNAL_URL/{id}/timestamps"
    )


@app.get("/health")
def health():
    return {"status": "ok", "device": CFG.device, "language": CFG.language}


@app.post("/align", status_code=202)
def align_enqueue(req: AlignRequest, bg: BackgroundTasks):
    _resolve_audio(req.audio_path)  # validate path early
    bg.add_task(_process, req)
    return {"status": "queued", "exercise_id": req.exercise_id}


@app.post("/align-sync")
def align_sync(req: AlignRequest):
    audio_path = _resolve_audio(req.audio_path)
    timestamps = align_audio(str(audio_path), req.transcript, CFG)
    _check_word_count(req.transcript, timestamps)
    return {"exercise_id": req.exercise_id, "word_timestamps": timestamps}


def _resolve_audio(audio_path: str) -> Path:
    candidate = (AUDIO_BASE / audio_path).resolve()
    # Prevent path traversal outside AUDIO_BASE
    if AUDIO_BASE not in candidate.parents and candidate != AUDIO_BASE:
        raise HTTPException(400, f"audio_path escapes AUDIO_BASE: {audio_path}")
    if not candidate.is_file():
        raise HTTPException(404, f"audio not found: {candidate}")
    return candidate


def _process(req: AlignRequest) -> None:
    try:
        audio_path = _resolve_audio(req.audio_path)
        timestamps = align_audio(str(audio_path), req.transcript, CFG)
        _check_word_count(req.transcript, timestamps)
        _post_back(req, timestamps)
        log.info("aligned %s (%d tokens)", req.exercise_id, len(timestamps))
    except Exception:
        log.exception("alignment failed for %s", req.exercise_id)


def _check_word_count(transcript: str, timestamps: list[dict]) -> None:
    expected = len(re.findall(r"\b[\w'\-]+\b", transcript, flags=re.UNICODE))
    got = len(timestamps)
    if expected != got:
        log.warning("word-count mismatch: expected=%d got=%d", expected, got)


def _post_back(req: AlignRequest, timestamps: list[dict]) -> None:
    url = req.callback_url or f"{LARAVEL_URL}/{req.exercise_id}/timestamps"
    if not url:
        raise RuntimeError("LARAVEL_INTERNAL_URL not configured")
    if not INTERNAL_TOKEN:
        raise RuntimeError("INTERNAL_API_TOKEN not configured")
    r = requests.post(
        url,
        json={"word_timestamps": timestamps},
        headers={
            "X-Internal-Token": INTERNAL_TOKEN,
            "Accept": "application/json",
        },
        timeout=30,
    )
    r.raise_for_status()
