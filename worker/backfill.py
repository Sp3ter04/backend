"""One-shot backfill: pump every exercise with audio through the worker.

Usage:
    python backfill.py                 # all exercises with audio_url_1
    python backfill.py --limit 5       # first N
    python backfill.py --id <uuid>     # single exercise
    python backfill.py --dry-run       # list only

Requires SUPABASE_URL + SUPABASE_SERVICE_KEY in .env to read exercises.
"""
from __future__ import annotations

import argparse
import os
import sys
import time

import requests
from dotenv import load_dotenv

load_dotenv()

SUPABASE_URL = os.environ.get("SUPABASE_URL", "").rstrip("/")
SUPABASE_KEY = os.environ.get("SUPABASE_SERVICE_KEY", "")
WORKER_URL = (
    f"http://{os.environ.get('WORKER_HOST', '127.0.0.1')}:"
    f"{os.environ.get('WORKER_PORT', '8000')}/align"
)


def fetch_exercises(limit: int | None, only_id: str | None) -> list[dict]:
    if not SUPABASE_URL or not SUPABASE_KEY:
        sys.exit("SUPABASE_URL / SUPABASE_SERVICE_KEY not set")
    params = {
        "select": "id,content,audio_url_1",
        "audio_url_1": "not.is.null",
        "order": "number.asc",
    }
    if only_id:
        params["id"] = f"eq.{only_id}"
    if limit:
        params["limit"] = str(limit)
    r = requests.get(
        f"{SUPABASE_URL}/rest/v1/exercises",
        params=params,
        headers={
            "apikey": SUPABASE_KEY,
            "Authorization": f"Bearer {SUPABASE_KEY}",
        },
        timeout=30,
    )
    r.raise_for_status()
    return r.json()


def main() -> None:
    p = argparse.ArgumentParser()
    p.add_argument("--limit", type=int)
    p.add_argument("--id", dest="only_id")
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--sleep", type=float, default=0.1)
    args = p.parse_args()

    rows = fetch_exercises(args.limit, args.only_id)
    print(f"{len(rows)} exercise(s) to align")

    for ex in rows:
        line = f"  {ex['id']}  {ex['audio_url_1']}"
        if args.dry_run:
            print(line)
            continue
        r = requests.post(
            WORKER_URL,
            json={
                "exercise_id": ex["id"],
                "audio_path": ex["audio_url_1"],
                "transcript": ex["content"],
            },
            timeout=10,
        )
        status = "OK" if r.ok else f"ERR {r.status_code}"
        print(f"{status}  {line}")
        time.sleep(args.sleep)


if __name__ == "__main__":
    main()
