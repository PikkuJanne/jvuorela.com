# Native Page templates for manual setup

SPDX-License-Identifier: GPL-2.0-or-later

The setup ZIP's `page-templates/` directory contains eleven UTF-8 HTML files decoded directly from `content/pages.json`. They have the same draft wording and core blocks; Page titles, slugs, excerpts, templates and sharing-image choices remain in the JSON records. These files simplify setup through the WordPress admin when hosting has no WP-CLI.

Before pasting any file into the native Page code editor, upload the approved media, create the draft Pages, and resolve **every** placeholder in a local text editor against the reviewed native site:

- `{{attachment:slot}}`: the numeric Media Library attachment ID, without quotes. The unresolved token is deliberately not valid JSON inside an Image block.
- `{{asset:slot}}`: the uploaded approved media's actual URL. Escape it correctly for its HTML attribute.
- `{{page:id}}`: the intended public native Page URL, after the deployment session has verified permalink/rewrite behavior and the Home assignment. Do not use private preview or nonce links.

Search for `{{` and `}}` and confirm none remain. Paste the resolved block markup into the Page code editor, return to the visual editor, and confirm all blocks are valid. Review image choices, alternative text, button destinations and the signed-out protected preview before saving/publishing there. Use the Page records for each native title/slug/excerpt/template and social-image setting; those settings are not encoded in these HTML files.

See `docs/CONTENT_IMPORT.md` for the full native setup route, including separate Impressum/Privacy footer links and Settings → Reading selection of the published 404 content Page. Theme updates never reseed native Pages, media, menus or settings. Existing hosted editor content remains authoritative.
