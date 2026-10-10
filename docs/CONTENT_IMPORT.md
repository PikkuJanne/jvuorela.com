# Content, media and owner editing

SPDX-License-Identifier: GPL-2.0-or-later

WordPress Pages and Media Library records become authoritative after editing starts. Theme installation, activation and updates never import or replace page wording. The scripts here are development tools, outside the theme ZIP, and require an explicit WP-CLI invocation. They do not publish Pages.

## Fresh private installation

Use the tested WordPress/PHP setup from the repository README. Back up an existing target's database, uploads and theme before an import. Keep backups, exported snapshots, credentials and environment-specific IDs in private storage. On IONOS, first verify whether WP-CLI is actually available; otherwise follow the editor procedure below.

Before either setup route, record the existing **Settings → Permalinks** structure, WordPress/site URLs, physical rewrite rules and domain/redirect routing. Preserve current hosted routes. On the protected preview, verify the host supports the intended pretty permalink structure and that real Page routes and a never-existing route work with the expected HTTP status. If the existing site deliberately uses plain permalinks, retain that behavior and resolve native query links. Do not assume local pretty links will work on IONOS or change production routing during content setup. Resolve/import internal Page links only after checking the actual native URL behavior.

Run these commands from the public source checkout, replacing the WordPress path and administrator with the actual private preview installation. WP-CLI may use its normal installed `wp` launcher or the pinned local PHP/PHAR command documented in the README.

```sh
wp --path=/path/to/private-wordpress --user=ADMIN eval-file scripts/import-content.php
wp --path=/path/to/private-wordpress --user=ADMIN eval-file scripts/import-content.php apply
wp --path=/path/to/private-wordpress --user=ADMIN eval-file scripts/import-content.php
```

The first command is read-only. Review its page/media actions and conflicts. `apply` is the explicit write action: it uploads only checksum-verified public derivatives from `assets/approved/`, creates missing native Pages as drafts, assigns native templates and social attachment metadata, and translates portable page/asset/attachment tokens to that installation's URLs and numeric IDs. It uses the largest mapped derivative; WordPress generates proportional responsive sizes without cropping the artwork. Source masters remain unchanged and private. The final command should report unchanged objects before any editorial changes.

The input defaults to `content/pages.json`. An alternate reviewed seed path can be supplied as a second positional argument after `plan` or `apply`. The source file contains core-block markup and stable page IDs, while `assets/manifest.json` supplies approved slot paths, source provenance and derivative hashes. These files are setup/review inputs; the theme does not read them to render Pages.

Tokens pointing to the configuration's intended front Page resolve to the installation root URL even before settings are applied, avoiding a stale Home slug/query link after setup. This URL translation does not configure or publish the Page.

Other native Page tokens use WordPress's sample Page permalink API, so drafts resolve to their intended slug routes when pretty permalinks are configured. Ordinary draft permalinks can instead contain `?page_id=ID`; those are not saved into the new site's card links. A site deliberately using plain permalinks keeps WordPress's native query-link behavior.

The ICO favicon is a separately validated theme runtime file, not a Media Library upload. The normal PNG site-icon slot is uploaded for native WordPress site-icon settings. The importer reports the favicon file/hash but never relaxes WordPress upload MIME security or accepts a Page/attachment token for that runtime-only slot.

## Conflicts preserve owner changes

`_jvt_content_id` and `_jvt_asset_slot` identify project-owned objects independently of numeric IDs. Initial imports save a full page/media baseline and a source-seed fingerprint. A rerun checks content, title, excerpt, slug, parent, status, template and social image. It also checks attachment bytes and edited Media Library text. Changing a source seed does not grant permission to replace an existing Page.

Unmarked slug collisions, duplicate markers, changed content/settings/media and missing baselines are reported as conflicts. Page plans list changed native fields and previous/proposed source fingerprints; the private export contains the full current/baseline wording for a detailed diff. An apply with a conflict makes no changes. There is no overwrite or adoption mode. Export fresh content, compare it with the proposed source and resolve the specific change in the normal editor. Do not delete markers or reset fingerprints to hide a conflict. Interrupted writes can leave clearly marked incomplete objects; inspect and recover those objects deliberately before retrying. The operation is not an atomic database transaction.

Draft-to-published status changes are intentional editorial changes and are therefore preserved/reported too. A post-launch code deployment should upload the theme only. An old local seed or a full development database must never replace current hosted Pages/uploads.

