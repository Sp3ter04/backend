"""
Force-alignment of a known transcript against an audio file using WhisperX.

We deliberately SKIP the ASR (Whisper) step and feed the canonical transcript
directly into the alignment model. This is faster and guarantees that the
emitted token sequence matches the words stored in `exercises.content`.
"""
from __future__ import annotations

import logging
import threading
from dataclasses import dataclass
from typing import Optional

import whisperx

log = logging.getLogger(__name__)

# WhisperX align models are ~1GB; load once per process and reuse.
_align_lock = threading.Lock()
_align_cache: dict[tuple[str, str], tuple] = {}


@dataclass
class AlignConfig:
    device: str = "cpu"
    language: str = "pt"


def _get_align_model(cfg: AlignConfig):
    key = (cfg.language, cfg.device)
    with _align_lock:
        if key not in _align_cache:
            log.info("Loading WhisperX align model lang=%s device=%s", *key)
            model, metadata = whisperx.load_align_model(
                language_code=cfg.language, device=cfg.device
            )
            _align_cache[key] = (model, metadata)
        return _align_cache[key]


def align_audio(
    audio_path: str,
    transcript: str,
    cfg: Optional[AlignConfig] = None,
) -> list[dict]:
    """Return a list of `{token, type, startTime, endTime}` for each aligned word.

    Punctuation is NOT emitted — only word tokens. The caller is expected to
    persist this verbatim into `exercises.word_timestamps`.
    """
    cfg = cfg or AlignConfig()
    model_a, metadata = _get_align_model(cfg)

    audio = whisperx.load_audio(audio_path)
    duration = len(audio) / 16000.0
    segments = [{"text": transcript, "start": 0.0, "end": duration}]

    result = whisperx.align(
        segments,
        model_a,
        metadata,
        audio,
        cfg.device,
        return_char_alignments=False,
    )

    out: list[dict] = []
    for seg in result.get("segments", []):
        for w in seg.get("words", []):
            start = w.get("start")
            end = w.get("end")
            token = w.get("word")
            if token is None or start is None or end is None:
                # WhisperX may fail to align a token (OOV, noise). Skip silently;
                # the caller validates the count and logs mismatches.
                log.warning("dropping unaligned token: %r", w)
                continue
            out.append(
                {
                    "token": token,
                    "type": "word",
                    "startTime": round(float(start), 3),
                    "endTime": round(float(end), 3),
                }
            )
    return out
