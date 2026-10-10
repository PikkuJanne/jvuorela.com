# Page structure using supplied final art

## Canonical routes

Home `/`; seven root-level tool paths from `content/tools.json`; `/impressum/`; `/privacy/`; actual unknown URLs return HTTP 404 with the theme's fallback. Use relative internal links resolved against the current site origin. Production canonical links point to HTTPS only after the environment is actually production.

No nested `/tools/` parent is required. Keep historical `/tools/` proposals out of generated menus/canonicals. If those routes already exist on a real site, inventory first and plan redirects.

## Home

A restrained header: owner/site identity and a link to the seven-tool section; avoid fitting all seven long names into a narrow top bar. The hero holds an editable short heading and introduction. The lineup is seven large image-led cards; each uses a supplied square avatar, exact tool name and one sentence. Cards link to real tool Pages. An uneven count can use a lead card plus a six-card arrangement or a fluid grid; choose the simplest layout that looks deliberate at measured widths. No separate team illustration is required.

Do not repeat requirements, version labels or giant button stacks across cards. Footer holds legal links, the GitHub profile and a source-code link to the actual public website repository. Use the supplied website identity and existing PNG tool icons.

## Tool Page

Desktop: text and portrait art side by side; artwork gets roughly half the hero width. Tool title, tagline, short description and primary download link remain visible without a complex interaction. Follow with three concise benefits, requirements, short quick start and source/documentation links. A small link returns to the lineup; previous/next tool navigation is optional if unobtrusive.

Mobile: use a deliberate stacked composition, with readable title/summary, a bounded artwork area and the primary action reachable without excessive scrolling. Avoid a fixed full-screen hero that pushes all useful content below an enormous image. Keep the complete pose with `contain` by default; crop only where approved.

Long names, especially UniversalPodcastDownloader, must wrap cleanly. Do not shrink the title to unreadable size to force one line. Use sensible fluid sizes and wrapping; no clipping, horizontal scroll or ellipsis for the main product name.

## Placeholders

Use neutral rectangular HTML regions with the manifest's aspect ratio, a thin outline and a small diagnostic slot label. No drawn silhouettes, pseudo-characters or generated illustrations. Placeholder labels are private-preview diagnostics, not final visitor copy. Missing-art fallbacks should not cause layout shifts or broken-image icons.

Early layout work establishes composition, hierarchy and responsive behavior. Final CSS colors, typography, surface detail and motion should complement the already approved art direction. Keep CSS token-based so this does not require replacing page content.

## Basic interaction floor

Visible keyboard focus, standard links/buttons, descriptive accessible names, logical headings, readable contrast, click targets around 44 CSS pixels where practical, no hover-only essential information, and no autoplay/forced introduction. Respect reduced motion; essential content works with JavaScript disabled. Prefer a navigation structure that needs no mobile menu script at all.

## Required screenshots for artwork handoff

Home and one representative tool at 1440×900, 1024×768 and 390×844; all seven tool titles at 320 CSS-pixel width. Record actual image box dimensions, full-height behavior, focal-safe areas and text lengths in `templates/ARTWORK_LAYOUT_MEASUREMENTS.md`. Use the actual supplied dimensions to fit the completed artwork. No creative-production request is needed; record any actual delivery/composition problem.