## Configure only when explicitly desired

Front-page, privacy-page, site-icon, site wording and primary/footer menus use a separate action:

```sh
wp --path=/path/to/private-wordpress --user=ADMIN eval-file scripts/import-content.php configure
wp --path=/path/to/private-wordpress --user=ADMIN eval-file scripts/import-content.php configure-apply
```

This uses `configuration` from the reviewed seed. It does not publish Pages. First setup accepts only unused core defaults or already matching values; an existing nondefault setting, menu name or occupied location is a conflict. Subsequent runs compare the saved `_jvt_configuration_state` with current options, menu entries and locations, preserving editor changes. Created menus have a project location marker. No unrelated options, menus or users are changed. Configure before editing imported drafts; existing page/media conflicts require review first.

New menu names default to `JVuorela primary` and `JVuorela footer`. An optional reviewed `menu_prefix` changes only the names of newly created menus; existing names/locations remain conflict-protected. Disposable tests use unique fixture menu names, preserving all actual menu objects.

## Export before candidate review

```sh
wp --path=/path/to/private-wordpress --user=ADMIN eval-file scripts/export-content.php > private/current-content.json
```

The read-only schema-2 export contains current native Page fields, local URLs/IDs, media hashes, current site title/tagline/Reading/privacy/icon options, menu assignments and actual menu entries. Saved import baselines are separate from these freshly read settings. It is a fresh review snapshot, not an automatic migration/import input. Review it for private material, approve the current wording and record its hash with the candidate. Store the full environment snapshot privately; publish only deliberately sanitized review evidence. It is not a database/uploads backup.

## Edit through WordPress

Open **Pages → All Pages** and edit Home or a Tool Page. The native title supplies the page H1. The block editor holds the purpose/tagline, benefits, requirements, quick-start text and button labels/URLs. Select each block and save the change, reload the editor, then preview the actual Page. Home cards have their own concise editable text; update the corresponding Home card when a tool purpose changes.

To replace an image, select its Image block and choose **Replace → Media Library**. Upload the approved project derivative, choose it, and review its alternative text and size. Decorative character artwork can have empty alternative text; an image carrying information needs meaningful text. Do not download artwork from a tool repository. Set a Page's social image with the theme's native social-image field; choose its local Media Library attachment. Native excerpts supply editable description metadata.

Use **Appearance → Menus** to edit the primary and footer entries and assign their locations. Use **Settings → Reading** for the static Home Page and the theme's editable 404 Page selection, and **Settings → Privacy** for the privacy Page. Set the native site icon through the WordPress site-identity/site-icon control exposed by the installed version. Legal/contact text must use the owner's confirmed details before publication.

The owner supplied the exact Impressum text in `content/impressum.html`. Keep its **Impressum** link clearly visible in the site-wide footer on Home, every tool/support Page and the 404 route. Privacy is a separate document/Page with its own link; do not merge it into Impressum.

For recovery, open the Page's **Revisions** in the editor, compare the relevant versions and restore the chosen revision. Review both editor and front-end output afterward. Preserve a database/uploads backup for changes beyond a Page revision, especially media replacements. Theme updates do not refresh tool facts or GitHub release links automatically; those are deliberate content edits.

## Native setup when WP-CLI is unavailable

This procedure applies to the corrected v0.1.1 theme with the native 404 selector; the fixed initial v0.1.0 preview does not contain that selector. Exact candidate/source/checksum verification is recorded in the public repository's [candidate review](https://github.com/PikkuJanne/jvuorela.com/blob/main/docs/CANDIDATE_REVIEW.md) and the release artifact index. Keep the actual preview access-protected throughout this procedure. Publishing for the protected preview is separate from public cutover authorization.

