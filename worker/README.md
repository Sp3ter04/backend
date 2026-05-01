# WhisperX alignment worker

FastAPI service that force-aligns a known transcript against an audio file and
posts the resulting word timestamps back to Laravel, which persists them into
`exercises.word_timestamps` (and recomputes `word_start_times`).

```
Laravel ──POST /align──▶ worker ──POST /api/internal/exercises/{id}/timestamps──▶ Laravel ──▶ Supabase
```

## Output format

The worker emits one entry per word (punctuation dropped):

```json
[
  {"token": "No",      "type": "word", "startTime": 0.12, "endTime": 0.31},
  {"token": "domingo", "type": "word", "startTime": 0.34, "endTime": 0.78}
]
```

This is a **superset** of the existing schema (`endTime` added).

## Setup (local dev)

```bash
cd worker
python3.11 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
pip install --no-deps whisperx==3.8.5
# NLTK punkt_tab (used by whisperx.align). On macOS the system Python often
# lacks CA roots, so force certifi:
SSL_CERT_FILE=$(python -c "import certifi; print(certifi.where())") \
  python -c "import nltk; nltk.download('punkt_tab')"
cp .env.example .env
# edit .env: set INTERNAL_API_TOKEN (must match backend/.env) and LARAVEL_INTERNAL_URL
```

Why `--no-deps` for whisperx: it depends on `pyannote.audio` -> `lightning`,
and `lightning` is currently quarantined on PyPI. We don't need diarization
for alignment, so the deps in `requirements.txt` are sufficient.

First alignment call downloads the wav2vec2 model (~1 GB) into the HF cache.

## Run

```bash
uvicorn main:app --host 127.0.0.1 --port 8000 --reload
```

Smoke test:

```bash
curl -s http://127.0.0.1:8000/health

# Inline (no DB write) — useful while iterating
curl -s -X POST http://127.0.0.1:8000/align-sync \
  -H 'Content-Type: application/json' \
  -d '{
    "exercise_id": "00000000-0000-0000-0000-000000000000",
    "audio_path": "audio/sentences/exercise-1-foo.mp3",
    "transcript": "A menina vê a mamã."
  }' | jq
```

## Backfill the existing exercises

```bash
# uvicorn is running in another shell
python backfill.py --dry-run        # preview
python backfill.py --limit 1        # one
python backfill.py                  # all
```

## Production notes

* `WHISPERX_DEVICE=cuda` + `WHISPERX_COMPUTE_TYPE=float16` if a GPU is available.
* The worker is single-process and uses `BackgroundTasks` (in-process queue).
  For >1 concurrent alignment, run multiple workers behind a reverse proxy or
  switch to a real queue (RQ / Celery / Redis).
* `AUDIO_BASE_PATH` must point to the directory that contains `audio/sentences/...`
  (i.e. the target of `storage/app/public`). Path traversal is blocked.
