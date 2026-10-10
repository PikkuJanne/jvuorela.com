// SPDX-License-Identifier: GPL-2.0-or-later
// Read-only block validation through the actual local WordPress Editor account.
const {chromium} = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const root = path.resolve(__dirname, '..');
const base = process.env.JVT_TEST_URL || 'http://127.0.0.1:8088';
const origin = new URL(base).origin;
if (!['127.0.0.1', 'localhost'].includes(new URL(base).hostname)) throw Error('Read-only editor checks require a disposable loopback site.');
const normalized = value => value.replace(/\r\n/g, '\n').trim();
async function readLoadedBlocks(page, privatePostId) {
  await page.waitForFunction(id => window.wp?.data?.select('core/editor')?.getCurrentPostId() === id && window.wp?.data?.select('core/block-editor')?.getBlocks()?.length > 0, privatePostId);
  return page.evaluate(() => {
    const all = [];
    const walk = blocks => blocks.forEach(block => { all.push({name: block.name, isValid: block.isValid === true}); walk(block.innerBlocks || []); });
    walk(wp.data.select('core/block-editor').getBlocks());
    return {blocks: all, dirty: wp.data.select('core/editor').isEditedPostDirty(), content: wp.data.select('core/editor').getCurrentPost().content};
  });
}
function blockResult(loaded) {
  return {
    editor_loaded: true,
    block_count: loaded.blocks.length,
    all_blocks_core: loaded.blocks.every(block => block.name.startsWith('core/')),
    all_blocks_valid: loaded.blocks.every(block => block.isValid),
    unsaved_changes: loaded.dirty,
    block_types: Object.fromEntries([...new Set(loaded.blocks.map(b => b.name))].sort().map(name => [name, loaded.blocks.filter(b => b.name === name).length])),
  };
}

