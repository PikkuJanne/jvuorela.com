#!/usr/bin/env python3
"""GPL-2.0-or-later. Generate portable editable core blocks; never writes a database."""
import html
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def text(value):
    return re.sub(r'`([^`]+)`', r'<code>\1</code>', html.escape(value))


def block(kind, inner, attrs=None):
    attr = ' ' + json.dumps(attrs, separators=(',', ':'), ensure_ascii=False) if attrs else ''
    return f'<!-- wp:{kind}{attr} -->{inner}<!-- /wp:{kind} -->\n'


def paragraph(value, cls=''):
    return block('paragraph', '<p'+(f' class="{cls}"' if cls else '')+'>'+text(value)+'</p>', {'className': cls} if cls else None)


def heading(value, level=2, anchor=None):
    return block('heading', f'<h{level}'+(f' id="{anchor}"' if anchor else '')+f' class="wp-block-heading">{value}</h{level}>', {'level': level, **({'anchor': anchor} if anchor else {})})


def group(content, cls):
    return block('group', f'<div class="wp-block-group {cls}">{content}</div>', {'className': cls})


def image(slot, cls, dims):
    width, height = dims
    # Attachment ID is an unquoted numeric token, resolved before WordPress parses the block.
    # Core image save markup has no intrinsic dimension attributes without editor sizing.
    # WordPress adds actual attachment dimensions and responsive sources when rendering.
    return '<!-- wp:image {"id":{{attachment:'+slot+'}},"sizeSlug":"full","linkDestination":"none","className":"'+cls+'"} --><figure class="wp-block-image size-full '+cls+'"><img src="{{asset:'+slot+'}}" alt="" class="wp-image-{{attachment:'+slot+'}}"/></figure><!-- /wp:image -->\n'


def button(label, url, outline=False):
    cls = 'wp-block-button' + (' is-style-outline' if outline else '')
    return block('button', f'<div class="{cls}"><a class="wp-block-button__link wp-element-button" href="{html.escape(url, quote=True)}">{text(label)}</a></div>', {'className': 'is-style-outline'} if outline else None)


