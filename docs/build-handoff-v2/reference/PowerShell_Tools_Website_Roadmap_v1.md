> HISTORICAL SNAPSHOT — not active instructions. Current DECISIONS.md, ROADMAP.md and the v2 asset/GitHub/cleanup specifications supersede proposals here. Release observations are dated and require fresh verification.

# jvuorela.com — PowerShell Tools Website Roadmap
Version: 1.1 | 9 October 2026 | Status: proposed project baseline

## Goal
Create a small, visually striking website presenting Janne Vuorela's seven locally running PowerShell tools. Publish through IONOS at jvuorela.com. Visual impact takes priority, while text, navigation, and download links remain readable and functional.

Every tool receives one original character within a shared cyberpunk world inspired by Masamune Shirow's Ghost in the Shell and the user's “Sargent” reference. Final visual design and artwork happen last.

## Confirmed hosting and proposed architecture
Hosting: IONOS WordPress Start. Domain: jvuorela.com. Confirmed by the owner on 9 October 2026.

Recommended baseline for this hosting: WordPress with one lightweight custom theme, native Pages, plain CSS, and small amounts of vanilla JavaScript only where a specific effect needs it. The site needs no added plugins or page builder for its presentation features. Audit any IONOS-preinstalled or required components before promising zero active plugins. Keep fonts and artwork locally hosted.

Keep the custom theme source in a dedicated Git repository, separate from the seven tool repositories. Share layouts through theme templates. Maintain page content through the WordPress editor. Download buttons lead to verified GitHub release pages; documentation and issues remain with each tool's repository. Avoid a live GitHub API dependency.

Hosting choice does not itself require using WordPress. A fully static site remains an alternative if the actual account's webspace configuration permits it, but the proposed baseline now uses the purchased WordPress environment.

IONOS documents SFTP access for WordPress hosting. Confirm theme installation, access, backup facilities, and domain assignment in the actual account during M0.
Sources:
- https://www.ionos.com/help/wordpress/wordpress-administration/creating-an-sftp-user-for-wordpress/
- https://www.ionos.com/hosting/wordpress-hosting

## Scope and page map
One home page with the complete seven-tool lineup; seven individual tool pages; Impressum and privacy information as applicable; one styled 404 page.

| Tool | Proposed path | Presentation focus, to verify against current release | Repository |
|---|---|---|---|
| WinBookSplit | /winbooksplit/ | Split books/PDFs into manageable parts | https://github.com/PikkuJanne/WinBookSplit |
| WinPDFMerger | /winpdfmerger/ | Merge PDFs and produce a smaller sharing copy | https://github.com/PikkuJanne/WinPDFMerger |
| WinVidCompress | /winvidcompress/ | Compress video files | https://github.com/PikkuJanne/WinVidCompress |
| UniversalPodcastDownloader | /universalpodcastdownloader/ | Download podcast episodes | https://github.com/PikkuJanne/UniversalPodcastDownloader |
| WinAudioClean | /winaudioclean/ | Clean and normalize audio | https://github.com/PikkuJanne/WinAudioClean |
| WinImageNormalizer | /winimagenormalizer/ | Normalize and convert image files | https://github.com/PikkuJanne/WinImgNormalizer |
| AudioFileForPhone | /audiofileforphone/ | Prepare podcasts and audiobooks for phone playback | https://github.com/PikkuJanne/AudioFileForPhone |

Use WinImageNormalizer as the display name and preserve WinImgNormalizer in repository links. Confirm any other display-name/script-name differences during the audit.

Each tool page contains: name; one-sentence purpose; character artwork; three concise benefits; supported Windows/PowerShell versions and dependencies; a short quick-start; release/download link; source and documentation links. Only publish claims verified against the actual release. Keep longer instructions in GitHub.

## Milestones

### M0 — Confirm the foundation
Hosting plan and target domain are confirmed. Confirm WordPress admin/SFTP access, domain status, any existing website that must be preserved, website language (proposed: English), public identity, and repository location. Document the page map and custom-theme architecture with zero added plugins.
Done when: hosting route and scope are recorded, with unresolved decisions clearly identified.

### M1 — Audit tools and prepare content
Review current README files and published releases for all seven tools. Record exact names, capabilities, versions, requirements, licenses, installation steps, and verified links. Mark unpublished tools accurately rather than inventing download availability. Prepare short page copy.
Done when: all seven content records are complete and reviewed. Tool improvements remain separate work.

### M2 — Build the structural prototype
Build the custom theme, home, a reusable tool-page template, native WordPress Pages, supporting pages, and navigation using neutral placeholders. Reserve portrait/landscape artwork slots so images can be added without rebuilding pages. Support desktop and mobile layouts.
Done when: every route works and the content fits. No final artwork or visual theme is required yet.

### M3 — Complete and check the functional site
Populate all seven pages. Check links, downloads, keyboard navigation, text readability, mobile overflow, and behavior without JavaScript. Add page titles, descriptions, sitemap, social-preview placeholders, and 404 handling.
Done when: the complete site works with placeholders and no plugins.

### M4 — Prepare publication
Create a repeatable custom-theme deployment package and installation/rollback instructions. Include database/content and uploads in the backup plan. Prepare a private preview using the actual IONOS hosting capabilities. Verify HTTPS and domain configuration before changing public routing. Review legal/footer content against the actual site setup; keep analytics and embeds out of the initial scope.
Done when: the tested site can be published and restored, and only final creative work remains.

### M5 — Final visual design and seven characters
Confirm exactly which “Sargent” reference the user means before character production. Establish the shared art direction: dark cyberpunk atmosphere, restrained interface detail, strong typography, and distinct tool accents.

Design seven original characters with different silhouettes, equipment, poses, and visual cues linked to their tool. Preserve a coherent team identity. Produce one pilot character/page first, review it, then extend the approved direction to the remaining six. Prior icons/posters are optional reference material; do not assume they define the new characters.

Create character artwork, page backgrounds where useful, home-page portraits, favicon, and social-preview images. Deliver optimized web assets and reusable masters. Keep tool names and page copy as real HTML text. Use subtle motion only when it supports the composition; avoid forced intro sequences.
Done when: all seven characters and the finished visual site are approved and checked on desktop and mobile.

### M6 — Publish and verify
Deploy the approved theme and content to IONOS WordPress Start at jvuorela.com. Verify HTTPS, canonical domain/redirects, all routes, release links, artwork loading, mobile layout, and 404 behavior on the live site. Tag the website release and retain the previous deployment.
Done when: the live site matches the approved preview and all seven tool pages pass the launch checks.

## Completion criteria
- jvuorela.com is live over HTTPS.
- Home page and all seven presentation pages are complete.
- Each tool has its own approved original character.
- Download and documentation links match verified releases.
- Zero added plugins required for presentation; any retained hosting components documented; no unnecessary external scripts.
- Desktop and mobile pages remain readable and functional.
- Required supporting pages are completed for the actual setup.
- Source, deployment package, artwork masters, and rollback instructions are retained.

## Maintenance
Update a tool's content when its release or requirements change. Recheck links at each website update. Keep published tool version information explicit and manually maintained; automatic update widgets are unnecessary for seven tools. Maintain WordPress and the custom theme, and verify backups after updates.

## Evidence limits
This roadmap is a proposed plan, not a completed repository or hosting audit. Several supplied GitHub pages could not be retrieved during preparation, and search-cached pages can lag current releases. M1 must verify current source/release details before website claims are finalized.

## Next action
Complete M0: confirm WordPress admin/SFTP access, existing domain content, and the proposed English-language/custom-theme baseline. Then prepare the seven-tool content inventory. Begin final character artwork only at M5.

