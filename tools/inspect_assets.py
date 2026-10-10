#!/usr/bin/env python3
"""Read-only intake of the two owner-supplied asset folders. No network/image edits."""
from pathlib import Path, PurePosixPath
import argparse
import hashlib
import json
import struct
import sys
import zlib

PNG_MAGIC = b'\x89PNG\r\n\x1a\n'
ALLOWED_FOLDERS = {'Website Assets', 'PS Visuals'}


def png_metadata(data):
    if not data.startswith(PNG_MAGIC):
        raise ValueError('PNG signature missing')
    pos = 8
    kinds = []
    dims = None
    color = None
    depth = None
    while pos < len(data):
        if pos + 12 > len(data):
            raise ValueError('truncated PNG chunk')
        size = struct.unpack_from('>I', data, pos)[0]
        kind = data[pos+4:pos+8]
        end = pos + 12 + size
        if end > len(data):
            raise ValueError('PNG chunk outside file')
        payload = data[pos+8:pos+8+size]
        actual = zlib.crc32(kind + payload) & 0xffffffff
        expected = struct.unpack_from('>I', data, pos+8+size)[0]
        if actual != expected:
            raise ValueError('PNG chunk CRC mismatch')
        if not kinds and kind != b'IHDR':
            raise ValueError('PNG must start with IHDR')
        if kind == b'IHDR':
            if dims is not None or size != 13:
                raise ValueError('invalid/duplicate PNG header')
            width, height, depth, color, comp, filt, inter = struct.unpack('>IIBBBBB', payload)
            if not width or not height or color not in (0, 2, 3, 4, 6) or comp != 0 or filt != 0 or inter not in (0, 1):
                raise ValueError('invalid PNG dimensions/color/header')
            dims = {'width': width, 'height': height}
        kinds.append(kind)
        pos = end
        if kind == b'IEND':
            if size or pos != len(data):
                raise ValueError('PNG data after IEND or malformed end')
            break
    if not dims or b'IDAT' not in kinds or not kinds or kinds[-1] != b'IEND':
        raise ValueError('PNG missing required chunk')
    return {'format': 'PNG', **dims, 'bit_depth': depth, 'color_type': color,
            'alpha_channel_or_transparency_chunk': color in (4, 6) or b'tRNS' in kinds,
            'color_profile_chunks': [k.decode('ascii') for k in kinds if k in (b'iCCP', b'sRGB', b'gAMA')],
            'inspection_limit': 'Chunk structure/CRCs only; pixels, actual visible transparency and identity require visual inspection.'}


def ico_metadata(data):
    if len(data) < 6:
        raise ValueError('truncated ICO header')
    reserved, typ, count = struct.unpack_from('<HHH', data)
    if reserved or typ != 1 or not count or 6 + 16*count > len(data):
        raise ValueError('invalid ICO directory')
    entries = []
    for i in range(count):
        width, height, colors, reserved, planes, bits, size, offset = struct.unpack_from('<BBBBHHII', data, 6+16*i)
        width, height = width or 256, height or 256
        if reserved or not size or offset < 6+16*count or offset + size > len(data):
            raise ValueError('ICO entry outside image data')
        block = data[offset:offset+size]
        encoding = 'DIB'
        if block.startswith(PNG_MAGIC):
            info = png_metadata(block)
            if (info['width'], info['height']) != (width, height):
                raise ValueError('ICO/PNG dimensions disagree')
            encoding = 'PNG'
        else:
            if len(block) < 40:
                raise ValueError('truncated ICO bitmap')
            header_size, bitmap_width, bitmap_height = struct.unpack_from('<Iii', block)
            if header_size < 40 or header_size > len(block) or bitmap_width != width or abs(bitmap_height) != height*2:
                raise ValueError('ICO/DIB dimensions disagree')
        entries.append({'width': width, 'height': height, 'bit_depth': bits, 'encoding': encoding})
    return {'format': 'ICO', 'embedded_images': entries,
            'inspection_limit': 'Directory bounds and embedded headers checked; render/visual checks remain required.'}


def resolve_source(root, relative):
    if not isinstance(relative, str) or '\\' in relative or ':' in relative:
        raise ValueError('unsafe source path')
    p = PurePosixPath(relative)
    if p.is_absolute() or not p.parts or p.parts[0] not in ALLOWED_FOLDERS or len(p.parts) != 2 or any(x in ('', '.', '..') for x in relative.split('/')):
        raise ValueError('source outside approved folders or unsafe nesting')
    current = root
    for component in p.parts:
        current = current / component
        if current.is_symlink():
            raise ValueError('source symlink is not allowed')
    current.resolve().relative_to(root.resolve())
    return current


