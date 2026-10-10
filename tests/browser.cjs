// SPDX-License-Identifier: GPL-2.0-or-later
// Actual loopback WordPress HTTP/layout tests; Playwright is a development dependency.
const {chromium} = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname,'..');
const base = process.env.JVT_TEST_URL || 'http://127.0.0.1:8088';
if (!['127.0.0.1','localhost'].includes(new URL(base).hostname)) throw Error('Tests require disposable loopback preview.');
const tools = JSON.parse(fs.readFileSync(path.join(root,'content/tools.json'),'utf8')).tools;
const executablePath = process.env.JVT_BROWSER_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
(async()=>{
  const browser = await chromium.launch({executablePath,headless:true});
  const context = await browser.newContext({javaScriptEnabled:false,viewport:{width:1440,height:900},reducedMotion:'reduce'});
  const page = await context.newPage();
  const requests = new Set(); const errors=[]; const cases=[];
  page.on('request',req=>requests.add(req.url()));
  page.on('pageerror',e=>errors.push(e.message));
  fs.mkdirSync(path.join(root,'evidence/screenshots'),{recursive:true});
  const routes=['/',...tools.map(t=>t.route),'/impressum/','/privacy/'];
  for (const width of [320,390,768,1024,1440]) {
    await page.setViewportSize({width,height:width<600?844:900});
    for (const route of routes) {
      const response=await page.goto(base+route,{waitUntil:'networkidle'});
      assert.equal(response.status(),200,route+' status');
      assert.equal(await page.locator('h1').count(),1,route+' one H1');
      const layout=await page.evaluate(()=>({scroll:document.documentElement.scrollWidth,width:window.innerWidth}));
      assert.ok(layout.scroll<=width+1,`${route} overflow at ${width}: ${layout.scroll}`);
      const broken=await page.locator('img').evaluateAll(imgs=>imgs.filter(i=>i.complete&&i.naturalWidth===0).map(i=>i.src));
      assert.deepEqual(broken,[],route+' images');
      const hashLinks=await page.locator('a[href="#"]').count(); assert.equal(hashLinks,0,'No dummy actions');
      if ([390,1440].includes(width)||width===1024&&['/','/winpdfmerger/'].includes(route)) {
        // Native lazy images load on actual scrolling; full-page screenshots alone do not scroll.
        for(let y=0;y<await page.evaluate(()=>document.documentElement.scrollHeight);y+=600){await page.evaluate(y=>window.scrollTo(0,y),y);await page.waitForTimeout(80);}
        await page.evaluate(()=>window.scrollTo(0,0));
        await page.locator('img').evaluateAll(imgs=>Promise.all(imgs.map(i=>i.complete?Promise.resolve():new Promise(resolve=>{i.onload=resolve;i.onerror=resolve}))));
        const filename=(route==='/'?'home':route.replaceAll('/',''))+`-${width}.png`;
        await page.screenshot({path:path.join(root,'evidence/screenshots',filename),fullPage:true});
      }
      cases.push({route,width,http_status:response.status(),overflow:false,one_h1:true,images_loaded:true,js_enabled:false});
      process.stdout.write('.');
    }
  }
  const missing=await page.goto(base+'/this-route-does-not-exist',{waitUntil:'networkidle'});
  assert.equal(missing.status(),404,'True unknown-route 404');
  assert.match(await page.locator('h1').innerText(),/Page not found/);
  await page.goto(base+'/',{waitUntil:'networkidle'});
  assert.equal(await page.locator('.tool-card').count(),7,'Seven cards');
  for(const t of tools) assert.equal(await page.locator(`.tool-card a[href$="${t.route}"]`).count(),1,'Real card '+t.id);
  await page.keyboard.press('Tab'); assert.equal(await page.locator(':focus').innerText(),'Skip to content');
  for(const t of tools){
    await page.goto(base+t.route,{waitUntil:'networkidle'});
    assert.equal(await page.locator('link[rel="canonical"]').count(),1,'One canonical');
    assert.equal(await page.locator('meta[name="description"]').count(),1,'One description');
    const social=await page.locator('meta[property="og:image"]').getAttribute('content');
    assert.ok(social.startsWith(base),'Local sharing image');
    assert.equal((await context.request.get(social)).status(),200,'Sharing image HTTP');
    const hero=page.locator('.tool-art img');
    assert.equal(await hero.getAttribute('loading'),'eager');
    assert.equal(await hero.getAttribute('fetchpriority'),'high');
    assert.ok(await hero.getAttribute('srcset'),'Native responsive art');
    const button=page.getByRole('link',{name:'Download v1.0.0',exact:true});
    if(['winbooksplit','audiofileforphone'].includes(t.id)) assert.equal(await button.count(),0,'Absent release no invented download');
    else assert.equal(await button.getAttribute('href'),t.repo_url+'/releases/tag/v1.0.0','Exact verified release');
  }
  const external=[...requests].filter(u=>!u.startsWith(base)&&!u.startsWith('data:'));
  assert.deepEqual(external,[],'No third-party signed-out requests');
  assert.deepEqual(errors,[],'No page errors');
  const result={checked_at:new Date().toISOString(),browser:await browser.version(),executable:'Chrome or Edge via explicit local executable',environment:'Local WordPress 7.1.3/PHP8.3.35/SQLite3.1.0',cases,unknown_route_status:404,release_links:5,missing_releases:2,no_javascript:true,reduced_motion:true,keyboard_skip_link:true,external_requests:external,errors,limits:['Hosted IONOS not tested','Firefox/Safari and manual 200% zoom pending','Legal content is draft','No Lighthouse/field performance claim']};
  fs.writeFileSync(path.join(root,'evidence/browser-runtime.json'),JSON.stringify(result,null,2)+'\n');
  console.log('\nBrowser runtime checks passed:',cases.length,'route/viewport cases.');
  await browser.close();
})().catch(error=>{console.error(error);process.exit(1);});
