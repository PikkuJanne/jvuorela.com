# Visual asset contract — final supplied set

## Authoritative sources

Use only the owner's **Website Assets** and **PS Visuals** folders and the exact files in assets/manifest.json. The owner created/selected these for this website project. The art is complete; the files are supplied separately from this instructions ZIP. They have not been technically inspected by the bundle producer.

**Never use preliminary website assets from any of the seven tool repositories**, including their history, branches, release attachments, cached copies, README images or social banners. No downloading, copying, hotlinking, reference use or substitute generation. A matching filename or even identical bytes does not establish an allowed source. GitHub remains the factual source for release/code documentation, not this website's artwork. Once imported from the approved local sources, hashed derivatives in the dedicated website repository are allowed and must support a clean public clone.

## Exact website folder mapping

The following filenames are relative to Website Assets. `_character` means the full character; `_portrait` means the avatar. Verify roles visually rather than inferring them from generic naming conventions.

| Tool | Character | Avatar | Social |
| --- | --- | --- | --- |
| WinBookSplit | `WinBookSplit_character.png` | `WinBookSplit_portrait.png` | `WinBookSplit_SM.png` |
| WinPDFMerger | `WinPDFMerger_character.png` | `WinPDFMerger_portrait.png` | `WinPDFMerger_ SM.png` |
| WinVidCompress | `WinVidCompress_character.png` | `WinVidCompress_portrait.png` | `WinVidCompress_SM.png` |
| UniversalPodcastDownloader | `UniversalPodcastDownloader_character.png` | `UniversalPodcastDownloader_portrait.png` | `UniversalPodcastDownloader_SM.png` |
| WinAudioClean | `WinAudioClean_character.png` | `WinAudioClean_portrait.png` | `WinAudioClean_SM.png` |
| WinImageNormalizer | `WinImageNormalizer_character.png` | `WinImageNormalizer_portrait.png` | `WinImageNormalizer_SM.png` |
| AudioFileForPhone | `AudioFileForPhone_character.png` | `AudioFileForPhone_portrait.png` | `AudioFileForPhone_SM.png` |

Shared sources: `Website_SM.png` → home social preview; `Website_icon.png` → WordPress site icon; `Website_icon.ico` → browser favicon; `Website_background.png` → desktop background; `Mobile_background.png` → mobile background. Total: **26 files** in Website Assets.

The WinPDFMerger social source contains a literal extra space: `WinPDFMerger_ SM.png`. Also accept `WinPDFMerger_SM.png` if already corrected. If both exist, hash both; identical copies may be recorded as duplicates, differing files require explicit resolution. Do not rename/delete masters to resolve import.

## Exact PS Visuals mapping

These are the approved project icons/posters delivered locally, regardless of whether some other image exists in a tool repository. Total: **21 visual files**.

| Tool | PNG icon | ICO icon | Poster |
| --- | --- | --- | --- |
| WinBookSplit | `WinBookSplit.png` | `WinBookSplit.ico` | `WinBookSplit_poster.png` |
| WinPDFMerger | `WinPDFMerger.png` | `WinPDFMerger.ico` | `WinPDFMerger_poster.png` |
| WinVidCompress | `WinVidCompress.png` | `WinVidCompress.ico` | `WinVidCompress_poster.png` |
| UniversalPodcastDownloader | `UniversalPodcastDownloader.png` | `UniversalPodcastDownloader.ico` | `UniversalPodcastDownloader_poster.png` |
| WinAudioClean | `WinAudioClean.png` | `WinAudioClean.ico` | `WinAudioClean_poster.png` |
| WinImageNormalizer | `WinImageNormalizer.png` | `WinImageNormalizer.ico` | `WinImageNormalizer_poster.png` |
| AudioFileForPhone | `AudioFileForPhone.png` | `AudioFileForPhone.ico` | `AudioFileForPhone_poster.png` |

`PS Visuals/tesktit.txt` is a separate 692-byte reference text file according to the owner listing. Read it, compare it with audited copy and record discrepancies. Never publish it automatically or elevate it above explicit current owner decisions.

