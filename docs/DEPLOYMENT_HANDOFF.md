# Deployment handoff and rollback

SPDX-License-Identifier: GPL-2.0-or-later

The owner’s 10 October 2026 follow-up moves IONOS work to a separate deployment session. This handoff prepares a tested local candidate and public GitHub packages. **Ready for deployment** describes the installable local handoff; it does not claim a live website or permission to change current hosting. Live launch still requires seven verified v1.0.0 tool releases, final owner legal/contact wording, actual hosting acceptance and the concrete cutover decision.

## Package and installation

Build/verification commands are in BUILD.md. The public preview release contains the single-root theme ZIP, separate portable content/media setup ZIP, checksums and artifact index. Verify SHA-256 before uploading. Never upload the source checkout, private folder, original masters, a local database, PHP runtime, SQLite adapter or WP-CLI PHAR as theme files.

In the deployment session first inventory the target installation and its WordPress/PHP versions, upload limits, theme permissions, managed components, domain routing and backup capabilities. On 10 October the existing apex request ended at https://anti-commercial.media/; preserve that existing routing until its relationship to the new site is deliberately resolved. Do not change email DNS or unrelated sites.

Use an actual separate protected private preview if the account supports it. Verify an unauthorized signed-out request is denied: noindex alone is not access protection. Test the exact candidate on the actual hosted database/PHP family. The local tests use an official SQLite development adapter; the theme includes no adapter/plugin.

Upload the candidate theme ZIP through Appearance → Themes → Add New → Upload Theme. Back up any existing same-slug theme before replacement. Activate on the isolated preview and verify front-end/admin access. Initialize content/media separately using CONTENT_IMPORT.md. Imports create drafts; plan before apply, and configure separately. If WP-CLI is unavailable, use the documented native Page/Media/editor route. Resolve stable tokens with uploaded Media Library IDs and actual internal Page links; never paste unresolved tokens.

Publish reviewed Pages only in the protected preview during testing. Set the Home Page in Settings → Reading; select the native privacy Page and site icon. Header/footer links are ordinary menus. Refresh a public-safe editor snapshot immediately before candidate freeze; do not overwrite hosted edits with this repository’s old draft seed. Theme updates deploy code only.

## Pre-cutover backup

Before production changes, capture database, uploads, active theme and relevant configuration using the account’s supported tools. Keep them encrypted/access-restricted outside the public web root and GitHub; record date, scope, checksums, location and restoration steps privately. Export a public-safe Page review snapshot separately. Confirm emergency WordPress/SFTP/account access and the prior working theme package.

Restore the backup into a disposable environment and check at least Home, a tool Page, one image, menus and admin access. Local rehearsal evidence shows the supported procedure on the disposable site; the hosted backup itself must be tested in the separate session. Obtain the final launch decision only after this concrete candidate/backup review.

## Cutover and acceptance

Name each proposed production action: code upload/activation, intentional content publication, domain/HTTPS routing, cache invalidation and removal of preview protection from production only. Preserve all other domains/mail/hosted content. Check apex HTTPS and www behavior, Home, all seven tools, Impressum/Privacy, random-route HTTP404, image/social/download links, no mixed content, keyboard/mobile layouts, hosted editor save/revision and signed-out network requests.

WinBookSplit and AudioFileForPhone currently lack public stable v1.0.0 releases. Their draft Pages contain “Release link pending” and no invented download. Do not launch until those releases/packages/checksums and their final README facts pass the verifier and are intentionally updated in the reviewed native Pages. Contact/legal Pages remain drafts requiring supplied final details and observed hosting privacy facts.

## Rollback

Trigger rollback for inaccessible pages/admin, missing media, data loss, serious layout failure or redirect loops. For a code-only failure restore the prior theme/package first, leaving newer editorial data intact. For an initialization/content fault restore only the backed-up content/uploads scope, accounting for writes since backup. Restore only domain routing changed in this deployment; retain mail/unrelated records. Restore preview protection if production should be withdrawn. Verify the previous site and record the incident privately.

Keep the last working theme package and private backup references. After actual live acceptance publish website v1.0.0 from the exact deployed clean commit, never move existing tags, and record version/source/package hashes. The current public candidate release is separate from that future final release.
