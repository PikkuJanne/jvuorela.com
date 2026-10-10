# Runtime evidence index

SPDX-License-Identifier: GPL-2.0-or-later

The tested initial local preview [v0.1.0](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0) is public at exact source `0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738`. A corrected v0.1.1 candidate is in progress for native 404 Page selection during manual UI setup; final local readiness awaits its tests/publication. The owner has deferred hosting/live cutover. These are actual local/public-source results, with environments, limits and failed attempts preserved. A planning validator is distinct from runtime acceptance.

| Scope | Evidence |
|---|---|
| Workspace preservation/public remote | [foundation](foundation.md), [synchronized checkpoints](checkpoints.json) |
| Published preview identity, anonymous downloads and exact Windows/Linux reproduction | [candidate release verification](candidate-release.json), [candidate review](../docs/CANDIDATE_REVIEW.md) |
| Immutable initial v0.1.0 publication and exact-package results | [initial release](releases/v0.1.0.json), [initial Editor survival](releases/v0.1.0-editor-blocks.json), [initial rehearsal](releases/v0.1.0-deployment-rehearsal.json) |
| All 47 supplied sources and traced derivatives | [visual review](asset-review.md), [manifest](../assets/manifest.json) |
| Real WordPress importer/edit/conflict preservation | [37 assertions](importer-runtime-2026-10-10.md) |
| Native Editor save/reload/theme reinstall | [Editor result](editor-blocks.json), [screenshot](screenshots/editor-winpdfmerger.png) |
| All eleven native Pages, 228 valid core blocks, exact owner Impressum | [read-only editor UI validation](editor-pages.json); Editor on ordinary Pages, administrator on WordPress's restricted Privacy policy Page |
| Native 404 Reading selector, public/draft/password safeguards, dynamic Page context and fresh option/menu export | [20 focused corrected-source checks](theme-content-review-runtime.json); prior seven-check baseline and assertion correction retained |
| Actual native Reading save, unmarked 404 Page, signed-out HTTP 404/legal links and theme ZIP reinstall | [settings UI result](settings-404.json); dirty development candidate explicitly provisional until clean acceptance |
| Fresh pinned setup/rerun and bootstrap correction | [setup result](local-setup-runtime.json) |
| Signed-out Home/seven tools/support/404, site-wide Impressum link | [50 Chrome cases](browser-runtime.json), [screenshots](screenshots/) |
| Edge/Firefox, full artwork boxes, responsive files, mobile lab observations | [measurements](artwork-layout-measurements.md), [raw results](layout-performance.json) |
| Exact released ZIP installation/private backup/fault/restore, 74 checks | [rehearsal summary](deployment-rehearsal.md), [package hashes/checks](deployment-rehearsal.json) |
| Three passing public GitHub CI jobs; 37 importer assertions on each of WordPress 6.9.10/7.1.3 | [CI result](github-ci.json), [actual candidate run](https://github.com/PikkuJanne/jvuorela.com/actions/runs/38076065026) |
| Public clean clone and cross-platform package comparison | [earlier checkpoint mismatch/correction](public-build-checkpoint.json), [final byte-identical comparison](candidate-release.json) |
| Five stable checksummed releases/two absent releases | [release facts](release-facts.md), [anonymous verification](release-verification.json) |
| Seven tool audits, retained posters, scoped removals | [cleanup manifest](../repositories/tool-cleanup.json), [individual receipts](tool-cleanup/) |

All four anonymous v0.1.0 release downloads match its exact tested artifacts, two Windows clean builds and Linux CI. The tag remains fixed; later corrections/receipt/docs commits on `main` do not replace its source or ZIPs. Use those release ZIPs or check out that tag/full commit to reproduce the initial preview. Replacement acceptance evidence will identify v0.1.1 explicitly.

Public evidence excludes development credentials, database dumps, native IDs, original masters and private backup locations. The owner explicitly supplied the public Impressum contact details; that Page is intentional publication content. Final Privacy wording, two missing tool releases and actual hosted/live acceptance remain separate launch gates. Safari was unavailable; native headless zoom did not change the viewport, and mobile performance results are loopback lab observations. See [deployment/rollback instructions](../docs/DEPLOYMENT_HANDOFF.md).
