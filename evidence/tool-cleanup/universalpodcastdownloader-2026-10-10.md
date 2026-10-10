# UniversalPodcastDownloader tool-asset cleanup audit

Observed 2026-10-10T17:49:56.530609+00:00. Public repository: https://github.com/PikkuJanne/UniversalPodcastDownloader.

Inspected clean isolated default branch `main` at `6df1e357fe12890a5bed285ac5560a97c8905575`; independent live default branch equals this commit. 268 tracked paths were searched. Existing user workspaces were not touched.

| Exact tracked path | Classification | Blob SHA-1 | Action |
| --- | --- | --- | --- |
| `UniversalPodcastDownloader.ico` | active runtime/application icon | `8e4ad59ccda46392e8f8179bf6e40f6525a45fb1` | keep |
| `UniversalPodcastDownloader_icon.png` | active documentation/shared | `7260ddd08203ae20d06c3e3868f5d3b7c06099d3` | keep |
| `UniversalPodcastDownloader_poster.png` | active documentation/shared | `ef4201b9334964d2f8da653727eca3e16caa1dfd` | keep |
| `docs/website/PRODUCT_PAGE.md` | active documentation/shared | `e2d2787257e5a87bfea1ec7d3965c78dbd829fc6` | keep |
| `docs/website/README.md` | active documentation/shared | `ceeaa644a6d3a70064e18c4ad91cbcf8c40471f3` | keep |
| `docs/website/SCREENSHOT_CAPTURE.md` | active documentation/shared | `2b6159d647b26a7c78b80a692ef00ebb7512b7e7` | keep |
| `docs/website/version.example.json` | active documentation/shared | `7261919a22e4cec1cac93e31674b3200553b6c88` | keep |
| `docs/codex/RELEASE_AND_WEBSITE.md` | active documentation/shared | `86163951304536af706e920775f6ac385b539316` | keep |

No confirmed obsolete website-only paths were removed. No direct reference required an edit. No branch, commit or PR was created because tool source was unchanged. Hashes, introduction subjects, reasons and exact textual references are in the adjacent JSON report.

Files were classified through tracked names, source/document/packaging/test references and commit subjects. Artwork pixels were never inspected or imported. Existing runtime/application icons, useful product documentation and current shared payload assets were retained. No package, release attachment, tag or history changed.

## Release observation

Public stable [`v1.0.0`](https://github.com/PikkuJanne/UniversalPodcastDownloader/releases/tag/v1.0.0) verified through current GitHub API metadata: draft=false, prerelease=false, release ID 406417139, published 2026-10-08T04:40:50Z, tag commit `6df1e357fe12890a5bed285ac5560a97c8905575`. The unauthenticated release-page HEAD returned 200. API-reported asset facts are recorded without downloading/executing packages.

## Checks and limits

Clean checkout, independent live-main equality, classified-path existence and absence of source mutations passed. No behavior tests were warranted by this read-only audit. Do not claim a deletion, merge, total historical erasure or approved artwork source. JSON validation is performed separately.