def inspect(asset_root, manifest):
    root = Path(asset_root).resolve()
    errors, warnings, results = [], [], []
    allowed = set()
    for slot in manifest.get('slots', []):
        sid = slot.get('slot_id', '<missing>')
        entry = {'slot_id': sid, 'status': 'not_inspected', 'source_file': None}
        try:
            sources = [slot['source_file']] + slot.get('source_aliases', [])
            paths = [(s, resolve_source(root, s)) for s in sources]
            allowed.update(sources)
            present = [(s, p) for s, p in paths if p.is_file()]
            if not present:
                raise ValueError('missing listed source (and accepted alias)')
            payloads = [(s, p.read_bytes()) for s, p in present]
            hashes = {hashlib.sha256(b).hexdigest() for _, b in payloads}
            if len(hashes) != 1:
                raise ValueError('conflicting primary/alias files')
            source, data = payloads[0]
            if len(payloads) > 1:
                warnings.append(sid + ': identical primary/alias copies recorded')
            ext = PurePosixPath(source).suffix.lower()
            meta = png_metadata(data) if ext == '.png' else ico_metadata(data) if ext == '.ico' else None
            if meta is None:
                raise ValueError('unsupported/unlisted image format')
            entry.update(source_file=source, size_bytes=len(data), sha256=next(iter(hashes)), metadata=meta,
                         duplicate_aliases=[s for s, _ in payloads[1:]], status='structure_checked_visual_review_required')
            if len(data) != slot.get('expected_source_bytes'):
                warnings.append(sid + ': byte size differs from owner listing; review and document before acceptance')
                entry['size_matches_owner_listing'] = False
            else:
                entry['size_matches_owner_listing'] = True
        except (OSError, ValueError, KeyError, struct.error) as e:
            entry['status'] = 'error'
            entry['issue'] = str(e) if not isinstance(e, OSError) else type(e).__name__
            errors.append(sid + ': ' + entry['issue'])
        results.append(entry)
    references = []
    for ref in manifest.get('reference_files', []):
        name = ref['source_file']; allowed.add(name)
        try:
            p = resolve_source(root, name)
            data = p.read_bytes()
            references.append({'source_file': name, 'size_bytes': len(data), 'sha256': hashlib.sha256(data).hexdigest(), 'status': 'read_and_compare_wording_manually'})
        except (OSError, ValueError):
            warnings.append(name + ': reference file unavailable; record for copy review')
    unexpected = []
    for folder in sorted(ALLOWED_FOLDERS):
        directory = root / folder
        if directory.is_dir() and not directory.is_symlink():
            for p in directory.iterdir():
                rel = folder + '/' + p.name
                if rel not in allowed:
                    unexpected.append(rel)
    if unexpected:
        warnings.append('Unlisted entries found; inventory only, never import automatically')
    return {'status': 'FAIL' if errors else 'REVIEW' if warnings else 'STRUCTURE_CHECKED',
            'scope': 'Local source intake only; no copying, deletion, network, image edits, licensing conclusion or visual approval.',
            'expected_visual_files': manifest.get('expected_source_file_count'), 'inspected_slots': len(results),
            'results': results, 'reference_files': references, 'unexpected_entries': unexpected,
            'errors': errors, 'warnings': warnings}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--asset-root', type=Path, required=True, help='Parent of Website Assets and PS Visuals')
    parser.add_argument('--manifest', type=Path, default=Path(__file__).resolve().parents[1]/'assets/manifest.json')
    parser.add_argument('--output', type=Path, help='Local JSON report; must not overwrite a source file')
    args = parser.parse_args()
    manifest = json.loads(args.manifest.read_text(encoding='utf-8'))
    if args.output:
        out = args.output.resolve()
        for folder in ALLOWED_FOLDERS:
            if out.is_relative_to((args.asset_root/folder).resolve()):
                parser.error('Report output must be outside both source folders')
        if out == args.manifest.resolve():
            parser.error('Report output must not overwrite the manifest')
    report = inspect(args.asset_root, manifest)
    rendered = json.dumps(report, indent=2, ensure_ascii=False)+'\n'
    if args.output:
        args.output.parent.mkdir(parents=True, exist_ok=True)
        args.output.write_text(rendered, encoding='utf-8')
        print(json.dumps({'status': report['status'], 'slots': len(report['results']), 'errors': report['errors'], 'warnings': report['warnings']}, indent=2))
    else:
        print(rendered)
    return 2 if report['errors'] else 1 if report['warnings'] else 0


if __name__ == '__main__':
    sys.exit(main())
