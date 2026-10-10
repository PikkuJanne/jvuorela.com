#!/usr/bin/env python3
"""Offline handoff validation. Does not validate a WordPress implementation."""
from pathlib import Path, PurePosixPath
import argparse
import hashlib
import json
import re
import sys

TOOLS = {
    'winbooksplit': ('WinBookSplit', 'WinBookSplit'),
    'winpdfmerger': ('WinPDFMerger', 'WinPDFMerger'),
    'winvidcompress': ('WinVidCompress', 'WinVidCompress'),
    'universalpodcastdownloader': ('UniversalPodcastDownloader', 'UniversalPodcastDownloader'),
    'winaudioclean': ('WinAudioClean', 'WinAudioClean'),
    'winimagenormalizer': ('WinImageNormalizer', 'WinImgNormalizer'),
    'audiofileforphone': ('AudioFileForPhone', 'AudioFileForPhone'),
}


def safe_relative(path):
    if not isinstance(path, str) or not path or '\\' in path or ':' in path:
        return False
    p = PurePosixPath(path)
    return not p.is_absolute() and all(x not in ('', '.', '..') for x in path.split('/'))


def validate(root, integrity=True):
    root = Path(root).resolve()
    errors = []
    def need(condition, message):
        if not condition:
            errors.append(message)
    def read(name):
        try:
            return json.loads((root / name).read_text(encoding='utf-8'))
        except (OSError, ValueError) as e:
            errors.append(f'{name}: cannot read JSON ({type(e).__name__})')
            return {}
    def index(items, key, name):
        result = {}
        for obj in items:
            k = obj.get(key)
            need(isinstance(k, str) and bool(k), f'{name}: missing {key}')
            need(k not in result, f'{name}: duplicate {k}')
            result[k] = obj
        return result

    content = read('content/tools.json')
    site = read('content/site.json')
    releases = read('content/releases.json')
    assets = read('assets/manifest.json')
    task_data = read('TASKS.json')
    acceptance = read('ACCEPTANCE.json')
    cleanup = read('repositories/tool-cleanup.json')
    for label, data in [('tools',content),('site',site),('releases',releases),('tasks',task_data),('acceptance',acceptance),('cleanup',cleanup)]:
        need(data.get('schema_version') == 1, f'{label}: unsupported/missing schema_version')
    need(assets.get('schema_version') == 2, 'assets: v2 integrated schema required')
    ts = index(content.get('tools', []), 'id', 'tools')
    rs = index(releases.get('records', []), 'tool_id', 'releases')
    slots = index(assets.get('slots', []), 'slot_id', 'assets')
    tasks = index(task_data.get('tasks', []), 'id', 'tasks')
    cases = index(acceptance.get('cases', []), 'id', 'acceptance')
    need(set(ts) == set(TOOLS), 'tools: expected exactly seven canonical IDs')
    need(set(rs) == set(TOOLS), 'releases: expected exactly seven canonical IDs')
    need(content.get('copy_is_editable') is True and content.get('seed_only') is True, 'tools: editable seed contract changed')
    need(site.get('canonical_origin') == 'https://jvuorela.com', 'site: canonical origin mismatch')
    need(site.get('publish_seed_pages') is False, 'site: seed must not publish automatically')
    need(assets.get('artwork_creation_by_codex') is False, 'assets: Codex artwork creation must remain disabled')
    policy=site.get('repository_policy',{})
    need(policy.get('owner') == 'PikkuJanne', 'github: wrong owner namespace')
    need(policy.get('visibility_required') == 'public' and policy.get('open_source_required') is True, 'github: public open-source requirement missing')
    need(policy.get('sync_each_passing_checkpoint') is True and policy.get('public_release_required') is True, 'github: continuous sync/final release required')
    need(policy.get('code_license') == 'GPL-2.0-or-later', 'github: code license missing')
    need(assets.get('tool_repository_assets_allowed') is False, 'assets: tool-repository sources forbidden')
    need(set(assets.get('allowed_source_folders',[])) == {'Website Assets','PS Visuals'}, 'assets: source-folder allowlist changed')
    need(cleanup.get('reuse_for_website_allowed') is False, 'cleanup: candidate reuse forbidden')
    need(cleanup.get('normal_git_cleanup_authorized') is True, 'cleanup: ordinary scoped removal missing')
    need(cleanup.get('history_rewrite_authorized') is False and cleanup.get('published_release_changes_authorized') is False, 'cleanup: destructive scope expansion')
    cr=index(cleanup.get('repositories',[]),'tool_id','cleanup')
    need(set(cr)==set(TOOLS),'cleanup: expected seven repositories')
    for ident, (_, repo) in TOOLS.items():
        need(cr.get(ident,{}).get('repo_url')=='https://github.com/PikkuJanne/'+repo, f'cleanup: wrong repository {ident}')
    routes = []
    for ident, t in ts.items():
        if ident not in TOOLS:
            continue
        name, repo = TOOLS[ident]
        need(t.get('display_name') == name, f'{ident}: display name mismatch')
        need(t.get('repo_url') == 'https://github.com/PikkuJanne/' + repo, f'{ident}: repository alias mismatch')
        need(t.get('route') == '/' + ident + '/', f'{ident}: canonical route mismatch')
        need(t.get('launch_version') == '1.0.0', f'{ident}: initial tool version mismatch')
        need(len(t.get('benefits', [])) == 3, f'{ident}: expected three draft benefits')
        for key in ['tagline', 'description', 'requirements', 'quick_start', 'output_and_limits', 'launcher']:
            need(isinstance(t.get(key), str) and bool(t.get(key)), f'{ident}: missing {key}')
        for kind, sid in t.get('asset_slots', {}).items():
            need(sid in slots, f'{ident}: unknown {kind} slot {sid}')
            need(slots.get(sid, {}).get('tool_id') == ident, f'{ident}: wrong asset ownership {sid}')
        need(set(t.get('asset_slots', {})) == {'hero','avatar','social','icon','poster','application_icon'}, f'{ident}: missing integrated slot mapping')
        r = rs.get(ident, {})
        need(r.get('repo') == 'PikkuJanne/' + repo, f'{ident}: release repository mismatch')
        need(r.get('expected_tag') == 'v1.0.0', f'{ident}: release tag mismatch')
        url = r.get('release_page_url')
        if r.get('observed_status') == 'published':
            need(url == t['repo_url'] + '/releases/tag/v1.0.0', f'{ident}: bad observed release URL')
            need(any(a.get('name','').endswith('.zip') for a in r.get('assets', [])), f'{ident}: missing observed runtime ZIP')
        else:
            need(url is None, f'{ident}: unpublished record must not invent verified URL')
        for a in r.get('assets', []):
            need(a.get('url','').startswith(t['repo_url'] + '/releases/download/v1.0.0/'), f'{ident}: wrong asset URL origin/tag')
        routes.append(t.get('route'))
    need(ts.get('winpdfmerger',{}).get('launcher') == 'WinPDFMerge.bat', 'WinPDFMerge launcher alias lost')
    need(ts.get('winimagenormalizer',{}).get('launcher') == 'WinImgNormalizer.bat', 'WinImgNormalizer launcher alias lost')
    routes += [site.get('home',{}).get('route')] + [p.get('route') for p in site.get('support_pages',[])]
    need(len(routes) == len(set(routes)), 'site: duplicate page route')
    need(set(p.get('route') for p in site.get('support_pages',[])) == {'/impressum/','/privacy/'}, 'site: support route mismatch')
    required = [s for s in slots.values() if s.get('required_for_launch')]
    need(len(required) == 33 == assets.get('required_slot_count'), 'assets: required slot count mismatch')
    need(len(slots)-len(required) == 14 == assets.get('optional_slot_count'), 'assets: optional slot count mismatch')
    need(len(slots) == 47 == assets.get('expected_source_file_count'), 'assets: expected 47 mapped visual files')
    expected_sources={}
    for ident,(name,_) in TOOLS.items():
        for kind,suffix in [('character','_character.png'),('avatar','_portrait.png'),('social','_SM.png')]:
            expected_sources[ident+'-'+kind]='Website Assets/'+name+suffix
        for kind,suffix in [('icon','.png'),('application-icon','.ico'),('poster','_poster.png')]:
            expected_sources[ident+'-'+kind]='PS Visuals/'+name+suffix
    expected_sources['winpdfmerger-social']='Website Assets/WinPDFMerger_ SM.png'
    expected_sources.update({'site-icon':'Website Assets/Website_icon.png','site-social':'Website Assets/Website_SM.png','site-favicon':'Website Assets/Website_icon.ico','site-background-desktop':'Website Assets/Website_background.png','site-background-mobile':'Website Assets/Mobile_background.png'})
    need(set(slots)==set(expected_sources),'assets: slot ID set differs from approved inventory')
    source_paths=[]
    for sid, s in slots.items():
        dims=s.get('recommended_master_size')
        if dims is not None:
            need(all(type(dims.get(k)) is int and dims[k] > 0 for k in ('width','height')), f'{sid}: invalid dimensions')
        need(s.get('tool_id') is None or s.get('tool_id') in ts, f'{sid}: unknown tool')
        source=s.get('source_file')
        need(safe_relative(source), f'{sid}: unsafe source path')
        need(source==expected_sources.get(sid), f'{sid}: source mapping differs from owner inventory')
        source_paths.append(source)
        need(s.get('source_root')=='owner_asset_parent', f'{sid}: invalid source root')
        need(s.get('inventory_required') is True,f'{sid}: inventory required')
        need(type(s.get('expected_source_bytes')) is int and s['expected_source_bytes']>0, f'{sid}: source byte-count hint missing')
        need(s.get('required_for_launch') == (s.get('kind') not in ['application-icon','poster']), f'{sid}: selected-use classification mismatch')
        aliases=s.get('source_aliases',[])
        expected_alias=['Website Assets/WinPDFMerger_SM.png'] if sid=='winpdfmerger-social' else []
        need(aliases==expected_alias, f'{sid}: unexpected source alias')
        if s.get('verification',{}).get('status')=='verified':
            need(bool(re.fullmatch('[a-f0-9]{64}', s.get('source_sha256') or '')) and bool(s.get('actual_metadata')), f'{sid}: verified without source evidence')
        if s.get('kind') == 'social':
            need(dims == {'width':1200,'height':630}, f'{sid}: social size mismatch')
    need(len(set(source_paths))==len(source_paths),'assets: duplicate source mapping')
    for role,sid in site.get('asset_slots',{}).items():
        need(sid in slots and slots[sid].get('tool_id') is None,f'site: bad shared asset {role}')
    need(set(site.get('asset_slots',{}))=={'site_icon','favicon','social','desktop_background','mobile_background'},'site: shared asset mappings incomplete')
    need(all(t in tasks for t in ['T24','T25','T26','T27','T28']),'tasks: public GitHub/intake/cleanup/reproducibility tasks missing')
    need({'T24','T27','T28'} <= set(tasks.get('T23',{}).get('depends_on',[])), 'tasks: final handover must depend on GitHub/cleanup/public reconstruction')
    for path in ['LICENSE','LICENSE_SCOPE.md','ARTWORK_LICENSE.md','THIRD_PARTY_NOTICES.md','docs/11_PUBLIC_GITHUB.md','docs/12_TOOL_REPOSITORY_CLEANUP.md','tools/inspect_assets.py','.github/workflows/handoff-checks.yml']:
        need((root/path).is_file(), f'public handoff: missing {path}')
    for tid,t in tasks.items():
        need(t.get('status') in task_data.get('statuses',[]), f'{tid}: invalid status')
        need(bool(t.get('acceptance_ids')), f'{tid}: missing acceptance')
        for dep in t.get('depends_on',[]):
            need(dep in tasks and dep != tid, f'{tid}: invalid dependency {dep}')
        for path in t.get('specifications',[]):
            need(safe_relative(path) and (root/path).is_file(), f'{tid}: missing/unsafe specification {path}')
        for cid in t.get('acceptance_ids',[]):
            need(cid in cases and cases.get(cid,{}).get('task_id') == tid, f'{tid}: invalid acceptance mapping {cid}')
    for cid,c in cases.items():
        need(c.get('task_id') in tasks and cid in tasks.get(c.get('task_id'),{}).get('acceptance_ids',[]), f'{cid}: orphaned case')
    visiting=set(); visited=set()
    def visit(tid):
        if tid in visiting:
            errors.append(f'tasks: dependency cycle at {tid}'); return
        if tid in visited or tid not in tasks:
            return
        visiting.add(tid)
        for dep in tasks[tid].get('depends_on',[]): visit(dep)
        visiting.remove(tid); visited.add(tid)
    for tid in tasks: visit(tid)
    if integrity:
        manifest=read('BUNDLE_MANIFEST.json'); listed=set()
        for entry in manifest.get('files',[]):
            name=entry.get('path');need(safe_relative(name),f'manifest: unsafe path {name}')
            if not safe_relative(name): continue
            need(name not in listed, f'manifest: duplicate {name}');listed.add(name)
            p=root/name
            need(p.is_file() and not p.is_symlink(),f'manifest: missing/linked {name}')
            if p.is_file() and not p.is_symlink():
                need(len(p.read_bytes()) == entry.get('size_bytes'),f'manifest: size mismatch {name}')
                need(hashlib.sha256(p.read_bytes()).hexdigest() == entry.get('sha256'),f'manifest: hash mismatch {name}')
        actual={p.relative_to(root).as_posix() for p in root.rglob('*') if p.is_file() and p.name != 'BUNDLE_MANIFEST.json' and '.git' not in p.parts and '__pycache__' not in p.parts and p.suffix != '.pyc'}
        need(actual == listed,'manifest: file set differs from shipped bundle')
        need(bool(listed),'manifest: no files')
    return errors, {'tools':len(ts),'tasks':len(tasks),'acceptance_cases':len(cases),'required_assets':len(required),'optional_assets':len(slots)-len(required),'mapped_source_files':len(source_paths),'tool_repositories_for_cleanup':len(cr)}


def main():
    p=argparse.ArgumentParser(description=__doc__)
    p.add_argument('--bundle', type=Path, default=Path(__file__).resolve().parents[1])
    p.add_argument('--structure-only', action='store_true', help='For edited handoff data; skips original-file hashes. Does not check implementation.')
    args=p.parse_args()
    try:
        errors,counts=validate(args.bundle, integrity=not args.structure_only)
    except (TypeError, KeyError, AttributeError) as e:
        errors=[f'Malformed handoff data: {type(e).__name__}'];counts={}
    print(json.dumps({'status':'FAIL' if errors else 'PASS','scope':'handoff only; no runtime or live-site validation','counts':counts,'errors':errors},indent=2))
    return 1 if errors else 0

if __name__ == '__main__':
    sys.exit(main())
