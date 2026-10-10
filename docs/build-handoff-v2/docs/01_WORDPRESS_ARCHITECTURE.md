# WordPress architecture and repository layout

## Runtime

One standalone classic PHP theme with `theme.json`, editor styles and core block patterns. Native WordPress Pages contain the wording and image blocks. Use the standard WordPress rendering/escaping, media and menu APIs. A page builder, ACF, SEO plugin or bespoke content plugin is unnecessary for this scope. Development tools are not production plugins.

Confirm the actual WordPress and PHP versions during M0. Pin the local setup to a supported combination compatible with IONOS, document it, and test that combination. Do not copy another site's PHP version or assume a marketed hosting feature is enabled in this account.

## Implementation structure to create

| Path | Responsibility |
| --- | --- |
| `theme/jvuorela-tools/style.css` | Theme metadata and main stylesheet or entry |
| `theme/jvuorela-tools/functions.php` | Small bootstrap for named `inc/` modules |
| `theme/jvuorela-tools/theme.json` | Typography/spacing/color tokens, core-block editor settings |
| `theme/jvuorela-tools/header.php`, `footer.php` | Shared navigation/footer, standard WP hooks |
| `theme/jvuorela-tools/front-page.php` | Static home Page content |
| `theme/jvuorela-tools/page.php` | Standard support-page rendering |
| `theme/jvuorela-tools/templates/tool.php` | Reusable tool Page template |
| `theme/jvuorela-tools/404.php`, `index.php` | Actual not-found behavior and required fallback |
| `theme/jvuorela-tools/inc/` | Setup, metadata, menu and narrowly scoped helpers |
| `theme/jvuorela-tools/patterns/` | Unsynced core-block composition starters |
| `theme/jvuorela-tools/assets/css/` | Front-end/editor styles using shared tokens |
| `theme/jvuorela-tools/assets/js/` | Only the small optional enhancement actually needed |
| `content/` | Draft/review seed records and release evidence, not runtime authority |
| `scripts/` | Development-only content import/export and release packaging |
| `tests/`, `evidence/` | Meaningful behavior checks and sanitized results |
| `docs/` | Runbooks and owner editing guide |

The handoff does not contain implemented theme files. Codex creates them incrementally and records real validation. Do not put a nonfunctional scaffold ZIP into WordPress and call it an installable theme.

## Editable content

Use a Page's `post_title` as the single rendered H1; avoid a second H1 in its block content. Templates can place the title at the top of the text column while `the_content()` holds the remaining core-block composition. Keep a consistent `.tool-page`, `.tool-hero`, `.tool-copy`, `.tool-art`, `.tool-benefits`, `.tool-requirements` class contract. Exact implementation may change if the editor round-trip remains intact.

Tagline, description, benefit list, requirements, quick start and button labels/URLs must be editable through normal core blocks. No PHP-embedded per-tool marketing strings. Draft patterns are not synchronized content that silently replaces page-specific copy.

Use core image blocks and Media Library attachment IDs with meaningful alternative text or empty alt when decorative. Responsive output must carry actual intrinsic dimensions and responsive sources. Do not hard-code production attachment IDs in source. Keep a private environment-specific mapping from asset slot IDs to local attachment IDs.

For description/social metadata, use the Page excerpt where suitable and a small native theme meta box for the social attachment ID only if necessary. Secure metadata saves with capability checks, nonce verification, autosave/revision handling, sanitization and escaping. Avoid a new custom settings framework. Use native site icon settings.

Home card descriptions are short editorial copies. They do not need an API or custom content framework. Initially seed from the same JSON records; document the owner action to update a home card when a corresponding page changes. Requirements, version and download claims belong on the tool page so they need not be duplicated on every card.

## Content seed workflow

Codex implements a local/dev-only script (WP-CLI when available) with a read-only plan/diff mode. It creates missing Pages as drafts, assigns templates and creates approved menu entries only on explicit invocation. Do not seed on theme activation, normal requests or ordinary deployments.

Use a stable project-owned marker such as `_jvt_content_id`, not numeric IDs. Existing unmarked slug collisions are conflicts. Rerun must create no duplicates and preserve edited content. Track the previous seed fingerprint; changes since that fingerprint are conflicts that require a diff and explicit resolution, never silent overwrite. URL/attachment translation must preserve block markup. Test both a first import and rerun after an editor change.

The database is authoritative once editorial work begins. Export a fresh review snapshot before preparing a deployment candidate. A local JSON edit is not an instruction to overwrite the live Page. Keep content movement separate from theme-code updates. If WP-CLI is unavailable remotely, provide a normal WordPress editor/media upload procedure that does not require an importer plugin.

## Theme update and data boundaries

A theme ZIP contains only the theme directory and public approved derivatives used by the theme. Page media ordinarily lives in uploads; a theme ZIP alone does not transport pages, menus, media or settings. Document both installation and content/media initialization explicitly. After launch, ship code-only updates by default. Owner edits must survive activation/update of the theme.

Preserve core admin, REST/block-editor and update functionality. Do not broadly disable endpoints for hardening. Avoid duplicated canonical/meta tags and respect any observed required hosting component that emits them.

## Public reconstruction

Apply docs/11_PUBLIC_GITHUB.md. Commit optimized approved public media and a portable setup mapping so a clean public clone can reproduce the candidate without private masters or production credentials. Keep environment attachment IDs/private data outside Git. Source originals only from the two approved local project folders, never tool repositories.
