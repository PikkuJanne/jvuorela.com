# Tool release and factual copy review — 2026-10-10

SPDX-License-Identifier: GPL-2.0-or-later

Five exact public stable `v1.0.0` releases passed the signed-out/public checks. WinBookSplit and AudioFileForPhone have no public release, so the all-seven launch gate remains blocked. No tool release, tag or source was changed by this verification.

The machine-readable record is `evidence/release-verification.json`. The first complete observation ended at 2026-10-10 17:48 UTC (19:48 Europe/Berlin). Observations are dated evidence; rerun `python tools/verify-public-releases.py` before launch, then reconcile the content release records intentionally. That command refreshes evidence only and does not overwrite editable copy.

For each published tool, unauthenticated GitHub REST release-by-tag and release-page requests returned HTTP 200. The exact tag was resolved to its commit rather than trusting `target_commitish`. Every advertised application ZIP downloaded directly without authentication; its byte length, computed SHA-256, GitHub asset digest and published checksum agreed. The ZIP bytes were hashed in memory without extraction. No tool-repository artwork was requested, rendered, extracted, retained or used.

All twelve recorded main/tagged documentation page URLs also returned HTTP 200 to unauthenticated HEAD requests. README source bytes were separately fetched from raw.githubusercontent.com and hashed. This includes every exact documentation URL seeded into the seven tool Pages.

This establishes public availability and package integrity. It does not establish tool execution, compatibility on this machine, audio listening, visual media fidelity, publisher authentication or a code signature. Website wording remains draft and editable pending owner review.

