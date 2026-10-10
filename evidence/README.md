# Runtime evidence index

SPDX-License-Identifier: GPL-2.0-or-later

The tested local preview [v0.1.1](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.1) is public and ready for deployment at exact clean source `14cb3a924a1c1337ab7fcca9060bd4ed8003e801`. Native 404 Page selection and decoded no-WP-CLI templates are included. The owner has deferred hosting/live cutover. These are actual local/public-source results, with environments, limits and failed attempts preserved. A planning validator is distinct from runtime acceptance.

| Scope | Evidence |
|---|---|
| Workspace preservation/public remote | [foundation](foundation.md), [synchronized checkpoints](checkpoints.json) |
| Published preview identity, anonymous downloads and exact Windows/Linux reproduction | [candidate release verification](candidate-release.json), [candidate review](../docs/CANDIDATE_REVIEW.md) |
| Immutable initial v0.1.0 publication and exact-package results | [initial release](releases/v0.1.0.json), [initial Editor survival](releases/v0.1.0-editor-blocks.json), [initial rehearsal](releases/v0.1.0-deployment-rehearsal.json) |
| All 47 supplied sources and traced derivatives | [visual review](asset-review.md), [manifest](../assets/manifest.json) |
| Real WordPress importer/edit/conflict preservation | [37 assertions](importer-runtime-2026-10-10.md) |
| Native Editor save/reload/theme reinstall | [Editor result](editor-blocks.json), [screenshot](screenshots/editor-winpdfmerger.png) |
| All eleven native Pages, 228 valid core blocks, exact owner Impressum | [read-only editor UI validation](editor-pages.json); Editor on ordinary Pages, administrator on WordPress's restricted Privacy policy Page |
| Native 404 Reading selector, public/draft/password safeguards, dynamic Page context and fresh option/menu export | [20 focused development checks](theme-content-review-runtime.json), repeated on each exact-source CI WordPress version; prior baseline/failures retained |
| Actual native Reading save, unmarked 404 Page, signed-out HTTP 404/legal links and exact clean theme ZIP reinstall | [settings UI result](settings-404.json) |
| Eleven decoded templates from the exact clean setup ZIP; 228 valid core blocks, zero Page writes | [manual template acceptance](manual-templates.json), [portable template guide](../content/PAGE_TEMPLATES.md) |
| Fresh pinned setup/rerun and bootstrap correction | [setup result](local-setup-runtime.json) |
| Signed-out Home/seven tools/support/404, site-wide Impressum link | [50 Chrome cases](browser-runtime.json), [screenshots](screenshots/) |
| Earlier Edge/Firefox, full artwork boxes, responsive files, mobile lab observations; correction leaves presentation/artwork unchanged | [measurements](artwork-layout-measurements.md), [raw results](layout-performance.json) |
| Exact released ZIP installation/private backup/fault/restore, 74 checks | [rehearsal summary](deployment-rehearsal.md), [package hashes/checks](deployment-rehearsal.json) |
| Three passing public GitHub CI jobs; 37 importer and 20 native 404/export assertions on each of WordPress 6.9.10/7.1.3 | [CI result](github-ci.json), [actual candidate run](https://github.com/PikkuJanne/jvuorela.com/actions/runs/38077421861) |
| Public clean clone and cross-platform package comparison | [earlier checkpoint mismatch/correction](public-build-checkpoint.json), [final byte-identical comparison](candidate-release.json) |
| Five stable checksummed releases/two absent releases | [release facts](release-facts.md), [anonymous verification](release-verification.json) |
| Continuation check of the two missing exact v1.0.0 releases/tags; API-only scope | [next-step receipt](next-step-release-gate.json), [next-step handover](../docs/NEXT_STEP.md) |
| Seven tool audits, retained posters, scoped removals | [cleanup manifest](../repositories/tool-cleanup.json), [individual receipts](tool-cleanup/) |

All four anonymous v0.1.1 release downloads match the exact tested artifacts, two Windows clean builds and Linux CI. Use its release ZIPs or check out `v0.1.1`/the full source commit to reproduce them; later receipt/docs commits on `main` do not replace that identity. The initial v0.1.0 tag, ZIPs and archived verification remain fixed and do not contain the new selector/templates.

Public evidence excludes development credentials, database dumps, native IDs, original masters and private backup locations. The owner explicitly supplied the public Impressum contact details; that Page is intentional publication content. Final Privacy wording, two missing tool releases and actual hosted/live acceptance remain separate launch gates. Earlier layout/lab measurements predate the manual setup correction; Safari was unavailable, native headless zoom did not change the viewport, and mobile performance results are loopback lab observations. See [deployment/rollback instructions](../docs/DEPLOYMENT_HANDOFF.md).
