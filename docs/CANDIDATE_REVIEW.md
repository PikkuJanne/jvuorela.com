# Tested initial preview and candidate correction

SPDX-License-Identifier: GPL-2.0-or-later

Reviewed 2026-10-10. The tested initial preview is publicly available at [v0.1.0](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0). A corrected **v0.1.1 candidate is in progress**: review identified that manual UI setup needed a native published-Page selector for editable 404 copy. Final local readiness awaits testing/publication of that replacement. IONOS installation, hosting checks and live cutover are deferred to the owner's separate deployment session. These are GitHub previews; final website v1.0.0 remains reserved for its accepted live source.

The v0.1.0 tag and downloads stay immutable. The identity/hashes and acceptance results below describe that initial preview. Replacement source, hashes and tests will be recorded only after actual verification. The implemented correction has [20 passing focused real WordPress checks](../evidence/theme-content-review-runtime.json) on the reviewed development source. [Actual native Reading form save, signed-out unmarked 404 Page/legal links and development ZIP reinstall](../evidence/settings-404.json) also pass; the dirty development ZIP is provisional and clean-package/release acceptance remains pending. Current handoff runbooks supplement the frozen package instructions with native 404 selection, decoded `page-templates/*.html` editor input and explicit permalink/routing checks. Resolve all portable tokens in a text editor before native code-editor paste; use numeric attachment IDs and actual public native URLs, never draft-preview nonces.

## Exact candidate and installation entry

