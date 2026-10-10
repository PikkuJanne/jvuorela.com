# Copy, versions and editing workflow

All copy is draft. The owner can change wording before publication and through WordPress afterward. Keep the bundle's technical limitations available for writers, while keeping the visitor page concise.

## Data files

- `site.json`: page map and shared draft wording.
- `tools.json`: seven factual draft page records, exact display/runtime naming, seed asset references.
- `releases.json`: dated source evidence and separate empty launch-verification fields.
- `COPY_REVIEW.md`: final wording review and factual check procedure.

Internal editorial notes, observed release status, verification records and asset/task IDs are not public page copy. Only the intended visitor fields are seeded; temporary pending-release labels belong to private preview states.

JSON is seed/review data, not a mandatory live dependency. A theme must not read a bundled JSON file on every request and thereby make WordPress edits ineffective. Do not populate a release verification result from its observed status automatically.

## Visitors should see

A strong tool title, a short purpose, three benefits, real requirements, a concise start instruction, Download v1.0.0 and Source & instructions. Keep detailed logs, exit codes, evidence IDs and development history in the repository. Concise limitations belong near the relevant claim; avoid burying material tradeoffs.

## Pending releases in previews

All pages can be built now. For an unverified missing release, show a neutral “Release link pending” preview state without inventing a download destination. Do not use `href="#"` as a live action. The final public site has all seven verified v1.0.0 links. Current source links are useful secondary links, not evidence that a release package exists.

## Display names

Website WinPDFMerger → launcher WinPDFMerge.bat. Website WinImageNormalizer → repository/launcher WinImgNormalizer. Preserve UniversalPodcastDownloader as one exact name. Tool release v1.0.0 and website release v1.0.0 are independent identities, even when their strings match.

## Editor acceptance

Using an ordinary editor account where permitted, change one tagline, one benefit, one requirement and one button URL. Save, reload the editor and view the front end. Confirm valid block markup, readable editor styling and the expected output. Update/reinstall the theme candidate and rerun the seed in plan mode: the edits must survive and conflicts must be reported.

Before launch, compare home cards with the individual pages. Export current approved page content for review and retain a database backup; do not publish an old seed snapshot over reviewed wording. Record the approved content snapshot/hash with the deployment candidate.

## Owner editing guide to produce

Codex must show the actual admin path to edit Home and Tool Pages, replace images, update download links/requirements, manage menu/footer links, set social images/site icon, preview changes, and recover a Page revision. Include screenshots of the completed admin workflow when possible; redact private details. Explain that theme updates do not update tool facts automatically.