(async () => {
  const auth = JSON.parse(fs.readFileSync(path.join(root, 'private/editor-auth.json'), 'utf8'));
  const native = JSON.parse(fs.readFileSync(path.join(root, 'private/current-content.json'), 'utf8'));
  const seed = JSON.parse(fs.readFileSync(path.join(root, 'content/pages.json'), 'utf8'));
  const labels = new Map(seed.pages.map(p => [p.id, p.id === 'home' ? 'Home' : p.title]));
  const pages = native.pages.filter(p => labels.has(p.id));
  assert.equal(pages.length, 11, 'Exactly eleven current managed Pages are required.');
  assert.equal(new Set(pages.map(p => p.id)).size, 11, 'Managed Page markers must be unique.');
  assert.ok(pages.every(p => Number.isSafeInteger(p.wordpress_id) && p.wordpress_id > 0), 'Private native Page IDs must be valid.');
  const ownerImpressum = normalized(fs.readFileSync(path.join(root, 'content/impressum.html'), 'utf8'));
  let browser;
  let blockedExternal = 0;
  let pageMutationRequests = 0;
  const cases = [];
  const privateDebug = [];
  const result = {
    checked_at: new Date().toISOString(),
    scope: 'Read-only current local native Page validation; not hosted editor acceptance',
    role_scope: 'Editor on ordinary Pages; owner administrator on the configured Privacy policy Page',
    native_save_invoked: false,
    cases,
    limits: ['Local disposable WordPress only; production editor was not accessed', 'Content edit/save and exact package reinstall survival are recorded separately'],
  };
  try {
    browser = await chromium.launch({executablePath: process.env.JVT_BROWSER_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true});
    const context = await browser.newContext({viewport: {width: 1440, height: 1000}});
    const guardRequest = async route => {
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
    };
    await context.route('**/*', guardRequest);
    const page = await context.newPage();
    page.setDefaultTimeout(25000);
    await page.goto(base + '/wp-login.php');
    await page.locator('#user_login').fill(auth.username);
    await page.locator('#user_pass').fill(auth.password);
    await Promise.all([page.waitForURL(/\/wp-admin\//), page.locator('#wp-submit').click()]);
    await page.goto(base + '/wp-admin/edit.php?post_type=page');
    const editLinks = await page.locator('a.row-title').evaluateAll(links => links.map(a => a.href));
    for (const current of pages) {
      const record = {page: labels.get(current.id), role: 'editor', editor_loaded: false, all_blocks_core: false, all_blocks_valid: false};
      try {
        const editLink = editLinks.find(href => Number(new URL(href).searchParams.get('post')) === current.wordpress_id);
        const href = editLink || base + '/wp-admin/post.php?post=' + current.wordpress_id + '&action=edit';
        const response = await page.goto(href, {waitUntil: 'domcontentloaded'});
        record.http_status = response?.status() || null;
        if (record.http_status !== 200) throw Error('Native Editor route is not available to the Editor account.');
        const loaded = await readLoadedBlocks(page, current.wordpress_id);
        Object.assign(record, blockResult(loaded));
        if (current.id === 'impressum') {
          record.owner_approved_source_matches = normalized(loaded.content) === ownerImpressum;
          record.owner_source_sha256 = crypto.createHash('sha256').update(ownerImpressum).digest('hex');
        }
        assert.ok(record.all_blocks_core && record.all_blocks_valid && loaded.dirty === false, 'Current native blocks must all validate without unsaved changes.');
        if (current.id === 'impressum') assert.ok(record.owner_approved_source_matches, 'Current Impressum must match its owner-approved source.');
        record.status = 'PASS';
      } catch (error) {
        record.status = 'FAIL';
        record.failure = record.editor_loaded ? 'Native block or approved-source validation failed' : 'Native Editor access or loading failed';
        privateDebug.push({page: current.id, error: String(error), editor_url: page.url()});
      }
      cases.push(record);
    }
    // Core maps editing its configured policy Page to manage_privacy_options
    // (manage_options on this single site). Verify only that Page as its owner;
    // do not broaden Editor capabilities or change the native setting.
    const privacy = pages.find(current => current.id === 'privacy');
    const privacyResult = cases.find(record => record.page === labels.get('privacy'));
    if (privacyResult && privacyResult.http_status === 403) {
      privacyResult.editor_access = {http_status: 403, outcome: 'Native WordPress privacy-policy restriction', required_capability: 'manage_privacy_options', single_site_mapping: 'manage_options'};
      const adminAuth = JSON.parse(fs.readFileSync(path.join(root, 'private/local-auth.json'), 'utf8'));
      const adminContext = await browser.newContext({viewport: {width: 1440, height: 1000}});
      await adminContext.route('**/*', guardRequest);
      const adminPage = await adminContext.newPage();
      adminPage.setDefaultTimeout(25000);
      try {
        await adminPage.goto(base + '/wp-login.php');
        await adminPage.locator('#user_login').fill(adminAuth.username);
        await adminPage.locator('#user_pass').fill(adminAuth.password);
        await Promise.all([adminPage.waitForURL(/\/wp-admin\//), adminPage.locator('#wp-submit').click()]);
        const response = await adminPage.goto(base + '/wp-admin/post.php?post=' + privacy.wordpress_id + '&action=edit', {waitUntil: 'domcontentloaded'});
        assert.equal(response?.status(), 200, 'Privacy owner must be allowed to open its native Editor.');
        const loaded = await readLoadedBlocks(adminPage, privacy.wordpress_id);
        Object.assign(privacyResult, blockResult(loaded), {role: 'administrator', http_status: response.status()});
        assert.ok(privacyResult.all_blocks_core && privacyResult.all_blocks_valid && loaded.dirty === false, 'Owner Privacy blocks must all validate without unsaved changes.');
        privacyResult.status = 'PASS';
        delete privacyResult.failure;
      } catch (error) {
        privacyResult.status = 'FAIL';
        privacyResult.failure = 'Owner Privacy native Editor access or block validation failed';
        privateDebug.push({page: 'privacy', role: 'administrator', error: String(error), editor_url: adminPage.url()});
      } finally {
        await adminContext.close();
      }
    }
    result.browser = await browser.version();
    result.pages_checked = cases.length;
    result.blocks_checked = cases.reduce((n, entry) => n + (entry.block_count || 0), 0);
    result.all_eleven_valid = cases.length === 11 && cases.every(entry => entry.status === 'PASS');
    result.page_mutation_requests = pageMutationRequests;
    result.external_requests_blocked = blockedExternal;
    result.status = result.all_eleven_valid && pageMutationRequests === 0 ? 'PASS' : 'FAIL';
  } catch (error) {
    result.status = 'FAIL';
    result.failure = 'Editor account validation could not complete';
    privateDebug.push({error: String(error)});
  } finally {
    if (browser) await browser.close();
    fs.writeFileSync(path.join(root, 'evidence/editor-pages.json'), JSON.stringify(result, null, 2) + '\n');
    fs.writeFileSync(path.join(root, 'private/editor-pages-debug.json'), JSON.stringify(privateDebug, null, 2) + '\n');
  }
  console.log(`Read-only Editor validation ${result.status}: ${result.pages_checked || cases.length}/11 Pages, ${result.blocks_checked || 0} blocks, ${pageMutationRequests} Page save requests.`);
  if (result.status !== 'PASS') process.exitCode = 1;
})().catch(() => { console.error('Read-only editor checks failed before browser validation; inspect local setup inputs privately.'); process.exitCode = 1; });
