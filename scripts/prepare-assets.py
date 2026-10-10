#!/usr/bin/env python3
"""GPL-2.0-or-later. Optimize only hashed, allowlisted owner deliveries."""
import argparse
import hashlib
import json
from pathlib import Path
from PIL import Image, ImageDraw, ImageOps
from sys import path as module_path

ROOT = Path(__file__).resolve().parents[1]
module_path.insert(0, str(ROOT / 'tools'))
from inspect_assets import inspect, resolve_source


def sha(p):
    return hashlib.sha256(p.read_bytes()).hexdigest()


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--asset-root', required=True, type=Path)
    ap.add_argument('--inspect-only', action='store_true')
    args = ap.parse_args()
    mpath = ROOT / 'assets/manifest.json'
    m = json.loads(mpath.read_text(encoding='utf8'))
    report = inspect(args.asset_root, m)
    if report['errors'] or report['warnings']:
        raise SystemExit('Intake requires review; no derivatives produced.')
    records = {r['slot_id']: r for r in report['results']}
    diag = ROOT / 'private/visual-review'
    diag.mkdir(parents=True, exist_ok=True)
    output = ROOT / 'assets/approved'
    output.mkdir(parents=True, exist_ok=True)
    groups = {}
    for slot in m['slots']:
        r = records[slot['slot_id']]
        source = resolve_source(args.asset_root, r['source_file'])
        with Image.open(source) as im:
            im.load()
            original = im.convert('RGBA')
            groups.setdefault(slot['kind'], []).append((slot['slot_id'], original.copy()))
            if args.inspect_only:
                continue
            # Recheck actual bytes before encoding. Never use a tool repository fallback.
            if sha(source) != r['sha256']:
                raise SystemExit('Source changed during intake.')
            slot['source_sha256'] = r['sha256']
            slot['actual_metadata'] = r['metadata']
            slot['actual_source_file'] = r['source_file']
            slot['verification'] = {'status': 'technical_verified_visual_review_recorded', 'evidence': 'evidence/asset-review.md'}
            slot['derivatives'] = []
            slot['alt_text'] = ''
            if not slot['required_for_launch']:
                slot['status'] = 'inventoried_ancillary_not_used'
                continue
            kind = slot['kind']
            widths = {'character': [480, 960], 'avatar': [360, 640], 'icon': [96], 'site_icon': [512], 'background': [1440] if 'desktop' in slot['slot_id'] else [780], 'social': [1200], 'favicon': [64]}[kind]
            for width in widths:
                width = min(width, original.width)
                height = round(original.height * width / original.width)
                derivative = original.resize((width, height), Image.Resampling.LANCZOS)
                ext = 'png' if kind in ('social', 'site_icon') else 'ico' if kind == 'favicon' else 'webp'
                name = f"{slot['expected_filename_stem']}-{width}.{ext}"
                dest = output / name
                options = {'quality': 84, 'method': 6} if ext == 'webp' else {'optimize': True} if ext == 'png' else {'sizes': [(16, 16), (32, 32), (64, 64)]}
                derivative.save(dest, **options)
                slot['derivatives'].append({'path': dest.relative_to(ROOT).as_posix(), 'sha256': sha(dest), 'size_bytes': dest.stat().st_size, 'width': width, 'height': height, 'format': ext, 'transform': 'proportional resize; no crop, generation, retouching or redesign', 'source_sha256': r['sha256']})
            slot['status'] = 'approved_derivatives_integrated'
    # Private diagnostic contact sheets only, never website artwork/compositions.
    for kind, items in groups.items():
        cellw, cellh, cols = (280, 370, 4) if kind == 'character' else (280, 320, 4)
        sheet = Image.new('RGB', (cols * cellw, ((len(items) + cols - 1) // cols) * cellh), '#171b20')
        draw = ImageDraw.Draw(sheet)
        for n, (label, im) in enumerate(items):
            im.thumbnail((cellw-20, cellh-50), Image.Resampling.LANCZOS)
            x, y = (n % cols)*cellw, (n // cols)*cellh
            sheet.paste(im, (x + (cellw-im.width)//2, y+10), im)
            draw.text((x+8, y+cellh-32), label, fill='white')
        sheet.save(diag / f'{kind}.jpg', quality=93)
    if not args.inspect_only:
        mpath.write_text(json.dumps(m, indent=2, ensure_ascii=False)+'\n', encoding='utf8')
        print(json.dumps({'sources': len(records), 'selected_slots': sum(s['required_for_launch'] for s in m['slots']), 'derivatives': sum(len(s['derivatives']) for s in m['slots'])}))


if __name__ == '__main__':
    main()
