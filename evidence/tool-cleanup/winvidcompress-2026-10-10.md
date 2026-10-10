# WinVidCompress tool-asset cleanup audit

Observed 2026-10-10T17:49:52.994793+00:00. Public repository: https://github.com/PikkuJanne/WinVidCompress.

Inspected clean isolated default branch `main` at `8c0d5ca320726e310c8bd9977500144c61b28eee`; independent live default branch equals this commit. 307 tracked paths were searched. Existing user workspaces were not touched.

| Exact tracked path | Classification | Blob SHA-1 | Action |
| --- | --- | --- | --- |
| `WinVidCompress_variant.ico` | active runtime/application icon | `3acc46fc70921f802f0c03e6e355252665c12089` | keep |
| `WinVidCompress_icon_variant.png` | active runtime/application icon | `b46188de4a51e139cf1546d812b5048d17b3dcd4` | keep |
| `WinVidCompress_poster.png` | owner_resolved_keep | `16634f49359d64f040b54118e682008018340382` | keep |
| `website-content/screenshots/menu.png` | active documentation/shared | `52aafbf6acd951a70e7aa2bec8b5bec2c13850f4` | keep |
| `website-content/README.md` | active documentation/shared | `add2436a4d99fd8b2f88b85760dc591f88b73443` | keep |
| `website-content/DEMONSTRATIONS.md` | active documentation/shared | `dabf152c82460bf07b832f1c728793ca057f1ceb` | keep |
| `website-content/product.json` | active documentation/shared | `aa32e7e18f15fef83e3b370eefacc87837720e78` | keep |
| `website-content/product.schema.json` | active documentation/shared | `3478f875d5b56db2e77a2a81d687efd6e94a8839` | keep |
| `website-content/examples/synthetic-default.json` | active documentation/shared | `244990388a74b0bc58bd45682c0d754f5acebdf5` | keep |
| `docs/releases/WEBSITE_CONTENT.md` | active documentation/shared | `554d9bbed8184334fd5105ea83ea69c89c7b9ba7` | keep |
| `docs/codex-winvidcompress/WEBSITE_HANDOFF.md` | active documentation/shared | `00dab8257f02c147759dcfbe0688da5d256cc0c8` | keep |
| `docs/codex-winvidcompress/templates/website-product-draft.json` | active documentation/shared | `41088df2a4b290b362c51f874955ef19cb4b867b` | keep |

No confirmed obsolete website-only paths were removed. No direct reference required an edit. No branch, commit or PR was created because tool source was unchanged. Hashes, introduction subjects, reasons and exact textual references are in the adjacent JSON report.

Files were classified through tracked names, source/document/packaging/test references and commit subjects. Artwork pixels were never inspected or imported. Existing runtime/application icons, useful product documentation and current shared payload assets were retained. No package, release attachment, tag or history changed.

## Release observation

Public stable [`v1.0.0`](https://github.com/PikkuJanne/WinVidCompress/releases/tag/v1.0.0) verified through current GitHub API metadata: draft=false, prerelease=false, release ID 406971113, published 2026-10-08T15:15:15Z, tag commit `06e803d8bfb56df0fd48cd5e073439f3ea93a4fd`. The unauthenticated release-page HEAD returned 200. API-reported asset facts are recorded without downloading/executing packages.

## Owner resolution

On 2026-10-10 the owner explicitly instructed keeping `WinVidCompress_poster.png`. Its earlier uncertain provenance is recorded in the JSON reason; this concrete retention decision resolves the cleanup ambiguity. No deletion is planned or claimed. It remains forbidden as a website artwork source.

## Checks and limits

Clean checkout, independent live-main equality, classified-path existence and absence of source mutations passed. No behavior tests were warranted by this read-only audit. Do not claim a deletion, merge, total historical erasure or approved artwork source. JSON validation is performed separately.
