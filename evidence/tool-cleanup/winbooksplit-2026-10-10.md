# WinBookSplit tool-asset cleanup audit

Observed 2026-10-10T17:49:46.099734+00:00. Public repository: https://github.com/PikkuJanne/WinBookSplit.

Inspected clean isolated default branch `main` at `cc81212de7c4ba563fbbdad9747cdbd0b48fbf5d`; independent live default branch equals this commit. 283 tracked paths were searched. Existing user workspaces were not touched.

| Exact tracked path | Classification | Blob SHA-1 | Action |
| --- | --- | --- | --- |
| `WinBookSplit_icon.ico` | active runtime/application icon | `fc57139965435f3ace0dc76d450e403a70adb8b8` | keep |
| `WinBookSplit_icon.png` | active runtime/application icon | `910453b85c4a814168f9ce0ee8a28fa857983603` | keep |
| `WinBookSplit_poster.png` | owner_resolved_keep | `3461d3bb810fb4cc8a82f6ba1e7590244fb876fb` | keep |

No confirmed obsolete website-only paths were removed. No direct reference required an edit. No branch, commit or PR was created because tool source was unchanged. Hashes, introduction subjects, reasons and exact textual references are in the adjacent JSON report.

Files were classified through tracked names, source/document/packaging/test references and commit subjects. Artwork pixels were never inspected or imported. Existing runtime/application icons, useful product documentation and current shared payload assets were retained. No package, release attachment, tag or history changed.

## Release observation

The [`v1.0.0` release URL](https://github.com/PikkuJanne/WinBookSplit/releases/tag/v1.0.0) and GitHub release-by-tag API returned 404; no v1.0.0 tag was observed. Current source records still describe in-progress/development work. This is a real website-launch blocker, not a completed release verification.

## Owner resolution

On 2026-10-10 the owner explicitly instructed keeping `WinBookSplit_poster.png`. Its earlier uncertain provenance is recorded in the JSON reason; this concrete retention decision resolves the cleanup ambiguity. No deletion is planned or claimed. It remains forbidden as a website artwork source.

## Checks and limits

Clean checkout, independent live-main equality, classified-path existence and absence of source mutations passed. No behavior tests were warranted by this read-only audit. Do not claim a deletion, merge, total historical erasure or approved artwork source. JSON validation is performed separately.
