# Tested deployment candidate v0.1.1

SPDX-License-Identifier: GPL-2.0-or-later

Reviewed 2026-10-10. **Ready for deployment:** the tested local preview [v0.1.1](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.1) is public with exact source and reproducible packages. IONOS installation, hosting checks and live cutover are deferred to the owner's separate deployment session. This is a GitHub prerelease; final website v1.0.0 remains reserved for its accepted live source.

The corrected candidate adds native 404 Page selection and decoded `page-templates/*.html` for setup without WP-CLI. All twenty focused checks pass on each exact-source CI WordPress version, and native Reading save/signed-out 404/exact clean ZIP reinstall pass. Runbooks cover permalink/routing checks and resolving all template tokens in a text editor before native code-editor paste; use numeric attachment IDs and actual public native URLs, never draft-preview nonces.

The initial [v0.1.0 preview](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0), source `0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738` and downloads remain fixed. It does not include the native selector/decoded templates. Its [publication verification](../evidence/releases/v0.1.0.json), [Editor survival](../evidence/releases/v0.1.0-editor-blocks.json) and [rehearsal](../evidence/releases/v0.1.0-deployment-rehearsal.json) are archived independently.

## Exact candidate and installation entry

Public source/remote: `https://github.com/PikkuJanne/jvuorela.com.git`. The fixed `v0.1.1` tag resolves to clean source [`14cb3a924a1c1337ab7fcca9060bd4ed8003e801`](https://github.com/PikkuJanne/jvuorela.com/tree/14cb3a924a1c1337ab7fcca9060bd4ed8003e801). The release is public, non-draft and marked prerelease. [Anonymous verification](../evidence/candidate-release.json) confirms all four downloads and nine tagged source files without GitHub credentials.

Download both ZIPs, `SHA256SUMS` and `artifacts.json` from [the release](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.1); verify hashes before installation. The theme ZIP contains 23 entries under one `jvuorela-tools/` root. The separate setup ZIP contains 84 entries under one `jvuorela-tools-setup/` root: eleven portable draft Pages, eleven decoded HTML templates plus their README, approved derivatives and the content/setup/deployment runbooks. No credentials, databases, private masters, WordPress core or local SQLite adapter are shipped.

| Artifact | Bytes | SHA-256 |
|---|---:|---|
| `jvuorela-tools-0.1.1.zip` | 198,918 | `363d428fef32eddb0b1fb80ab27c703df0da27a3e07204f68b2987f8b1b42d8f` |
| `jvuorela-tools-content-media-0.1.1.zip` | 14,440,372 | `38e8bf69be4da498cf60667348eb43349fb0e7d7dc98ac932e9b7ece42573675` |
| `artifacts.json` | 1,465 | `c139c5f1635f3b6a53301542333af4127d76d81725d64f4464cc8a51a6cff36e` |
| `SHA256SUMS` | 277 | `faca172c8005e17278019075287369372b83c69a95f56c6066bdf8c8558265df` |

Use [DEPLOYMENT_HANDOFF.md](DEPLOYMENT_HANDOFF.md) for host inventory, protected preview, installation, private backups and rollback. Its tested release version is [available at the fixed v0.1.1 tag](https://github.com/PikkuJanne/jvuorela.com/blob/v0.1.1/docs/DEPLOYMENT_HANDOFF.md) and inside the setup ZIP. Use [CONTENT_IMPORT.md](CONTENT_IMPORT.md) and [the portable template guide](../content/PAGE_TEMPLATES.md) for reviewed native setup; ordinary theme updates install code and preserve editor content. Current receipt/docs on `main` supplement frozen instructions without replacing their release identity.

Before content setup, inventory existing permalink/rewrite rules and domain routing. On the protected preview, verify the supported native permalink structure and actual rewrite behavior/true HTTP 404, or deliberately preserve existing query-link behavior. Resolve/import internal links against the resulting native URLs and preserve existing hosted routes. The corrected candidate exposes **Settings → Reading → Page for missing addresses (404)** to select a published public Page without a Page password or importer metadata. Its optional setting preserves imported/default fallback and never changes Page status; see the current runbooks for the complete UI procedure.

Receipt/docs commits on `main` intentionally follow the release freeze. Rebuilding `main` changes source metadata and may change setup documentation; use the published ZIPs or the fixed tag/commit to reproduce the candidate:

```sh
git clone https://github.com/PikkuJanne/jvuorela.com.git
cd jvuorela.com
git checkout v0.1.1
python scripts/check-source.py
python scripts/package.py --release --expected-commit 14cb3a924a1c1337ab7fcca9060bd4ed8003e801 --output dist
```

Python 3.12 or newer and Git suffice. Builds use committed derivatives; private artwork masters are unnecessary. [BUILD.md](BUILD.md) explains deterministic timestamps, entry order, permissions, checksums and source identity.

## Observed acceptance

| Behavior | Actual evidence |
|---|---|
| Exact v0.1.1 ZIP installation, 11 draft Pages/32 media records, native configuration, signed-out routes, private backup, controlled fault and restore | [74 passing checks, zero PHP diagnostics](../evidence/deployment-rehearsal.json), [rehearsal summary](../evidence/deployment-rehearsal.md) |
| Native Editor saves four content fields, reloads, exact released theme ZIP reinstall preserves all four, reviewed copy restored | [Exact v0.1.1 Editor survival result](../evidence/editor-blocks.json) |
| All eleven packaged HTML templates match portable source and resolve to 228 valid registered core blocks; zero Page writes | [Exact clean setup-ZIP acceptance](../evidence/manual-templates.json) |
| Native managed Page UI review, including exact owner Impressum, Editor on ordinary Pages and administrator on restricted Privacy Page | [Read-only eleven-Page editor result](../evidence/editor-pages.json); content unchanged by the selector correction |
| Actual native Reading save selects an unmarked Page, signed-out missing route stays HTTP 404 with legal links, setting/copy survive exact clean ZIP reinstall | [Native 404 UI result](../evidence/settings-404.json) |
| Real importer planning, draft-only writes, idempotence, content/media/configuration conflicts and unrelated-data preservation | [37 assertions](../evidence/importer-runtime-2026-10-10.md), repeated on both exact-source [CI WordPress versions](../evidence/github-ci.json) |
| Public clean builds and downloads reproduce all four artifacts exactly | [Two Windows clean builds, Linux CI and anonymous downloads](../evidence/candidate-release.json) |
| Public source/package job and real WordPress 6.9.10/7.1.3 behavior jobs pass | [All three CI jobs](https://github.com/PikkuJanne/jvuorela.com/actions/runs/38077421861), actual PHP 8.3.6, SQLite engine 3.45.1/local adapter 3.1.0, 37 importer plus 20 native 404/export assertions per WordPress version |
| Earlier signed-out Chrome, no JavaScript, five widths, true HTTP 404, one H1, no overflow, responsive artwork, metadata and no third-party requests | [50 route/viewport cases](../evidence/browser-runtime.json); presentation/artwork unchanged by correction |
| Earlier Edge/Firefox layout, keyboard focus, equivalent 200% reflow, complete portrait/avatar boxes and mobile observations | [20 cases per browser and measurement limits](../evidence/artwork-layout-measurements.md), [raw results](../evidence/layout-performance.json) |
| Native 404 selector/publication safeguards, dynamic Page context and fresh configuration/menu export | [20 focused development-source checks](../evidence/theme-content-review-runtime.json), repeated on each clean-source CI WordPress version; prior seven-check baseline/failed assertions retained |
| Pinned Windows setup initializes a fresh isolated installation and reruns without altering database/users/credentials | [Setup result and recorded bootstrap correction](../evidence/local-setup-runtime.json) |

The local rehearsal/editor runtime used PHP 8.3.35, WordPress 7.1.3 and WP-CLI 2.12.0. The development SQLite adapter is local only. Ordinary Pages were reviewed as Editor; WordPress's configured Privacy policy Page requires the native privacy-management capability and was reviewed separately as administrator. Roles, capabilities and Page content were not changed by the read-only review.

The browser layout/lab measurements predate the manual setup correction; presentation/artwork are unchanged. Safari was unavailable on Windows. Headless native zoom did not change the viewport, so equivalent 200% reflow is the recorded result. Mobile measurements are throttled loopback lab observations, not field data or IONOS performance scores. Local testing does not establish the host's MySQL/MariaDB behavior, routing, editor permissions, backups or HTTPS/cache behavior.

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
