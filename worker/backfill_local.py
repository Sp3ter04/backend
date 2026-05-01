"""Local backfill: enumerate exercises from a JSON dump and align them
sequentially via the worker's /align-sync endpoint, then POST results to
the Laravel internal endpoint.

Usage:
    python backfill_local.py /tmp/exercises_to_align.json [--limit N] [--skip-id UUID]
"""
import argparse, json, sys, time
import requests
from pathlib import Path
from dotenv import load_dotenv
import os

load_dotenv()
WORKER = f"http://{os.environ.get('WORKER_HOST','127.0.0.1')}:{os.environ.get('WORKER_PORT','8000')}"
LARAVEL = os.environ['LARAVEL_INTERNAL_URL'].rstrip('/')
TOKEN = os.environ['INTERNAL_API_TOKEN']

p = argparse.ArgumentParser()
p.add_argument('input', type=Path)
p.add_argument('--limit', type=int)
p.add_argument('--skip-id', action='append', default=[])
args = p.parse_args()

rows = json.loads(args.input.read_text())
if args.limit:
    rows = rows[:args.limit]

ok = fail = 0
t0 = time.time()
for i, ex in enumerate(rows, 1):
    if ex['id'] in args.skip_id:
        print(f"[{i}/{len(rows)}] SKIP {ex['id']}")
        continue
    label = ex['content'][:60].replace('\n',' ')
    try:
        ts0 = time.time()
        r = requests.post(f"{WORKER}/align-sync", json={
            'exercise_id': ex['id'],
            'audio_path': ex['audio_url_1'],
            'transcript': ex['content'],
        }, timeout=120)
        r.raise_for_status()
        wt = r.json()['word_timestamps']
        align_dt = time.time() - ts0

        r2 = requests.post(
            f"{LARAVEL}/{ex['id']}/timestamps",
            headers={'X-Internal-Token': TOKEN},
            json={'word_timestamps': wt},
            timeout=30,
        )
        r2.raise_for_status()
        body = r2.json()
        match = '✓' if body.get('word_count_match') else '✗'
        print(f"[{i}/{len(rows)}] OK  {match} {align_dt:5.2f}s {len(wt):3d}t  {label}")
        ok += 1
    except Exception as e:
        msg = str(e)
        if hasattr(e, 'response') and e.response is not None:
            msg = f"{e.response.status_code} {e.response.text[:200]}"
        print(f"[{i}/{len(rows)}] ERR {label} -- {msg}", file=sys.stderr)
        fail += 1

print(f"\n{ok} ok, {fail} failed in {time.time()-t0:.1f}s")
sys.exit(0 if fail == 0 else 1)
