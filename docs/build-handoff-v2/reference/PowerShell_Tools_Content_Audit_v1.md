> HISTORICAL SNAPSHOT — not active instructions. Current DECISIONS.md, ROADMAP.md and the v2 asset/GitHub/cleanup specifications supersede proposals here. Release observations are dated and require fresh verification.

# PowerShell tools website: content audit and page copy

Audited: 9 October 2026. Website: **jvuorela.com**, IONOS WordPress Start.

## Scope and launch decision

All seven tools must have a public, stable **v1.0.0** GitHub release before the website launches. The website presents and distributes tools that run on the visitor's Windows computer. It provides no file processing service. The agreed implementation is native WordPress pages and a lightweight custom theme, with no page builder or additional plugins required for these pages. Final artwork and character design remain separate work.

This audit reviewed live repository READMEs, repository metadata, public release records and asset listings through GitHub's public API. For four published tools, the v1.0.0 README was also read. It is a documentation/content audit: the scripts and release ZIPs were not executed, and published asset contents, checksums and physical download behavior were not independently tested here. Compatibility statements below describe documented requirements, not new test results.

Copy is written in English. All page version labels are planned as **v1.0.0**. Copy for tools without a release is provisional and must be reconciled with their final release; improvement plans alone are not evidence of implemented features.

## Inventory and page mapping

| Website title | Suggested page path | GitHub repository / runtime name | Public v1.0.0 today | Project license reported by GitHub |
| --- | --- | --- | --- | --- |
| WinBookSplit | `/tools/winbooksplit/` | WinBookSplit | Pending | MIT |
| WinPDFMerger | `/tools/winpdfmerger/` | WinPDFMerger / WinPDFMerge | Pending | MIT |
| WinVidCompress | `/tools/winvidcompress/` | WinVidCompress | Published | Unlicense |
| UniversalPodcastDownloader | `/tools/universalpodcastdownloader/` | UniversalPodcastDownloader | Published | MIT |
| WinAudioClean | `/tools/winaudioclean/` | WinAudioClean | Published | MIT |
| WinImageNormalizer | `/tools/winimagenormalizer/` | WinImgNormalizer | Published | MIT |
| AudioFileForPhone | `/tools/audiofileforphone/` | AudioFileForPhone | Pending | MIT |

Keep the owner's **WinImageNormalizer** presentation name. Installation instructions must use **WinImgNormalizer**. Similarly, WinPDFMerger instructions must retain **WinPDFMerge.ps1 / WinPDFMerge.bat**. Do not rename supplied launchers to match branding.

## Shared home-page copy

**Heading:** Seven tools. Your Windows workflow.

**Introduction:** Practical PowerShell tools by Janne Vuorela for books, PDFs, videos, podcasts, recordings and images. Download a tool, set up its dependencies, and run it on your own computer.

**Supporting line:** Focused tools. Local workflows. Source code on GitHub.

The one-line descriptions below can also serve as the home-page cards and proposed meta descriptions. Character art should dominate each page; the description, three benefits, requirements and two primary links provide the supporting content. Detailed technical instructions belong in the linked GitHub documentation.

## 1. WinBookSplit

**Tagline:** Turn a large book into focused chapters.

**Description / meta:** Split PDFs into organized chapter files using bookmarks or manual page selections. Convert EPUB and AZW3 books through Calibre before splitting.

**Three benefits:**

- Split by main chapters or subsections.
- Use manual selections when bookmarks are unavailable.
- Keep chapter files ordered with readable names.

**Requirements:** Windows 10/11; Windows PowerShell 5.1 or PowerShell 7+; Python 3 with pypdf. Calibre is needed for EPUB/AZW3 conversion, not ordinary PDF splitting.

**Quick start:** Extract the complete final release, install the dependencies, then drag a supported book onto `WinBookSplit.bat`. Choose bookmark level or manual mode.

**Output / limits:** The current README describes chapter PDFs and a log in a new Documents folder, preserving the source. Conversion can alter layout or bookmarks; damaged/encrypted files can prevent extraction. No OCR feature is documented.

**Status:** No public release found. Current-main copy is provisional; verify dependency versions, manual-selection syntax and output behavior against final v1.0.0.

