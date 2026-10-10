// SPDX-License-Identifier: GPL-2.0-or-later
// Native admin save + signed-out 404 rendering on the disposable loopback site.
const {chromium} = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');
const root = path.resolve(__dirname, '..');
const base = 'http://127.0.0.1:8088';
const php = path.join(root, 'private/php/php.exe');
const cli = path.join(root, 'private/wp-cli.phar');
function wp(...args) {
  return execFileSync(php, [cli, '--path=' + path.join(root, 'private/wordpress'), '--user=1', ...args], {cwd: root, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim();
}
(async () => {
  const auth = JSON.parse(fs.readFileSync(path.join(root, 'private/local-auth.json'), 'utf8'));
  const fixture = JSON.parse(wp('eval', `
    if (wp_parse_url(home_url('/'), PHP_URL_HOST) !== '127.0.0.1' || !current_user_can('manage_options')) { WP_CLI::error('Disposable loopback administrator required.'); }
    $previous = get_option('jvt_404_page', null);
    $id = wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Native 404 selection check','post_content'=>'<!-- wp:paragraph --><p>Native admin 404 selection fixture.</p><!-- /wp:paragraph -->'), true);
    if (is_wp_error($id)) { WP_CLI::error('Could not allocate native fixture.'); }
    WP_CLI::line(wp_json_encode(array('id'=>$id,'previous'=>$previous)));
  `));
  assert.ok(Number.isSafeInteger(fixture.id) && fixture.id > 0);
  let browser;
  const result = {checked_at: new Date().toISOString(), scope: 'Native Settings Reading form on disposable local WordPress; not hosted acceptance', page_save_for_existing_content: false, fixture_has_import_marker: false, tests: [], status: 'FAIL'};
  try {
    browser = await chromium.launch({executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true});
    const context = await browser.newContext();
    await context.route('**/*', route => new URL(route.request().url()).origin === base ? route.continue() : route.abort());
    const page = await context.newPage();
    await page.goto(base + '/wp-login.php');
    await page.locator('#user_login').fill(auth.username);
    await page.locator('#user_pass').fill(auth.password);
    await Promise.all([page.waitForURL(/\/wp-admin\//), page.locator('#wp-submit').click()]);
    await page.goto(base + '/wp-admin/options-reading.php');
    const selector = page.locator('#jvt_404_page');
    assert.equal(await selector.count(), 1, 'Native selector must exist.');
    await selector.selectOption(String(fixture.id));
    await Promise.all([page.waitForURL(/options-reading\.php\?settings-updated=true/), page.getByRole('button', {name: 'Save Changes', exact: true}).click()]);
    assert.equal(await selector.inputValue(), String(fixture.id));
    result.tests.push('Administrator selects an unmarked Page and saves the native Reading form with core nonce/capability protection');
    const publicContext = await browser.newContext({javaScriptEnabled: false});
    const signedOut = await publicContext.newPage();
    const response = await signedOut.goto(base + '/jvt-native-selector-missing-route/');
    assert.equal(response.status(), 404);
    assert.equal(await signedOut.getByRole('heading', {name: 'Native 404 selection check', exact: true}).count(), 1);
    assert.equal(await signedOut.getByText('Native admin 404 selection fixture.', {exact: true}).count(), 1);
    assert.equal(await signedOut.getByRole('link', {name: 'Impressum', exact: true}).count(), 1);
    assert.equal(await signedOut.getByRole('link', {name: 'Privacy', exact: true}).count(), 1);
    result.tests.push('Signed-out JavaScript-disabled missing route remains HTTP404 and uses the selected native title/content with separate Impressum/Privacy links');
    const zip = process.env.JVT_TEST_THEME_ZIP;
    assert.ok(zip && fs.existsSync(zip), 'Exact candidate ZIP is required.');
    const index = JSON.parse(fs.readFileSync(path.join(path.dirname(zip), 'artifacts.json'), 'utf8'));
    const digest = require('node:crypto').createHash('sha256').update(fs.readFileSync(zip)).digest('hex');
    assert.equal(index.artifacts.find(asset => asset.name === path.basename(zip)).sha256, digest);
    wp('theme', 'install', path.resolve(zip), '--force', '--activate');
    await page.reload();
    assert.equal(await selector.inputValue(), String(fixture.id));
    await signedOut.reload();
    assert.equal(await signedOut.getByText('Native admin 404 selection fixture.', {exact: true}).count(), 1);
    result.tests.push('Native selected Page and editable content survive reinstall/activation of the exact candidate theme ZIP');
    result.theme_zip_sha256 = digest;
    result.package_source = index.source;
    result.browser = await browser.version();
    result.status = 'PASS';
  } finally {
    if (browser) await browser.close();
    const previous = JSON.stringify(fixture.previous);
    // The restoration value comes from the original native option, never from
    // user wording or shell interpolation. No unrelated Page is deleted.
    const previousBase64 = Buffer.from(previous).toString('base64');
    wp('eval', `$previous = json_decode(base64_decode('${previousBase64}'), true); if ($previous === null) { delete_option('jvt_404_page'); } else { update_option('jvt_404_page', $previous); } wp_delete_post(${fixture.id}, true); if (get_post(${fixture.id}) || get_option('jvt_404_page', null) !== $previous) { WP_CLI::error('Fixture restoration failed.'); } WP_CLI::success('Owned fixture removed; original selector restored.');`);
    result.fixture_cleanup = 'Only the allocated Page deleted; original selector restored exactly; no existing Page or Media content modified';
    fs.writeFileSync(path.join(root, 'evidence/settings-404.json'), JSON.stringify(result, null, 2) + '\n');
  }
  console.log('Native 404 admin selection, signed-out rendering and exact ZIP reinstall: PASS; fixture restored.');
})().catch(() => { console.error('Native 404 settings check failed; inspect local inputs privately.'); process.exitCode = 1; });
