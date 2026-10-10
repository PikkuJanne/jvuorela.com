// SPDX-License-Identifier: GPL-2.0-or-later
const {chromium}=require('playwright');
const fs=require('node:fs'); const path=require('node:path'); const assert=require('node:assert/strict'); const {execFileSync}=require('node:child_process');
const root=path.resolve(__dirname,'..'); const base='http://127.0.0.1:8088';
(async()=>{
  const auth=JSON.parse(fs.readFileSync(path.join(root,'private/editor-auth.json'),'utf8'));
  const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
  const page=await browser.newPage({viewport:{width:1440,height:1000}});
  await page.goto(base+'/wp-login.php');
  await page.locator('#user_login').fill(auth.username); await page.locator('#user_pass').fill(auth.password);
  await Promise.all([page.waitForURL(/wp-admin/),page.locator('#wp-submit').click()]);
  await page.goto(base+'/wp-admin/edit.php?post_type=page');
  const link=page.locator('a.row-title').filter({hasText:'WinPDFMerger'}).first();
  assert.equal(await link.count(),1); await link.click();
  await page.waitForFunction(()=>window.wp?.data?.select('core/editor')?.getCurrentPostId());
  await page.waitForFunction(()=>window.wp?.data?.select('core/block-editor')?.getBlocks()?.length>0);
  const close=page.locator('.components-modal__header button[aria-label*="Close"]');
  if(await close.count()) await close.first().click();
  const parsed=await page.evaluate(()=>{
    const content=wp.data.select('core/editor').getCurrentPost().content;
    const blocks=wp.blocks.parse(content);
    const all=[];const walk=xs=>xs.forEach(b=>{all.push({name:b.name,isValid:b.isValid,attributes:b.attributes,issues:b.validationIssues?.map(x=>x.args?.[0])});walk(b.innerBlocks||[])});walk(blocks);
    return {blocks:all,postId:wp.data.select('core/editor').getCurrentPostId()};
  });
  assert.ok(parsed.blocks.length>15); if(parsed.blocks.some(b=>!b.isValid)){console.log(JSON.stringify(parsed.blocks.filter(b=>!b.isValid),null,2));}
  assert.ok(parsed.blocks.every(b=>b.isValid),'Every core block validates in actual editor');
  const original=await page.evaluate(()=>wp.data.select('core/editor').getCurrentPost().content);
  fs.writeFileSync(path.join(root,'private/editor-original.json'),JSON.stringify({content:original}));
  const changes=await page.evaluate(()=>{
    const blocks=wp.data.select('core/block-editor').getBlocks();
    const flat=[];const walk=xs=>xs.forEach(b=>{flat.push(b);walk(b.innerBlocks||[])});walk(blocks);
    const tagline=flat.find(b=>b.name==='core/paragraph'&&b.attributes.className==='tagline');
    const benefit=flat.find(b=>b.name==='core/list-item');
    const requirements=flat.find(b=>b.name==='core/group'&&b.attributes.className?.includes('tool-requirements')).innerBlocks.find(b=>b.name==='core/paragraph');
    const button=flat.find(b=>b.name==='core/button'&&b.attributes.url?.includes('/releases/tag/v1.0.0'));
    const edits=[tagline,benefit,requirements].map(b=>({id:b.clientId,content:b.attributes.content+' [local editor check]'}));
    edits.forEach(e=>wp.data.dispatch('core/block-editor').updateBlockAttributes(e.id,{content:e.content}));
    const changedUrl=button.attributes.url+'?local_editor_check=1';
    wp.data.dispatch('core/block-editor').updateBlockAttributes(button.clientId,{url:changedUrl});
    return {texts:edits.map(e=>e.content),changedUrl};
  });
  await page.getByRole('button',{name:'Save',exact:true}).click();
  await page.waitForFunction(()=>!wp.data.select('core/editor').isSavingPost()&&!wp.data.select('core/editor').isEditedPostDirty());
  const publicContext=await browser.newContext();const frontend=await publicContext.newPage();
  async function verifyEdits(){await frontend.goto(base+'/winpdfmerger/',{waitUntil:'networkidle'});assert.equal(await frontend.getByText('[local editor check]',{exact:false}).count(),3);assert.equal(await frontend.getByRole('link',{name:'Download v1.0.0',exact:true}).getAttribute('href'),changes.changedUrl);}
  await verifyEdits();
  await page.reload();await page.waitForFunction(()=>window.wp?.data?.select('core/block-editor')?.getBlocks()?.length>0);
  const zip=process.env.JVT_TEST_THEME_ZIP;
  assert.ok(zip&&fs.existsSync(zip),'Exact package required for edit-survival test');
  execFileSync(path.join(root,'private/php/php.exe'),[path.join(root,'private/wp-cli.phar'),'theme','install',path.resolve(zip),'--force','--activate','--path='+path.join(root,'private/wordpress')],{cwd:root,stdio:'pipe'});
  await verifyEdits();
  await page.screenshot({path:path.join(root,'evidence/screenshots/editor-winpdfmerger.png'),fullPage:true});
  // Restore the reviewed draft wording through the same native editor save route.
  await page.evaluate(content=>{wp.data.dispatch('core/block-editor').resetBlocks(wp.blocks.parse(content));},original);
  await page.getByRole('button',{name:'Save',exact:true}).click();
  await page.waitForFunction(()=>!wp.data.select('core/editor').isSavingPost()&&!wp.data.select('core/editor').isEditedPostDirty());
  await frontend.reload(); assert.equal(await frontend.getByText('[local editor check]',{exact:false}).count(),0);
  const index=JSON.parse(fs.readFileSync(path.join(path.dirname(zip),'artifacts.json')));
  const digest=require('node:crypto').createHash('sha256').update(fs.readFileSync(zip)).digest('hex');
  assert.equal(index.artifacts.find(a=>a.name===path.basename(zip)).sha256,digest,'Reinstalled ZIP matches its artifact identity');
  fs.writeFileSync(path.join(root,'evidence/editor-blocks.json'),JSON.stringify({checked_at:new Date().toISOString(),browser:await browser.version(),role:'editor',post:'WinPDFMerger',core_blocks:parsed.blocks.map(b=>({name:b.name,isValid:b.isValid})),all_valid:true,edited_fields:['tagline','benefit','requirement','download URL'],native_save_and_reload:true,signed_out_frontend_matches:true,theme_zip_reinstall_preserves_edits:true,theme_zip_sha256:digest,package_source:index.source,reviewed_copy_restored:true,limits:['Local disposable WordPress only; production editor not accessed']},null,2)+'\n');
  console.log('Real editor: valid blocks, 4 saved edits, reload, exact theme ZIP reinstall survival and copy restore passed.');
  await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