def main():
    site = json.loads((ROOT / 'content/site.json').read_text(encoding='utf8'))
    tools = json.loads((ROOT / 'content/tools.json').read_text(encoding='utf8'))['tools']
    releases = json.loads((ROOT / 'content/releases.json').read_text(encoding='utf8'))
    release_entries = releases['records']
    verified = {}
    # Only the newly verified evidence proves an actionable stable download.
    evidence_path = ROOT / 'evidence/release-verification.json'
    if evidence_path.exists():
        e = json.loads(evidence_path.read_text(encoding='utf8'))
        for r in e['records']:
            sid = r.get('tool_id', r.get('id'))
            rv = r.get('runtime_verification') or {}
            if r.get('observed_status') == 'published' and not r.get('draft') and not r.get('prerelease') and rv.get('checksum_matches') and rv.get('digest_matches_api'):
                verified[sid] = r['release_page_url']
    manifest = json.loads((ROOT / 'assets/manifest.json').read_text(encoding='utf8'))
    slots = {s['slot_id']: s for s in manifest['slots']}
    def art(slot, cls):
        d = max(slots[slot]['derivatives'], key=lambda x:x['width'])
        return image(slot, cls, (d['width'], d['height']))
    pages = []
    cards = ''
    for t in tools:
        cards += group(art(t['asset_slots']['avatar'], 'card-portrait') + paragraph(f"0{t['order']} / {t['display_name']}", 'card-index') + heading('<a href="{{page:'+t['id']+'}}">'+text(t['display_name'])+'</a>', 3) + paragraph(t['tagline']), 'tool-card')
        url = verified.get(t['id'])
        # Verification shape is also accepted from explicit final launch fields in content/releases.json.
        if not url:
            for r in release_entries:
                if r.get('tool_id') == t['id']:
                    v = r.get('launch_verification', {})
                    if v.get('status') == 'passed' and v.get('checksum_matches') and v.get('release_is_stable'):
                        url = r.get('release_page_url')
        pending = '' if url else paragraph('Release link pending', 'release-pending')
        actions = button(t['primary_button_label'], url) if url else ''
        actions += button('Source & instructions', t['documentation_url'], True)
        copy = art(t['asset_slots']['icon'], 'tool-icon') + paragraph(t['tagline'], 'tagline') + paragraph(t['description'], 'description') + pending + block('buttons', '<div class="wp-block-buttons">'+actions+'</div>')
        content = group(group(copy,'tool-copy') + art(t['asset_slots']['hero'], 'tool-art'), 'tool-hero')
        items = ''.join(block('list-item', '<li>'+text(s)+'</li>') for s in t['benefits'])
        content += group(heading('What it does')+block('list','<ul class="wp-block-list">'+items+'</ul>'), 'tool-section tool-benefits')
        for title, value, cls in [('Requirements', t['requirements'],'tool-requirements'), ('Get started',t['quick_start'],'tool-start'), ('Output & limits',t['output_and_limits'],'tool-limits')]:
            content += group(heading(title)+paragraph(value), 'tool-section '+cls)
        content += block('paragraph', '<p class="tool-return"><a href="{{page:home}}#tools">&larr; Back to all tools</a></p>', {'className':'tool-return'})
        pages.append({'id':t['id'],'slug':t['slug'],'title':t['display_name'],'excerpt':t['description'],'template':'templates/tool.php','content':content,'social_slot':t['asset_slots']['social']})
    home = group(paragraph(site['home']['introduction'], 'lead') + paragraph(site['home']['supporting_line'], 'eyebrow') + block('buttons', '<div class="wp-block-buttons">'+button('Explore the tools ↓','#tools')+'</div>'), 'home-intro')
    home += group(paragraph('THE TOOLKIT','eyebrow')+heading('Find your next workflow.', anchor='tools'),'lineup-head') + group(cards,'tool-grid')
    pages.insert(0, {'id':'home','slug':'home','title':site['home']['heading'],'excerpt':site['home']['introduction'],'template':'','content':home,'social_slot':'site-social'})
    for s in site['support_pages']:
        path = ROOT / 'content' / (s['id']+'.html')
        body = path.read_text(encoding='utf8') if path.exists() else paragraph('Draft awaiting owner-approved contact details and the hosting privacy review.')
        pages.append({'id':s['id'],'slug':s['id'],'title':s['title'],'excerpt':'','template':'','content':body,'social_slot':'site-social'})
    pages.append({'id':'not-found','slug':'not-found-content','title':site['not_found']['title'],'excerpt':'','template':'','content':paragraph(site['not_found']['body'])+block('paragraph','<p><a href="{{page:home}}">'+text(site['not_found']['return_label'])+' &rarr;</a></p>'),'social_slot':None})
    config = {'site_title':'jvuorela.com','site_tagline':'PowerShell tools by Janne Vuorela','front_page':'home','privacy_page':'privacy','site_icon_slot':'site-icon','menus':{'primary':[{'title':'Tools','url':'{{page:home}}#tools'}], 'footer':[{'title':'Impressum','url':'{{page:impressum}}'},{'title':'Privacy','url':'{{page:privacy}}'},{'title':'GitHub','url':'https://github.com/PikkuJanne'},{'title':'Website source','url':'https://github.com/PikkuJanne/jvuorela.com'}]}}
    (ROOT / 'content/pages.json').write_text(json.dumps({'schema_version':1,'seed_only':True,'pages':pages,'configuration':config},indent=2,ensure_ascii=False)+'\n',encoding='utf8')
    print(f'Generated {len(pages)} editable draft Pages. Verified download links: {len(verified)}. No database writes.')


if __name__ == '__main__':
    main()
