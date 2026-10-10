// SPDX-License-Identifier: GPL-2.0-or-later
// Read-only package-template validation in the actual local Gutenberg Editor.
const {chromium} = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {execFileSync} = require('node:child_process');
const root = path.resolve(__dirname, '..');
const base = process.env.JVT_TEST_URL || 'http://127.0.0.1:8088';
const origin = new URL(base).origin;
const zip = path.resolve(process.env.JVT_TEST_SETUP_ZIP || path.join(root, 'build/manual-ui-dev/jvuorela-tools-content-media-0.1.1.zip'));
const sha256 = value => crypto.createHash('sha256').update(value).digest('hex');
const normalized = value => value.replace(/\r\n/g, '\n');
const canonicalRemote = value => value.replace(/\.git$/, '').replace(/\/$/, '');
const escapeAttribute = value => value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
function wp(...args) {
  return execFileSync(path.join(root, 'private/php/php.exe'), [path.join(root, 'private/wp-cli.phar'), '--path=' + path.join(root, 'private/wordpress'), ...args], {cwd: root, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim();
}
function nativeSnapshot(pages) {
  const encoded = Buffer.from(JSON.stringify(pages.map(page => ({id: page.id, native: page.wordpress_id})))).toString('base64');
  return JSON.parse(wp('eval', `
    if (!in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) { WP_CLI::error('Disposable loopback site required.'); }
    $out = array();
    foreach (json_decode(base64_decode('${encoded}'), true) as $row) {
      $post = get_post($row['native']);
      if (!$post || $post->post_type !== 'page') { WP_CLI::error('Native Page is unavailable.'); }
      $out[$row['id']] = array('url' => get_permalink($post), 'content' => $post->post_content, 'title' => $post->post_title, 'excerpt' => $post->post_excerpt, 'status' => $post->post_status, 'modified' => $post->post_modified_gmt, 'template' => get_page_template_slug($post));
    }
    WP_CLI::line(wp_json_encode($out));
  `));
}
const readZip = String.raw`
import hashlib,json,sys,zipfile
digest=lambda data:hashlib.sha256(data).hexdigest()
canon=lambda value:(json.dumps(value,sort_keys=True,ensure_ascii=False,indent=2)+'\n').encode('utf-8')
with zipfile.ZipFile(sys.argv[1]) as archive:
    names=archive.namelist()
    assert len(names)==len(set(names))
    assert all(name.startswith('jvuorela-tools-setup/') and '..' not in name.split('/') and '\\' not in name for name in names)
    entries={name:archive.read(name) for name in names}
    build_name='jvuorela-tools-setup/BUILD.json'
    build=json.loads(entries[build_name].decode('utf-8'))
    inventory=[{'path':name,'sha256':digest(data),'size_bytes':len(data)} for name,data in sorted(entries.items())]
    assert build['inputs']==[entry for entry in inventory if entry['path']!=build_name]
    seed=json.loads(entries['jvuorela-tools-setup/content/pages.json'].decode('utf-8'))
    expected={'jvuorela-tools-setup/page-templates/'+page['id']+'.html' for page in seed['pages']}
    assert len(expected)==11
    actual={name for name in names if '/page-templates/' in name and name.endswith('.html')}
    assert expected==actual
    templates=[]
    for page in seed['pages']:
        name='jvuorela-tools-setup/page-templates/'+page['id']+'.html'
        data=entries[name]
        assert data==page['content'].replace('\r\n','\n').encode('utf-8')
        templates.append({'page':page['id'],'html':data.decode('utf-8'),'sha256':digest(data)})
    readme=entries['jvuorela-tools-setup/page-templates/README.md'].decode('utf-8')
    print(json.dumps({'seed':seed,'templates':templates,'readme':readme,'build':build,'entries':len(names),'input_tree_sha256':digest(canon(inventory))}))
`;

(async () => {
  const result = {checked_at: new Date().toISOString(), scope: 'Packaged manual-setup HTML parsed read-only by registered Gutenberg core blocks on disposable local WordPress', native_save_invoked: false, editor_dispatch_invoked: false, cases: [], limits: ['No Page paste, content edit, save or publication was performed', 'Local block parsing does not establish hosted editor or IONOS acceptance']};
  let browser;
  let stage = 'package_identity';
  let pageMutationRequests = 0;
  let blockedExternal = 0;
  try {
    assert.ok(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Loopback test site required.');
    const index = JSON.parse(fs.readFileSync(path.join(path.dirname(zip), 'artifacts.json'), 'utf8'));
    const bytes = fs.readFileSync(zip);
    const artifact = index.artifacts.find(item => item.name === path.basename(zip));
    assert.ok(artifact, 'Setup artifact must be indexed.');
    assert.equal(sha256(bytes), artifact.sha256, 'Exact setup archive checksum required.');
    assert.equal(bytes.length, artifact.size_bytes, 'Exact archive size required.');
    const sums = fs.readFileSync(path.join(path.dirname(zip), 'SHA256SUMS'), 'utf8').trim().split(/\r?\n/).map(line => line.split(/\s+/));
    assert.equal(sums.find(row => row[1] === path.basename(zip))?.[0], artifact.sha256);
    assert.equal(sums.find(row => row[1] === 'artifacts.json')?.[0], sha256(fs.readFileSync(path.join(path.dirname(zip), 'artifacts.json'))));
    assert.match(index.source.commit, /^[a-f0-9]{40}$/);
    assert.equal(typeof index.source.dirty, 'boolean');
    execFileSync('git', ['cat-file', '-e', index.source.commit + '^{commit}'], {cwd: root, stdio: ['ignore', 'pipe', 'pipe']});
    const remote = execFileSync('git', ['remote', 'get-url', 'origin'], {cwd: root, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim();
    assert.equal(canonicalRemote(index.source.remote), canonicalRemote(remote));
    const packaged = JSON.parse(execFileSync(process.env.JVT_PYTHON_PATH || 'python', ['-c', readZip, zip], {cwd: root, maxBuffer: 8 * 1024 * 1024, stdio: ['ignore', 'pipe', 'pipe']}).toString('utf8'));
    assert.deepEqual(packaged.build.source, index.source);
    assert.equal(packaged.build.website_version, index.website_version);
    assert.equal(packaged.entries, artifact.entry_count);
    assert.equal(packaged.input_tree_sha256, artifact.input_tree_sha256);
    assert.ok(packaged.readme.includes('{{attachment:slot}}') && packaged.readme.includes('numeric') && packaged.readme.includes('before'));
    if (!index.source.dirty) {
      const committedPages = execFileSync('git', ['show', index.source.commit + ':content/pages.json'], {cwd: root, encoding: 'utf8', maxBuffer: 2 * 1024 * 1024});
      const committedReadme = execFileSync('git', ['show', index.source.commit + ':content/PAGE_TEMPLATES.md'], {cwd: root, encoding: 'utf8'});
      assert.deepEqual(packaged.seed, JSON.parse(committedPages));
      assert.equal(packaged.readme, normalized(committedReadme));
    }
    Object.assign(result, {setup_zip_name: artifact.name, setup_zip_sha256: artifact.sha256, package_source: index.source, source_dirty: index.source.dirty, provisional: index.source.dirty, acceptance: index.source.dirty ? 'provisional_dirty_development_package' : 'tested_clean_package_candidate', package_identity_verified: true, all_template_bytes_match_portable_pages: true, template_readme_included: true});
    stage = 'private_token_resolution';
    const auth = JSON.parse(fs.readFileSync(path.join(root, 'private/editor-auth.json'), 'utf8'));
    const editorUser = JSON.parse(wp('user', 'get', auth.username, '--format=json'));
    assert.ok((Array.isArray(editorUser.roles) ? editorUser.roles : editorUser.roles.split(',')).includes('editor'), 'Actual Editor account required.');
    result.wordpress_version = wp('core', 'version');
    const native = JSON.parse(fs.readFileSync(path.join(root, 'private/current-content.json'), 'utf8'));
    const pages = packaged.seed.pages.map(seed => native.pages.find(page => page.id === seed.id));
    assert.equal(pages.length, 11);
    assert.ok(pages.every(page => page && Number.isSafeInteger(page.wordpress_id) && page.wordpress_id > 0));
    const before = nativeSnapshot(pages);
    const media = new Map(native.media.map(item => [item.slot_id, item]));
    const mediaValues = [];
    const resolved = packaged.templates.map(template => {
      const counts = {attachment: 0, asset: 0, page: 0};
      const html = template.html.replace(/\{\{(attachment|asset|page):([a-z0-9_-]+)\}\}/g, (_, type, key) => {
        counts[type]++;
        if (type === 'page') {
          assert.ok(before[key]?.url && new URL(before[key].url).origin === origin, 'Actual native permalink required.');
          return escapeAttribute(before[key].url);
        }
        const item = media.get(key);
        assert.ok(item && Number.isSafeInteger(item.wordpress_id) && item.wordpress_id > 0 && new URL(item.url).origin === origin, 'Mapped private native media required.');
        mediaValues.push({id: item.wordpress_id, url: item.url});
        return type === 'attachment' ? String(item.wordpress_id) : escapeAttribute(item.url);
      });
      assert.ok(!html.includes('{{') && !html.includes('}}'), 'All portable tokens must resolve before parsing.');
      return {...template, html, counts};
    });
    stage = 'editor_authentication';
    browser = await chromium.launch({executablePath: process.env.JVT_BROWSER_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true});
    const context = await browser.newContext();
    await context.route('**/*', route => {
      const request = route.request();
      const url = new URL(request.url());
      if (url.origin !== origin) { blockedExternal++; return route.abort(); }
      const restPath = url.searchParams.get('rest_route') || url.pathname;
      const mutating = !['GET', 'HEAD', 'OPTIONS'].includes(request.method());
      const pageSave = /\/(?:wp\/v2|wp-json\/wp\/v2)\/(?:pages|posts)(?:\/|$)/.test(restPath);
      const classicSave = url.pathname.endsWith('/wp-admin/post.php');
      const autosave = url.pathname.endsWith('/wp-admin/admin-ajax.php') && /(?:^|&)action=autosave(?:&|$)/.test(request.postData() || '');
      if (mutating && (pageSave || classicSave || autosave)) { pageMutationRequests++; return route.abort(); }
      return route.continue();
    });
    const page = await context.newPage();
    page.setDefaultTimeout(25000);
    await page.goto(base + '/wp-login.php');
    await page.locator('#user_login').fill(auth.username);
    await page.locator('#user_pass').fill(auth.password);
    await Promise.all([page.waitForURL(/\/wp-admin\//), page.locator('#wp-submit').click()]);
    const hostPage = pages.find(item => item.id === 'winpdfmerger');
    const response = await page.goto(base + '/wp-admin/post.php?post=' + hostPage.wordpress_id + '&action=edit', {waitUntil: 'domcontentloaded'});
    assert.equal(response.status(), 200, 'Editor must open an ordinary native Page.');
    await page.waitForFunction(id => window.wp?.blocks?.parse && wp.data?.select('core/editor')?.getCurrentPostId() === id && wp.data.select('core/block-editor').getBlocks().length > 0, hostPage.wordpress_id);
    const authenticated = await page.evaluate(async () => (await wp.data.resolveSelect('core').getCurrentUser()).id);
    assert.equal(authenticated, Number(editorUser.ID));
    result.editor_account_role_verified = true;
    stage = 'registered_core_block_validation';
    const editorState = await page.evaluate(() => ({content: wp.data.select('core/editor').getCurrentPost().content, dirty: wp.data.select('core/editor').isEditedPostDirty(), serialized: wp.blocks.serialize(wp.data.select('core/block-editor').getBlocks())}));
    assert.equal(editorState.dirty, false);
    for (const template of resolved) {
      const parsed = await page.evaluate(({html, media}) => {
        const all = [];
        const walk = blocks => blocks.forEach(block => {all.push(block); walk(block.innerBlocks || []);});
        walk(wp.blocks.parse(html));
        const images = all.filter(block => block.name === 'core/image');
        return {block_count: all.length, all_blocks_core: all.every(block => block.name.startsWith('core/')), all_blocks_registered: all.every(block => Boolean(wp.blocks.getBlockType(block.name))), all_blocks_valid: all.every(block => block.isValid === true), all_image_ids_numeric_and_mapped: images.every(block => Number.isSafeInteger(block.attributes.id) && media.some(item => item.id === block.attributes.id && item.url === block.attributes.url)), block_types: Object.fromEntries([...new Set(all.map(block => block.name))].sort().map(name => [name, all.filter(block => block.name === name).length]))};
      }, {html: template.html, media: mediaValues});
      const valid = parsed.block_count > 0 && parsed.all_blocks_core && parsed.all_blocks_registered && parsed.all_blocks_valid && parsed.all_image_ids_numeric_and_mapped;
      result.cases.push({page: template.page, portable_template_sha256: template.sha256, token_counts: template.counts, no_unresolved_tokens: true, ...parsed, status: valid ? 'PASS' : 'FAIL'});
    }
    stage = 'native_content_preservation';
    const editorAfter = await page.evaluate(() => ({content: wp.data.select('core/editor').getCurrentPost().content, dirty: wp.data.select('core/editor').isEditedPostDirty(), serialized: wp.blocks.serialize(wp.data.select('core/block-editor').getBlocks())}));
    assert.deepEqual(editorAfter, editorState, 'Parsing must not change loaded editor content/state.');
    assert.deepEqual(nativeSnapshot(pages), before, 'All eleven native Page contents/settings must remain unchanged.');
    Object.assign(result, {native_page_content_unchanged: true, loaded_editor_content_unchanged: true, permalink_resolution: 'Read-only native WP-CLI get_permalink for all eleven Pages', pages_checked: result.cases.length, blocks_checked: result.cases.reduce((sum, item) => sum + item.block_count, 0), all_eleven_valid: result.cases.length === 11 && result.cases.every(item => item.status === 'PASS'), page_mutation_requests: pageMutationRequests, external_requests_blocked: blockedExternal, browser: await browser.version()});
    assert.ok(result.all_eleven_valid && pageMutationRequests === 0, 'All templates must validate without Page save requests.');
    result.status = 'PASS';
  } catch (_) {
    result.status = 'FAIL';
    result.failure_stage = stage;
    result.failure = 'Packaged template verification failed; private inputs and native content were not published.';
    result.page_mutation_requests = pageMutationRequests;
  } finally {
    if (browser) await browser.close();
    fs.writeFileSync(path.join(root, 'evidence/manual-templates.json'), JSON.stringify(result, null, 2) + '\n');
  }
  const sourceLabel = result.package_identity_verified ? (result.provisional ? 'provisional dirty development package' : 'clean package candidate') : 'package identity unverified';
  console.log(`Manual template verification ${result.status}: ${result.cases.length}/11 templates; ${result.blocks_checked || 0} registered core blocks; ${pageMutationRequests} Page save requests; ${sourceLabel}.`);
  if (result.status !== 'PASS') process.exitCode = 1;
})().catch(() => {console.error('Manual template verification could not initialize; inspect local inputs privately.'); process.exitCode = 1;});