## Slot contract and approval

All 47 source files are inventoried. There are **33 selected website-use slots required for launch**: seven characters, seven avatars, eight social previews, one site-icon PNG, seven tool-icon PNGs, two responsive backgrounds and one favicon ICO. The remaining **14 ancillary slots** are seven tool ICOs and seven posters, retained in the inventory; use posters only where the approved layout benefits. Do not invent a gallery to display every file. The earlier core count of 23 remains descriptive, not the complete v2 integration requirement.

Owner selection/reuse approval is recorded in each slot. Actual dimensions, alpha, hashes, source provenance/license and successful integration remain unverified until intake. Do not confuse approval of a design with inspection of a file or permission to attach an invented license. The code's GPL license does not automatically apply to artwork. Public distribution of approved website derivatives is intended by the owner; document any separate rights terms that actually exist.

## Import and preservation

Run tools/inspect_assets.py against the parent containing the two folders. It is read-only except for its requested local JSON report. It checks all filenames, documented size hints, PNG chunk CRCs/headers, ICO entry bounds, dimensions and hashes. It flags missing/conflicting/changed sources and rejects path escapes/symlinks. A size mismatch is a review issue, not proof that the artwork is wrong. Never silently accept an unlisted substitute. Visual inspection is still required; a valid PNG structure does not prove correct identity or appearance.

Preserve original masters privately. Record source relative path/hash, actual format/dimensions/color/alpha, derivative path/hash/size/encoding, permitted crop and verification evidence. Keep private absolute paths out of Git. Normalize derivative names using expected_filename_stem with truthful file extensions. `WinImageNormalizer` is the display name; its code repository remains `WinImgNormalizer`.

## Processing and presentation

Codex may resize proportionally, optimize/convert encodings, remove private nonvisual metadata and apply already approved crops. Preserve faces, hair, poses, props, colors and typography. No generation, drawing, redesign, retouching, background removal, mirroring or new social compositions. Use contain-style hero framing and supplied square avatars. Neutral development rectangles are temporary only when useful for implementation or an unresolved missing file.

Character masters are recommended 3:4, avatars 1:1, social exports 1200×630, backgrounds approximately 16:9 and 9:16. Actual delivered dimensions may differ. Never upscale simply to meet an aspirational master size. Fit social exports without cropping text/faces; neutral padding for a small ratio mismatch is permitted. Flag a material composition problem for the owner.

Preserve alpha where present. Use responsive WebP/PNG or another supported on-page encoding; use broadly compatible PNG/JPEG for social previews. Target selected portrait transfers around 250 KB mobile/500 KB desktop, avatars around 80 KB, social images around 350 KB and backgrounds around 300 KB while preserving quality. These are review targets, not guarantees. Reserve intrinsic dimensions, prioritize the visible hero appropriately and lazy-load lower-page media. Do not fetch all seven large characters or both backgrounds at once.

Keep names, headings, descriptions, version labels and buttons as editable WordPress text. `_SM` artwork drives sharing metadata; promotional posters do not replace it. Check baked-in text against final audited facts and flag conflicts. Use meaningful alt text only where the image adds information; decorative art can use empty alt when nearby content supplies its meaning. Linked image-only controls need accessible names.

## Visual identity checks

WinBookSplit: silver-haired male, amber book/stylus. WinPDFMerger: midnight-blue bob, violet document device. WinVidCompress: approved revised copper-red braided ponytail/shaved sides, cyan shoulder camera. UniversalPodcastDownloader: deep brown skin, platinum curls/headphones, emerald receiver. WinAudioClean: low dark bun, red waveform equipment. WinImageNormalizer: black fringe, goggles, brass calibration equipment. AudioFileForPhone: freckled ash-blonde bob, blue in-ear monitors/player. Inspect supplied files as the source of truth; do not redraw from these notes.

Completion: all 47 sources accounted for, all 33 selected website slots technically verified and integrated, correct identities/wording and desktop/mobile framing checked, optimized public derivatives committed with provenance, no forbidden tool-repository imagery and no unresolved placeholder remains on launch pages.