| Tool | Public v1.0.0 | Published UTC | Exact tagged commit |
|---|---|---|---|
| WinBookSplit | Missing; exact API/page 404, release listing empty | — | — |
| [WinPDFMerger](https://github.com/PikkuJanne/WinPDFMerger/releases/tag/v1.0.0) | Stable; draft=false, prerelease=false | 2026-10-10 07:12:01 | `95e0a19e6cc5fc01cd4bec4ac15f989f9830840a` |
| [WinVidCompress](https://github.com/PikkuJanne/WinVidCompress/releases/tag/v1.0.0) | Stable; draft=false, prerelease=false | 2026-10-08 15:15:15 | `06e803d8bfb56df0fd48cd5e073439f3ea93a4fd` |
| [UniversalPodcastDownloader](https://github.com/PikkuJanne/UniversalPodcastDownloader/releases/tag/v1.0.0) | Stable; draft=false, prerelease=false | 2026-10-08 04:40:50 | `6df1e357fe12890a5bed285ac5560a97c8905575` |
| [WinAudioClean](https://github.com/PikkuJanne/WinAudioClean/releases/tag/v1.0.0) | Stable; draft=false, prerelease=false | 2026-10-07 14:14:20 | `6dcf1bf56bc474b2c42353ed3779205468f1237c` |
| [WinImageNormalizer](https://github.com/PikkuJanne/WinImgNormalizer/releases/tag/v1.0.0) | Stable; draft=false, prerelease=false | 2026-10-07 05:56:49 | `8edbcbaeb3425ec3a52eeafde212c32553755af1` |
| AudioFileForPhone | Missing; exact API/page 404, release listing empty | — | — |

All seven repositories are public. GitHub metadata identifies MIT for six tools and Unlicense for WinVidCompress. The website's GPL-2.0-or-later terms do not replace those tool or dependency licenses.

| Application archive | Bytes | Computed SHA-256 |
|---|---:|---|
| `WinPDFMerger-v1.0.0.zip` | 193669 | `2b95e90cc3eb3d47b5619710acd1b6cf551769e90ac89813a1dbf0c899c63fc2` |
| `WinVidCompress-1.0.0.zip` | 250197 | `5c26d993e482204f80b81f10b8833315ac40c2c8d800b5bdaa7424567298b858` |
| `UniversalPodcastDownloader-1.0.0.zip` | 6212904 | `2781e69bc3c2b5da6579a6e04c3d13def36371bfb1582a7b154b3e57d067e7b9` |
| `WinAudioClean-1.0.0-6dcf1bf56bc4-tool-only.zip` | 443971 | `d418a721b6ce8494e3a0233f4b8502120f90d2c17ad7c3e34aa539899abe8a46` |
| `WinImgNormalizer-1.0.0-portable.zip` | 184659 | `251828028e144759c919645f423fde08641cabf42e04db7024d7ad17da4ba14d` |

Exact direct URLs, asset IDs, all asset names, sizes, observed download counts, API digests, checksum-sidecar contents, status flags and README hashes are in the JSON. Counts are observations, not promised totals.

## Editable copy corrections and review

`content/tools.json` retains its editable seed structure, display/runtime aliases, three benefits per tool and draft copy status. `content/releases.json` contains the fresh evidence and five passing public/integrity records; the other two are explicitly blocked by missing releases. Internal notes and evidence fields must remain excluded from visitor content.

- **WinBookSplit:** corrected stale Windows/Python requirements using the [current main README](https://github.com/PikkuJanne/WinBookSplit/blob/main/README.md). Its current source requires Windows 11 x64, Windows PowerShell 5.1 or tested PowerShell 7.6.5, x64 CPython 3.14.8, pypdf 6.19.0 and Calibre 9.15.0 for ebook conversion. Windows 10 is unverified. The source rejects encrypted PDFs, forms, signatures and detected active/embedded-document features; manual choices use physical PDF pages and the user confirms the chapter plan. The source still identifies itself as `1.0.0-dev`. These are provisional main facts, not facts about an absent final release. Download stays pending.
- **WinPDFMerger:** replaced the old missing-release note, bound the documentation link to the [tagged README](https://github.com/PikkuJanne/WinPDFMerger/blob/v1.0.0/README.md), and made extraction retain `VERSION` plus the helper module. The BAT accepts one folder and writes beside the scripts; named PS1 options permit a separate existing destination. Only visible top-level PDFs are scanned. PDFtk is required and Ghostscript optional. The tagged README and release notes preserve dated prepublication statements, even though the release is now public; the verified release metadata determines publication status. Those historical statements must not become a visitor-facing missing-download claim.
- **WinVidCompress:** verified the [tagged README](https://github.com/PikkuJanne/WinVidCompress/blob/v1.0.0/README.md). Windows 11, PowerShell 5.1/supported stable 7 and separately supplied FFmpeg/FFprobe remain accurate; PATH wins over adjacent executables. The MP4 profile, height-only 1080 cap, no upscale, lossy SDR, detected-HDR refusal, omitted extra tracks and possible larger output remain accurate. Its README retains source-snapshot download wording, so the website uses the verified packaged release route.
- **UniversalPodcastDownloader:** verified the [tagged README](https://github.com/PikkuJanne/UniversalPodcastDownloader/blob/v1.0.0/README.md). Keep the complete `src` folder; Windows 11, PowerShell 5.1/supported 7, storage and network access are required. No FFmpeg/Python is needed. Latest/count/all and supported audio facts match. “All” remains the bounded accessible catalogue, and publisher media rights remain separate from the software license.
- **WinAudioClean:** verified the [tagged README](https://github.com/PikkuJanne/WinAudioClean/blob/v1.0.0/README.md). Preserve all components and supply FFmpeg/FFprobe separately. Raw cleans and levels, Zoom/Teams levels; default output is 48 kHz PCM16 WAV. Requested −12 LUFS/−1.5 dBTP targets do not imply default final measurement. Accurate mode is optional, Gentle remains experimental, and listening is required. Public `1.0.0` remains separate from historical internal `2.3`.
- **WinImageNormalizer:** verified the [tagged README](https://github.com/PikkuJanne/WinImgNormalizer/blob/v1.0.0/README.md). Preserve the repository/launcher name `WinImgNormalizer`. The ImageMagick requirement, 1 MiB best-effort target, fresh Pictures output, JPEG colour/metadata handling, unchanged supported videos, single-frame/page selection and heuristic duplicate limitations match. Tagged/package documentation retains unreleased preparation wording; [current main](https://github.com/PikkuJanne/WinImgNormalizer/blob/main/README.md) and the release explicitly explain its publication without altering those approved bytes. Windows 10 is documented and untested.
- **AudioFileForPhone:** checked the [current main README](https://github.com/PikkuJanne/AudioFileForPhone/blob/main/README.md). The MP3/M4A/OGG scan, Vorbis/Opus within OGG, 64 kbps CBR default, mirrored Music output, shortened date-prefixed filenames and optional common tags/cover match the draft. `.opus`, WAV and FLAC are not scanned. The README supplies no exact minimum PowerShell/FFmpeg versions. A final `v1.0.0` is missing, so its facts remain provisional and download pending.

Owner copy approval and the final all-seven recheck remain required. Do not publish invented URLs for either missing tool, use a source archive as proof of a release, create their releases as a website shortcut, or mark the combined A25/A26 gate complete from the five successes.