**Source:** [Current README](https://github.com/PikkuJanne/WinBookSplit/blob/main/README.md).

## 2. WinPDFMerger

**Tagline:** Bring your PDFs together.

**Description / meta:** Merge a folder of PDFs into one master document, with an optional smaller email copy. Keep source documents and existing outputs intact.

**Three benefits:**

- Merge top-level PDFs in natural filename order.
- Create an optional email copy with Ghostscript.
- Keep a local log of the merge.

**Requirements:** Windows 11 x64; Windows PowerShell 5.1 for the BAT launcher. Direct PowerShell 7.6.6 x64 invocation is documented as tested. PDFtk Server is required; Ghostscript is optional. Windows 10, ARM and live UNC shares remain unvalidated.

**Quick start:** Extract the full package, including `src/WinPDFMerge.Helpers.ps1`. Drag exactly one source folder onto `WinPDFMerge.bat`; use PowerShell for named options.

**Output / limits:** Validated master PDF, optional `_email.pdf`, and log. Email output is published only when valid and smaller. Sources are not scanned recursively. Email rewriting can lose detail/features; signature validity and universal PDF feature retention are not guaranteed.

**Status:** No public release found. Verify final package and launcher acceptance before launch.

**Source:** [Current README](https://github.com/PikkuJanne/WinPDFMerger/blob/main/README.md).

## 3. WinVidCompress

**Tagline:** Make a viewing copy. Keep the original.

**Description / meta:** Create H.264/AAC MP4 viewing copies of videos with a focused PowerShell and FFmpeg workflow. Process individual files or folders locally.

**Three benefits:**

- Queue files and folders through drag and drop.
- Apply a consistent CRF 22 video profile.
- Preserve originals and avoid overwriting existing copies.

**Requirements:** Windows 11; Windows PowerShell 5.1 or supported stable PowerShell 7; separately supplied FFmpeg and FFprobe with required encoding capabilities. PATH takes precedence over adjacent executables.

**Quick start:** Extract the release, supply FFmpeg/FFprobe, then open `WinVidCompress.bat` or drop videos/folders onto it. Use `-CheckEnvironment` for setup diagnostics.

**Output / limits:** MP4 with libx264 veryfast, CRF 22, AAC 160 kbps and faststart. Oriented height is capped at 1080 without upscale; width is not capped at 1920. Output is lossy SDR; detected HDR is refused. Extra tracks/subtitles are omitted, and output can be larger than input.

**Status:** Public v1.0.0 exists; README still gives source-ZIP/preparation wording. Website should use the release route below.

**Source:** [v1.0.0 README](https://github.com/PikkuJanne/WinVidCompress/blob/v1.0.0/README.md).

## 4. UniversalPodcastDownloader

**Tagline:** Keep your listening library close.

**Description / meta:** Download podcast audio from RSS and Atom feeds to your Windows computer. Choose the latest episode, a selected count or the accessible feed catalogue.

**Three benefits:**

- Keep original audio without transcoding.
- Verify previously downloaded files before skipping them.
- Save shows for repeat or batch downloads.

**Requirements:** Windows 11; Windows PowerShell 5.1 or supported PowerShell 7; writable output storage and internet access to feed/media hosts. No FFmpeg or Python dependency.

**Quick start:** Extract the ZIP with the complete `src` directory. Open `UniversalPodcastDownloader.bat`, enter a feed or supported show-page URL, then choose latest, count or all.

**Output / limits:** Audio, download history and logs in organized show folders; default root is Downloads/Podcasts. Audio includes supported MP3, M4A, Ogg, WAV and FLAC. “All” means the bounded accessible catalogue, not every historical episode. Provider feeds and discovery conventions vary; browser-session imports are unsupported.

**Status:** Public v1.0.0 exists. Software licensing does not grant rights to podcast recordings.

**Source:** [v1.0.0 README](https://github.com/PikkuJanne/UniversalPodcastDownloader/blob/v1.0.0/README.md).

## 5. WinAudioClean

**Tagline:** Give spoken audio a cleaner finish.

**Description / meta:** Clean and level speech recordings locally with PowerShell and FFmpeg. Use Raw for cleaning and leveling, or Zoom/Teams for leveling only.

**Three benefits:**

- Choose processing suited to the recording.
- Compare short previews before full processing.
- Export WAV audio with local processing reports.

**Requirements:** Windows desktop; Windows PowerShell 5.1 or PowerShell 7; separately supplied FFmpeg and FFprobe. Windows 10/11 are intended targets, with recorded Windows 11 checks rather than universal compatibility certification. Keep all package components together.

**Quick start:** Extract the full tool-only ZIP, supply dependencies, and drag a simply named recording onto `WinAudioClean.bat`. Choose Raw or Zoom/Teams.

**Output / limits:** Default export is 48 kHz, 16-bit PCM WAV. Requested targets are −12 LUFS and −1.5 dBTP, not RMS. Accurate measurement is optional; default Fast mode does not measure final compliance. Listen to exports. Gentle is experimental and should not be promoted as an approved default.

**Status:** Public v1.0.0 exists; old internal “2.3” wording is not the public version label.

**Source:** [v1.0.0 README](https://github.com/PikkuJanne/WinAudioClean/blob/v1.0.0/README.md).

## 6. WinImageNormalizer

**Tagline:** Bring a mixed image folder into focus.

**Description / meta:** Create JPEG viewing copies from mixed image folders, preserve subfolders and copy supported videos unchanged. Keep your original files untouched.

**Three benefits:**

- Convert supported image formats to JPEG.
- Aim for a configurable file-size target.
- Preserve folder structure in a fresh output tree.

**Requirements:** 64-bit Windows 10/11; Windows PowerShell 5.1 or PowerShell 7; ImageMagick 7.1.2-32 or a newer supported 7.x build on PATH. Windows 10 is documented but untested; decoding depends on the installed build.

**Quick start:** Extract the package, install ImageMagick, then drag one folder onto `WinImgNormalizer.bat`. Output goes into a new Windows Pictures folder.

**Output / limits:** Default target is 1,048,576 bytes (1 MiB), best effort. Above-target results can carry warnings. Images become sRGB JPEGs on white, with metadata removed; videos retain original bytes/metadata. Animation and multipage images produce one selected frame/page. Duplicate skipping is heuristic, not content-hash verification.

**Status:** Public v1.0.0 exists; packaged/tagged documentation retains preparation-time “unreleased” wording, acknowledged by current README.

**Sources:** [Current README](https://github.com/PikkuJanne/WinImgNormalizer/blob/main/README.md), [tagged README](https://github.com/PikkuJanne/WinImgNormalizer/blob/v1.0.0/README.md).

## 7. AudioFileForPhone

**Tagline:** Pack more listening into your phone.

**Description / meta:** Convert podcast and audiobook folders into compact MP3 copies with shorter filenames for phone use. Preserve the original folder structure.

**Three benefits:**

- Convert MP3, M4A and OGG audio in batches.
- Use 64 kbps CBR by default, or choose another bitrate.
- Add optional cover art and shared artist/album tags.

**Requirements:** Windows 11; Windows PowerShell or PowerShell 7; FFmpeg and FFprobe beside the script or on PATH. Exact minimum dependency versions remain to be confirmed for v1.0.0.

**Quick start:** Extract the final release, supply dependencies, and drag a source folder onto `AudioFileForPhone.bat`.

**Output / limits:** New MP3 tree under Windows Music, mirrored subfolders, date-prefixed shortened filenames, collision suffixes and a run log. OGG supports Vorbis/Opus; `.opus`, WAV and FLAC are not scanned by the current documented workflow. MP3 conversion is lossy. Filename sanitizing does not establish compatibility with every phone/player. This is conversion, not the speech-cleaning workflow of WinAudioClean.

**Status:** No public release found. Current-main copy is provisional; recheck final formats, defaults and output behavior.

**Source:** [Current README](https://github.com/PikkuJanne/AudioFileForPhone/blob/main/README.md).

## Link register

Use **Download v1.0.0** as the primary button and **Source & instructions** as the secondary button. Recommended primary destination is the explicit version's release page, where visitors can also obtain checksum/provenance files. Do not use `/releases/latest` for a page explicitly promising v1.0.0. Direct ZIP links below are optional; GitHub-generated source archives are not substitutes for prepared runtime packages.

| Tool | Primary button destination | Source & instructions |
| --- | --- | --- |
| WinBookSplit | Pending: final public v1.0.0 URL must be recorded | [GitHub](https://github.com/PikkuJanne/WinBookSplit) |
| WinPDFMerger | Pending: final public v1.0.0 URL must be recorded | [GitHub](https://github.com/PikkuJanne/WinPDFMerger) |
| WinVidCompress | [v1.0.0 release](https://github.com/PikkuJanne/WinVidCompress/releases/tag/v1.0.0) | [GitHub](https://github.com/PikkuJanne/WinVidCompress) |
| UniversalPodcastDownloader | [v1.0.0 release](https://github.com/PikkuJanne/UniversalPodcastDownloader/releases/tag/v1.0.0) | [GitHub](https://github.com/PikkuJanne/UniversalPodcastDownloader) |
| WinAudioClean | [v1.0.0 release](https://github.com/PikkuJanne/WinAudioClean/releases/tag/v1.0.0) | [GitHub](https://github.com/PikkuJanne/WinAudioClean) |
| WinImageNormalizer | [v1.0.0 release](https://github.com/PikkuJanne/WinImgNormalizer/releases/tag/v1.0.0) | [GitHub](https://github.com/PikkuJanne/WinImgNormalizer) |
| AudioFileForPhone | Pending: final public v1.0.0 URL must be recorded | [GitHub](https://github.com/PikkuJanne/AudioFileForPhone) |

### Assets listed in the published release records

These exact names and URLs were returned by the release API. Availability in the record is verified; downloading and checksum verification are launch checks.

| Tool | Runtime ZIP | Companion files listed |
| --- | --- | --- |
| WinVidCompress | [WinVidCompress-1.0.0.zip](https://github.com/PikkuJanne/WinVidCompress/releases/download/v1.0.0/WinVidCompress-1.0.0.zip) | `WinVidCompress-1.0.0.zip.sha256` |
| UniversalPodcastDownloader | [UniversalPodcastDownloader-1.0.0.zip](https://github.com/PikkuJanne/UniversalPodcastDownloader/releases/download/v1.0.0/UniversalPodcastDownloader-1.0.0.zip) | `manifest.json`, `SHA256SUMS` |
| WinAudioClean | [WinAudioClean-1.0.0-6dcf1bf56bc4-tool-only.zip](https://github.com/PikkuJanne/WinAudioClean/releases/download/v1.0.0/WinAudioClean-1.0.0-6dcf1bf56bc4-tool-only.zip) | Matching `.sha256` and `.provenance.json` |
| WinImageNormalizer | [WinImgNormalizer-1.0.0-portable.zip](https://github.com/PikkuJanne/WinImgNormalizer/releases/download/v1.0.0/WinImgNormalizer-1.0.0-portable.zip) | `SHA256SUMS.txt`, `build-provenance.json` |

## Content decisions and corrections

| Issue | Website decision |
| --- | --- |
| Three releases are pending | Build all seven pages for v1.0.0, but publish only after all seven stable releases and packages exist. Do not invent future asset names. |
| Main branch may differ from released code | Reconcile final copy with the release tag, packaged guide and release notes. Link source documentation to the tag where possible. |
| WinVidCompress README still directs visitors to source ZIP | Website primary button points to the public v1.0.0 release. Record a repository documentation follow-up. |
| Published packages contain preparation wording | Follow public release records for availability; retain a documentation follow-up without silently changing released ZIP bytes. |
| Windows support differs by tool | Show per-tool requirements. Avoid a shared promise of fully tested Windows 10/11 compatibility. |
| Dependencies are separate | Put the dependency names beside the download area; do not imply a ZIP contains FFmpeg, ImageMagick, Python or Calibre. |
| License differences | Preserve MIT vs Unlicense; third-party software and downloaded media have their own terms. |
| “Local” does not mean every workflow is offline | Media conversion runs locally. Podcast downloads require network access; redirected/cloud-synced folders can move files through other software. |
| Strong marketing claims | Avoid guaranteed size reduction, lossless conversion, guaranteed loudness, universal format support, complete historical archives or universal phone compatibility. |

## Launch acceptance for website content

1. Confirm all seven releases are public, stable v1.0.0 releases, not drafts/prereleases.
2. For the three pending tools, repeat the content check against the final release and update requirements, quick start and outputs.
3. Record exact runtime ZIP and checksum URLs for all seven; do not guess names.
4. Download each advertised package, verify its documented checksum and inspect its runtime files and included instructions. Check links in a signed-out browser.
5. Confirm the website copy agrees with each released package, especially supported Windows/PowerShell versions, optional dependencies and format limits.
6. Confirm each page has its title, description, three benefits, requirements, download link and source/documentation link. Keep operational detail in GitHub documentation.
7. Keep visual creation as a separate phase after the page structure and content are ready.

**Audit outcome:** Seven content records and a shared home-page introduction are prepared. Content findings are resolved at the website-copy level; final release reconciliation and download acceptance remain launch gates. The next website task is the shared page structure with artwork placeholders.
