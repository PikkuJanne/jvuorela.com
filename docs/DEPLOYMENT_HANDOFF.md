# Deployment handoff and rollback

SPDX-License-Identifier: GPL-2.0-or-later

The owner’s 10 October 2026 follow-up moves IONOS work to a separate deployment session. This handoff prepares a local candidate and public GitHub packages. The initial v0.1.0 preview stays public and immutable; the corrected v0.1.1 theme supplies complete native UI setup of editable 404 copy. Exact source/package acceptance is recorded in the public repository's [candidate review](https://github.com/PikkuJanne/jvuorela.com/blob/main/docs/CANDIDATE_REVIEW.md) and release artifact index. **Ready for deployment** describes the tested installable local handoff; it does not claim a live website or permission to change current hosting. Live launch still requires seven verified v1.0.0 tool releases, final Privacy wording, actual hosting acceptance and the concrete cutover decision.

## Package and installation

Build/verification commands are in [BUILD.md](BUILD.md). Use the exact tested release ZIPs/source identified in [the public candidate review](https://github.com/PikkuJanne/jvuorela.com/blob/main/docs/CANDIDATE_REVIEW.md) and its `artifacts.json`, rather than assuming the newer `main` branch reproduces an older release. The initial [v0.1.0 preview](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0) stays fixed; use the verified corrected v0.1.1 candidate for the complete UI setup documented here. Current handoff/receipt docs supplement frozen package instructions without moving tags or replacing ZIPs. Each public preview release contains the single-root theme ZIP, separate portable content/media setup ZIP, checksums and artifact index. Verify SHA-256 before uploading. Never upload the source checkout, private folder, original masters, a local database, PHP runtime, SQLite adapter or WP-CLI PHAR as theme files.

In the deployment session first inventory the target installation and its WordPress/PHP versions, upload limits, theme permissions, managed components, domain routing and backup capabilities. On 10 October the existing apex request ended at https://anti-commercial.media/; preserve that existing routing until its relationship to the new site is deliberately resolved. Do not change email DNS or unrelated sites.

Record existing WordPress/site URLs, **Settings → Permalinks**, physical rewrite rules and Page/redirect routing before content setup. On the protected preview, confirm the host supports the selected pretty permalink structure and serves actual native Page routes plus a true HTTP 404 for an invented missing route. If the site deliberately uses plain permalinks, preserve its query-link behavior instead. Resolve/import internal links against those actual native URLs. Do not replace current production routes or assume the local rewrite configuration applies to IONOS.

Use an actual separate protected private preview if the account supports it. Verify an unauthorized signed-out request is denied: noindex alone is not access protection. Test the exact candidate on the actual hosted database/PHP family. The local tests use an official SQLite development adapter; the theme includes no adapter/plugin.

Upload the candidate theme ZIP through Appearance → Themes → Add New → Upload Theme. Back up any existing same-slug theme before replacement. Activate on the isolated preview and verify front-end/admin access. Initialize content/media separately using [CONTENT_IMPORT.md](CONTENT_IMPORT.md). Imports create drafts; plan before apply, and configure separately. If WP-CLI is unavailable, follow that document's complete native Page/Media/editor procedure: upload approved derivatives, create/review all eleven drafts, select native templates, resolve `page-templates/*.html` in a local text editor using actual numeric attachment IDs/File URLs and intended public native Page links, then paste fully resolved markup through the native code editor and configure settings/menus. The corrected setup ZIP includes these deterministic decoded templates and their README; it requires no importer plugin/server CLI. Do not paste unresolved tokens or draft-preview nonces, and do not add importer markers to manually created Pages.

Publish reviewed Pages only in the protected preview during testing. Set the static Home Page in **Settings → Reading**. In the corrected theme, select the editable **Page not found** Page in **Page for missing addresses (404)** and save; this works without WP-CLI/protected importer metadata. The selector accepts only published public Pages without a WordPress Page password; access-protect the whole preview separately. A valid selection takes precedence over imported 404 copy. An unset or no-longer-public selection falls back to an imported published `not-found` Page, then minimal theme copy; it never publishes a draft or resets editorial settings on activation/reinstall. Check an invented route returns HTTP 404 with the intended native copy, one H1 and the site-wide Impressum/Privacy links. Select the separate native Privacy Page through **Settings → Privacy** and set the native site icon. The configured Privacy policy Page uses WordPress's privacy-management capability; use an administrator if an ordinary Editor is restricted, without broadening roles/capabilities. Header/footer links are ordinary menus. Refresh a public-safe editor snapshot immediately before candidate freeze; do not overwrite hosted edits with this repository’s old draft seed. Theme updates deploy code only.

## Pre-cutover backup

Before production changes, capture database, uploads, active theme and relevant configuration using the account’s supported tools. Keep them encrypted/access-restricted outside the public web root and GitHub; record date, scope, checksums, location and restoration steps privately. Export a public-safe Page review snapshot separately. Confirm emergency WordPress/SFTP/account access and the prior working theme package.

Restore the backup into a disposable environment and check at least Home, a tool Page, one image, menus and admin access. Local rehearsal evidence shows the supported procedure on the disposable site; the hosted backup itself must be tested in the separate session. Obtain the final launch decision only after this concrete candidate/backup review.

## Exact-package local rehearsal

After `scripts/setup-local.ps1` has cached the pinned official development runtime, run the independent rehearsal against the desired package directory:

```powershell
./scripts/rehearse-deployment.ps1 -ArtifactDirectory build/dev -Port 8089
```

Use the final clean package directory instead of `build/dev` for release acceptance. The script allocates a fresh ignored installation and refuses an occupied port. It never targets the primary local preview. It verifies the archive checksums, installs the exact theme ZIP, extracts the portable setup ZIP, imports drafts, applies native configuration and publishes only on its loopback fixture. Signed-out checks cover Home, seven tool Pages, Impressum, Privacy, a missing route and all 32 native Media Library originals.

The owned server is stopped before copying database, uploads, theme and configuration to a private backup outside its web root. The rehearsal deliberately changes only its new fixture, restores those scopes and checks tree hashes, native Page fields, rendered routes and media bytes again. Private credentials, database copies and diagnostic logs stay ignored. `evidence/deployment-rehearsal.json` and `.md` contain sanitized results and exact package/source hashes. A passing dirty development package is provisional; rehearse the clean final artifacts too. This is a local procedure rehearsal, with IONOS/MySQL acceptance and the hosted backup restore reserved for the later deployment session.

## Cutover and acceptance

Name each proposed production action: code upload/activation, intentional content publication, domain/HTTPS routing, cache invalidation and removal of preview protection from production only. Preserve all other domains/mail/hosted content. Check apex HTTPS and www behavior, Home, all seven tools, Impressum/Privacy, random-route HTTP404, image/social/download links, no mixed content, keyboard/mobile layouts, hosted editor save/revision and signed-out network requests.

Include both native setup routes in preview acceptance as applicable: verify the manually selected published 404 Page works without importer markers, and confirm native permalink/rewrite or deliberately retained query-link behavior before reviewing card/menu links. Confirm the settings/Pages survive the exact theme ZIP reinstall and preserve current hosted editor changes.

WinBookSplit and AudioFileForPhone currently lack public stable v1.0.0 releases. Their draft Pages contain “Release link pending” and no invented download. Do not launch until those releases/packages/checksums and their final README facts pass the verifier and are intentionally updated in the reviewed native Pages. The exact owner-supplied Impressum is included in editable blocks; its link is in the site-wide footer. Privacy remains separate and requires final text reflecting observed hosting facts before live publication.

## Rollback

Trigger rollback for inaccessible pages/admin, missing media, data loss, serious layout failure or redirect loops. For a code-only failure restore the prior theme/package first, leaving newer editorial data intact. For an initialization/content fault restore only the backed-up content/uploads scope, accounting for writes since backup. Restore only domain routing changed in this deployment; retain mail/unrelated records. Restore preview protection if production should be withdrawn. Verify the previous site and record the incident privately.

Keep the last working theme package and private backup references. After actual live acceptance publish website v1.0.0 from the exact deployed clean commit, never move existing tags, and record version/source/package hashes. The current public candidate release is separate from that future final release.
