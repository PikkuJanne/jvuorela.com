// SPDX-License-Identifier: GPL-2.0-or-later
// Disposable loopback browser measurements; not hosted/field performance.
const {chromium,firefox}=require('playwright');
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),zlib=require('node:zlib');
const root=path.resolve(__dirname,'..'),base=process.env.JVT_TEST_URL||'http://127.0.0.1:8088';
if(!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Loopback only');
const tools=JSON.parse(fs.readFileSync(path.join(root,'content/tools.json'))).tools;
const manifest=JSON.parse(fs.readFileSync(path.join(root,'assets/manifest.json')));
const routes=['/',...tools.map(t=>t.route),'/impressum/','/privacy/'];
const imageBoxes=()=>[...document.querySelectorAll('.tool-art img,.tool-card img')].map(img=>{
 const r=img.getBoundingClientRect(),css=getComputedStyle(img);
 return{selected_url:img.currentSrc,rendered_width:r.width,rendered_height:r.height,natural_width:img.naturalWidth,natural_height:img.naturalHeight,object_fit:css.objectFit,loading:img.loading,srcset:img.srcset,sizes:img.sizes};
});
(async()=>{
 const result={checked_at:new Date().toISOString(),source_state:'Current local working tree; final clean artifact identity recorded separately',environment:'Windows loopback WordPress7.1.3/PHP8.3.35/SQLite3.1.0',browsers:[],artwork:[],performance:[],limits:['IONOS/database/hosting not tested','Safari unavailable on this Windows host','Loopback lab measurements are not real-user or hosted performance','200% equivalent viewport reflow checked; native browser zoom observation recorded separately']};
 for(const spec of [{name:'Edge',type:chromium,opts:{executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'}},{name:'Firefox',type:firefox,opts:{}}]){
  const browser=await spec.type.launch({...spec.opts,headless:true});
  const context=await browser.newContext({javaScriptEnabled:false,reducedMotion:'reduce',viewport:{width:1440,height:900}}),page=await context.newPage();
  const cases=[];
  for(const width of [390,1440]){
   await page.setViewportSize({width,height:width===390?844:900});
   for(const route of routes){
    const response=await page.goto(base+route,{waitUntil:'networkidle'});assert.equal(response.status(),200);
    assert.equal(await page.locator('h1').count(),1);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),route+' overflow');
    assert.equal(await page.locator('a[href="#"]').count(),0);
    if(route==='/'||tools.some(t=>t.route===route)){
     for(let y=0;y<await page.evaluate(()=>document.documentElement.scrollHeight);y+=650){await page.evaluate(y=>scrollTo(0,y),y);await page.waitForTimeout(80);}
     // Visit each image rather than jumping past Firefox's lazy-load threshold.
     for(const img of await page.locator('img').all()){
      await img.scrollIntoViewIfNeeded();
      // Poll from Node: Firefox disables page timers/handlers with page JS off.
      for(let retry=0;retry<50;retry++){if(await img.evaluate(i=>i.complete&&i.naturalWidth>0))break;await new Promise(resolve=>setTimeout(resolve,100));}
     }
     const boxes=await page.evaluate(imageBoxes);assert.ok(boxes.every(i=>i.natural_width>0&&i.object_fit==='contain'));
     if(spec.name==='Edge')result.artwork.push({route,viewport:{width,height:width===390?844:900},boxes:boxes.map(b=>({...b,selected_url:b.selected_url.replace(base,'{local-origin}')}))});
    }
    cases.push({route,width,status:200,no_overflow:true,one_h1:true});process.stdout.write('.');
   }
  }
  await page.setViewportSize({width:720,height:450});
  for(const route of ['/',...tools.map(t=>t.route)]){
   await page.goto(base+route,{waitUntil:'networkidle'});
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
   assert.ok(await page.locator('nav a').first().isVisible());
  }
  await page.goto(base+'/',{waitUntil:'networkidle'});
  const focus=[];
  for(let i=0;i<12;i++){await page.keyboard.press('Tab');focus.push(await page.locator(':focus').evaluate(el=>({text:el.textContent.trim(),outline:getComputedStyle(el).outlineStyle,href:el.getAttribute('href')})));}
  assert.equal(focus[0].text,'Skip to content');
  assert.ok(focus.every(f=>f.outline==='solid'),'Visible keyboard focus');
  const beforeZoom=await page.evaluate(()=>({inner_width:innerWidth,dpr:devicePixelRatio,visual_scale:visualViewport.scale}));
  await page.keyboard.press('Control++');await page.keyboard.press('Control++');await page.keyboard.press('Control++');await page.keyboard.press('Control++');
  const afterZoom=await page.evaluate(()=>({inner_width:innerWidth,dpr:devicePixelRatio,visual_scale:visualViewport.scale}));
  result.browsers.push({name:spec.name,version:await browser.version(),javascript:false,cases,keyboard_focus:focus.map(f=>({...f,href:f.href&&f.href.replace(base,'{local-origin}')})),reflow_200_percent_equivalent:{baseline_viewport:'1440x900',effective_viewport:'720x450',routes:8,status:'PASS'},native_zoom_shortcut:{before:beforeZoom,after:afterZoom,changed:JSON.stringify(beforeZoom)!==JSON.stringify(afterZoom)}});
  await browser.close();
 }
 const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 for(const route of ['/','/winpdfmerger/']){
  const context=await browser.newContext({viewport:{width:390,height:844},reducedMotion:'reduce'}),page=await context.newPage(),cdp=await context.newCDPSession(page);
  await cdp.send('Network.enable');await cdp.send('Network.setCacheDisabled',{cacheDisabled:true});
  await cdp.send('Network.emulateNetworkConditions',{offline:false,latency:150,downloadThroughput:200000,uploadThroughput:93750});
  await cdp.send('Emulation.setCPUThrottlingRate',{rate:4});
  await page.addInitScript(()=>{window.jvtPerf={cls:0,lcp:0};new PerformanceObserver(list=>{for(const e of list.getEntries())if(!e.hadRecentInput)window.jvtPerf.cls+=e.value}).observe({type:'layout-shift',buffered:true});new PerformanceObserver(list=>{for(const e of list.getEntries())window.jvtPerf.lcp=e.startTime}).observe({type:'largest-contentful-paint',buffered:true});});
  await page.goto(base+route,{waitUntil:'networkidle'});await page.waitForTimeout(1800);
  const perf=await page.evaluate(()=>({...window.jvtPerf,resources:performance.getEntriesByType('resource').map(e=>({name:e.name,encoded_body_bytes:e.encodedBodySize,transfer_bytes:e.transferSize,duration_ms:e.duration})),navigation_bytes:performance.getEntriesByType('navigation')[0].transferSize}));
  const background=perf.resources.filter(r=>r.name.includes('site-background-'));
  assert.equal(background.length,1,'Only one mobile/desktop background fetched');assert.ok(background[0].name.includes('mobile'));
  result.performance.push({route,viewport:'390x844',browser:await browser.version(),network:'CDP 150ms latency, 200000B/s download, 93750B/s upload; cache disabled',cpu:'4x CDP slowdown on this Windows host',observation:'First viewport, no scrolling, 1800ms after networkidle',lcp_ms:perf.lcp,cls:perf.cls,first_view_transfer_bytes:perf.navigation_bytes+perf.resources.reduce((s,r)=>s+r.transfer_bytes,0),resources:perf.resources.map(r=>({...r,name:r.name.replace(base,'{local-origin}')})),targets:{transfer_bytes:1572864,lcp_ms:2500,cls:0.1}});
  await context.close();
 }
 await browser.close();
 const css=fs.readFileSync(path.join(root,'theme/jvuorela-tools/assets/css/site.css'));
 result.theme_css={uncompressed_bytes:css.length,gzip_bytes:zlib.gzipSync(css).length,target_gzip_bytes:50000};
 result.source_dimensions=manifest.slots.filter(s=>s.kind==='character'||s.kind==='avatar').map(s=>({slot_id:s.slot_id,metadata:s.actual_metadata}));
 fs.writeFileSync(path.join(root,'evidence/layout-performance.json'),JSON.stringify(result,null,2)+'\n');
 console.log('Edge/Firefox routes, keyboard focus, artwork boxes and mobile lab measurements passed.');
})().catch(e=>{console.error(e);process.exit(1)});