1. Install/activate the tested replacement theme ZIP. Check the site's existing permalink/routing inventory as above, then confirm intended native Page routes on the protected preview. Preserve existing production routes and settings until the deployment session deliberately approves changes.
2. Upload only the approved derivatives from the setup ZIP through **Media → Add New**. Use the appropriate largest mapped PNG for each character/avatar/social/site-icon slot; WordPress generates proportional sizes. Record each slot's native **File URL** and attachment ID in private working notes. The attachment ID appears in its native Media edit link (`post=NUMBER`). Do not upload source masters, fetch tool-repository artwork or relax upload security to import the ICO file. The favicon is already a theme runtime file.
3. Create all eleven Pages as drafts through **Pages → Add New**: Home, the seven tools, Impressum, Privacy and Page not found. Use the reviewed titles, slugs and excerpts from `content/pages.json`; record their intended native permalinks privately. Home card links should use the root URL that will become the static front Page. Select the Tool Page template for tool Pages and set social images through the theme field as appropriate.
4. Open the matching decoded `page-templates/PAGE.html` file from the extracted corrected setup ZIP in a local text editor; `page-templates/README.md` explains its portable tokens. These files are generated deterministically from the same authoritative eleven Page records. Replace every `{{attachment:SLOT}}` with that uploaded image's numeric attachment ID **without adding quotes**, every `{{asset:SLOT}}` with its native File URL and every `{{page:PAGE}}` with the intended public native Page URL. Use the actual intended permalink, not a draft preview URL/nonce. Escape HTML attribute URLs correctly (`&` as `&amp;`, a double quote as `&quot;`). Resolve **all tokens before pasting**: unresolved attachment tokens are not valid numeric block attributes. Paste the fully resolved `.html` markup through the native code editor, return to the visual editor and verify valid core blocks. Image **Replace → Media Library** then provides the normal editing/replacement route. Check no `{{...}}` token remains in blocks, button destinations or metadata; set alternative text according to image meaning and review each draft/preview.
5. After review, publish the Pages only within the protected preview. Use **Settings → Reading** to select the static Home Page, then select the editable **Page not found** Page in **Page for missing addresses (404)** and save settings. The 404 selector accepts only published public Pages without a WordPress Page password; protect access to the entire preview separately. No importer markers or custom-field edits are needed. If the selector is absent, verify that the corrected theme/version is installed and that the account can manage settings before proceeding.
6. Select the separate Privacy Page through **Settings → Privacy**, set the native site icon and site title/tagline, and create/assign primary/footer menus through **Appearance → Menus**. Keep Impressum visibly linked in the site-wide footer, with a separate Privacy link. WordPress's configured Privacy policy Page requires the native privacy-management capability; an ordinary Editor may be unable to edit it, so an administrator handles that Page without changing roles/capabilities.
7. Verify Home, all tools, support Pages, image/social/download links and an invented missing route using a signed-out request allowed by the preview's access protection. The invented route must return **HTTP 404**, show the selected editable copy, one H1 and the site-wide footer links. Recheck internal URLs against the actual permalink/rewrite behavior. Confirm the native editor can save/reload ordinary Page changes before the eventual cutover review.

Do not install an importer or presentation plugin as a workaround. The manually created Pages are authoritative native content; a later importer run may report unmarked slug collisions and must not adopt/overwrite them. Save a fresh private database/uploads/configuration backup and reviewed content snapshot after successful setup.

The decoded templates are convenient editor input, not a second copy source or automatic publisher. They require no WP-CLI, plugin or JSON-decoding step on the host. Keep resolved working copies and installation-local notes private and outside the host's public web root. Ordinary editorial changes remain in the native Pages; do not regenerate/reseed them on theme updates.

The optional 404 selection takes precedence over an imported published Page marked `not-found`. **Use default Page not found content** leaves no explicit selection: the theme uses that imported Page if available, otherwise its minimal fallback copy. If a previously selected Page is removed, becomes a draft or gains a Page password, it is not exposed through the 404 route; the same fallback applies. Selecting a Page never changes its publication status. Theme activation/reinstall does not seed or reset this setting.

## Runtime verification

`tests/importer.php` runs against a real disposable loopback WordPress site with an explicit administrator and pretty permalinks configured. It uses traced approved derivatives under unique fixture markers and cleans up only the fixture objects it allocated. It checks read-only planning, first draft/media import, numeric core-image IDs, intended draft slug/root links, proportional generated sizes, idempotence, edited wording/requirements/buttons and native fields, collisions, duplicate markers, changed seeds/media, validation failure and preservation of unrelated Pages/options/users. It also exercises the explicit native front-page/icon/menu setup, its idempotence and settings/menu edit protection. The suite temporarily isolates those targeted settings, uses unique fixture menus, then restores the complete previous configuration and locations without editing existing Pages or menu objects. It refuses a non-loopback target. Record actual runtime results; PHP lint alone is not behavior evidence.
