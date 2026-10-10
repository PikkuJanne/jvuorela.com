# Artwork layout and browser measurements

SPDX-License-Identifier: GPL-2.0-or-later

Measured on 2026-10-10 against real local WordPress 7.1.3/PHP 8.3.35 on Windows. The JSON contains all selected sources, actual responsive URLs/boxes, browser versions and checks. Each source is owner-supplied and mapped in assets/manifest.json; native WordPress generated smaller proportional copies from the approved public upload originals. No crop or artwork redesign.

| Page/slot | CSS viewport | Supplied source | Rendered image box | Fit | Screenshot |
|---|---|---|---|---|---|
| Home/winbooksplit avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| Home/winpdfmerger avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| Home/winvidcompress avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| Home/universalpodcastdownloader avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| Home/winaudioclean avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| Home/winimagenormalizer avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| Home/audiofileforphone avatar | 390×844 | 1254×1254 | 311.2×311.2 | contain | [view](screenshots/home-390.png) |
| winbooksplit/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/winbooksplit-390.png) |
| winpdfmerger/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/winpdfmerger-390.png) |
| winvidcompress/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/winvidcompress-390.png) |
| universalpodcastdownloader/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/universalpodcastdownloader-390.png) |
| winaudioclean/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/winaudioclean-390.png) |
| winimagenormalizer/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/winimagenormalizer-390.png) |
| audiofileforphone/character | 390×844 | 1086×1448 | 267.2×350.0 | contain | [view](screenshots/audiofileforphone-390.png) |
| Home/winbooksplit avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| Home/winpdfmerger avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| Home/winvidcompress avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| Home/universalpodcastdownloader avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| Home/winaudioclean avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| Home/winimagenormalizer avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| Home/audiofileforphone avatar | 1440×900 | 1254×1254 | 366.0×366.0 | contain | [view](screenshots/home-1440.png) |
| winbooksplit/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/winbooksplit-1440.png) |
| winpdfmerger/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/winpdfmerger-1440.png) |
| winvidcompress/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/winvidcompress-1440.png) |
| universalpodcastdownloader/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/universalpodcastdownloader-1440.png) |
| winaudioclean/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/winaudioclean-1440.png) |
| winimagenormalizer/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/winimagenormalizer-1440.png) |
| audiofileforphone/character | 1440×900 | 1086×1448 | 571.4×610.0 | contain | [view](screenshots/audiofileforphone-1440.png) |

All seven full poses/props and avatar identities were visually checked on the 390/1440 screenshots. CSS uses contain and respects supplied aspect ratios; bounded hero boxes preserve the complete art. At 320 CSS pixels all route titles/navigation/actions fit without horizontal overflow. Portraits are eager/high-priority; card images are native lazy. Mobile downloads one mobile background and no desktop background. Native 480-wide character/360-wide avatar sizes replace oversized mobile selections.

| First viewport | Transfer bytes | LCP ms | CLS |
|---|---:|---:|---:|
| / (390×844) | 286252 | 1212 | 0 |
| /winpdfmerger/ (390×844) | 219143 | 1304 | 0 |

Theme CSS: 6557 raw bytes, 2247 gzip bytes. Theme JS: none. WordPress core may enqueue its local emoji support; the recorded JavaScript-enabled network evidence includes that resource.

Lab conditions: Chrome 154, cache disabled, CDP 150ms latency, 200000B/s download, 93750B/s upload and 4× CPU slowdown on this Windows host. Both observed first views meet the provisional transfer/LCP/CLS targets. These are loopback lab observations, not IONOS, Lighthouse or field-performance claims. Social PNGs are intentionally alpha-preserving and remain above the 350KB review target; they are metadata assets and are not loaded in the first viewport.

Edge 155 and Firefox 153 passed 20 route/viewport cases each with JavaScript disabled, reduced motion and visible keyboard focus. Effective 200% viewport reflow was checked at 720×450 from 1440×900 for Home and all seven tools. Native zoom shortcuts did not alter the headless viewport; manual native zoom and Safari are not claimed. Safari is unavailable on this Windows host. Repeat actual hosted checks during the deployment session.