Public source/remote: `https://github.com/PikkuJanne/jvuorela.com.git`. The fixed `v0.1.0` tag resolves to clean source [`0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738`](https://github.com/PikkuJanne/jvuorela.com/tree/0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738). The release is public, non-draft and marked prerelease. [Archived anonymous verification](../evidence/releases/v0.1.0.json) confirms its downloads and tagged source files without GitHub credentials.

Download both ZIPs, `SHA256SUMS` and `artifacts.json` from [the release](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0); verify hashes before installation. The theme ZIP contains one `jvuorela-tools/` root. The separate setup ZIP contains one `jvuorela-tools-setup/` root with eleven portable draft Pages, approved derivatives and the content/setup/deployment runbooks. No credentials, databases, private masters, WordPress core or local SQLite adapter are shipped.

| Artifact | Bytes | SHA-256 |
|---|---:|---|
| `jvuorela-tools-0.1.0.zip` | 197,065 | `742a83724afd0090855e18fbeb65c71b2627152c0c586b5b4ab88be2553d0f6d` |
| `jvuorela-tools-content-media-0.1.0.zip` | 14,388,483 | `a941072ec620a53459c9f0801e0d67474cfc1cb836881f645c97948013a91375` |
| `artifacts.json` | 1,465 | `e4a3c01a9317215dd1ac3698da6d7693e2ca631bbfb93ea25544d207d4e2dbc5` |
| `SHA256SUMS` | 277 | `6de5294f143e5b373a3c0f68763bc9b04eeeae9eecf46d8ebb22d532afb1d196` |

Use [DEPLOYMENT_HANDOFF.md](DEPLOYMENT_HANDOFF.md) for host inventory, protected preview, installation, private backups and rollback. Its tested release version is [available at the fixed tag](https://github.com/PikkuJanne/jvuorela.com/blob/v0.1.0/docs/DEPLOYMENT_HANDOFF.md) and inside the setup ZIP. Use [CONTENT_IMPORT.md](CONTENT_IMPORT.md) for planning and reviewed draft creation; ordinary theme updates install code and preserve native editor content.

Before content setup, inventory existing permalink/rewrite rules and domain routing. On the protected preview, verify the supported native permalink structure and actual rewrite behavior/true HTTP 404, or deliberately preserve existing query-link behavior. Resolve/import internal links against the resulting native URLs and preserve existing hosted routes. The corrected candidate exposes **Settings → Reading → Page for missing addresses (404)** to select a published public Page without a Page password or importer metadata. Its optional setting preserves imported/default fallback and never changes Page status; see the current runbooks for the complete UI procedure.

Receipt/docs commits on `main` intentionally follow the release freeze. Rebuilding `main` changes source metadata and may change setup documentation; use the published ZIPs or the fixed tag/commit to reproduce the candidate:

```sh
git clone https://github.com/PikkuJanne/jvuorela.com.git
cd jvuorela.com
git checkout v0.1.0
python scripts/check-source.py
python scripts/package.py --release --expected-commit 0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738 --output dist
```

Python 3.12 or newer and Git suffice. Builds use committed derivatives; private artwork masters are unnecessary. [BUILD.md](BUILD.md) explains deterministic timestamps, entry order, permissions, checksums and source identity.

## Observed acceptance

| Behavior | Actual evidence |
|---|---|
| Exact ZIP installation, 11 draft Pages/32 media records, native configuration, signed-out routes, private backup, controlled fault and restore | [74 passing initial-release checks](../evidence/releases/v0.1.0-deployment-rehearsal.json) |
| Native Editor saves four content fields, reloads, exact released theme ZIP reinstall preserves all four, reviewed copy restored | [Initial-release Editor survival result](../evidence/releases/v0.1.0-editor-blocks.json) |
| All 11 Pages contain 228 valid core blocks; no saves during full editor review | [Read-only editor UI result](../evidence/editor-pages.json) |
| Real importer planning, draft-only writes, idempotence, content/media/configuration conflicts and unrelated-data preservation | [37 assertions](../evidence/importer-runtime-2026-10-10.md) and the [initial release CI observation](../evidence/releases/v0.1.0.json) |
| Public clean builds and downloads reproduce all four artifacts exactly | [Two Windows clean builds, Linux CI and anonymous downloads](../evidence/releases/v0.1.0.json) |
| Public source/package job and real WordPress 6.9.10/7.1.3 behavior jobs pass | [All three CI jobs](https://github.com/PikkuJanne/jvuorela.com/actions/runs/38076065026), actual PHP 8.3.6, SQLite engine 3.45.1/local adapter 3.1.0, 37 importer assertions per WordPress version |
| Signed-out Chrome, no JavaScript, five widths, true HTTP 404, one H1, no overflow, responsive artwork, metadata and no third-party requests | [50 route/viewport cases](../evidence/browser-runtime.json) |
| Edge/Firefox layout, keyboard focus, equivalent 200% reflow, complete portrait/avatar boxes and mobile observations | [20 cases per browser and measurement limits](../evidence/artwork-layout-measurements.md), [raw results](../evidence/layout-performance.json) |
| Initial dynamic 404 blocks receive native Page context; fresh exports preserve observed options/menus | [Prior seven-check baseline](../evidence/theme-content-review-runtime.json); the same file records the later 20-check native-selector correction separately |
| Pinned Windows setup initializes a fresh isolated installation and reruns without altering database/users/credentials | [Setup result and recorded bootstrap correction](../evidence/local-setup-runtime.json) |

The local rehearsal/editor runtime used PHP 8.3.35, WordPress 7.1.3 and WP-CLI 2.12.0. The development SQLite adapter is local only. Ordinary Pages were reviewed as Editor; WordPress's configured Privacy policy Page requires the native privacy-management capability and was reviewed separately as administrator. Roles, capabilities and Page content were not changed by the read-only review.

Safari was unavailable on Windows. Headless native zoom did not change the viewport, so equivalent 200% reflow is the recorded result. Mobile performance measurements are throttled loopback lab observations, not field data or IONOS performance scores. Local testing does not establish the host's MySQL/MariaDB behavior, routing, editor permissions, backups or HTTPS/cache behavior.

## Content, artwork and remaining launch gates

The exact owner-supplied Impressum is six editable native core blocks with the native Page title and site-wide footer link. Privacy remains a separate draft Page. All wording stays in native WordPress content/settings; existing hosted Pages, menus and uploads remain authoritative. The importer plans first, refuses conflicts and never publishes automatically; theme activation/updates do not reseed content.

All 47 supplied visual sources were inspected. The 33 selected slots produce 47 traced proportional derivatives, preserving originals and using no artwork from tool repositories. New website code/docs use GPL-2.0-or-later; [artwork rights](../ARTWORK_LICENSE.md), [license scope](../LICENSE_SCOPE.md), [third-party notices](../THIRD_PARTY_NOTICES.md) and per-slot provenance retain their separate terms.

Five stable public tool v1.0.0 ZIPs/checksum sidecars are verified. The future public website launch still requires:

1. Public stable v1.0.0 releases, exact downloads and checksum verification for WinBookSplit and AudioFileForPhone; recheck all seven links at launch.
2. Final owner Privacy wording informed by actual hosting facts, plus final factual copy acceptance.
3. Hosting inventory, protected preview, host-specific backup/restore evidence and signed-out/editor acceptance.
4. The separately scheduled IONOS cutover, any still-missing cutover authorization and live acceptance before the final website v1.0.0 release.

The existing apex was observed redirecting to `https://anti-commercial.media/`; its routing remains unchanged pending the deployment session. The [deployment handoff](DEPLOYMENT_HANDOFF.md) identifies domain/HTTPS/cache checks and rollback. Keep backups and exports private and account for editor changes made after any snapshot.

All seven tool repositories have audit receipts. The owner retains five posters. Confirmed obsolete website-only assets/references were removed through narrow merged WinAudioClean/WinImgNormalizer PRs; protected code/icons and published releases/tags/history are preserved. Candidate and fetched default-tree checks pass, as do all four WinAudioClean and both WinImgNormalizer default-branch CI jobs. T27 and [the cleanup receipts](../evidence/tool-cleanup/) record exact allowlists and verification.

See [STATUS.md](../STATUS.md), [TASKS.json](../TASKS.json) and [the evidence index](../evidence/README.md) for the current handoff record. The fixed release remains reproducible even as later documentation and receipts advance on `main`.
