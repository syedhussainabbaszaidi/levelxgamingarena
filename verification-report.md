# `/demo` Landing Page — Verification Report

**Date:** 2026-10-02
**Page:** `GET /demo` → `PageController@demo` (route named `public.demo`)
**Page:** `resources/views/demo/home.blade.php` (full redesign, all 10 sections)
**Assets:** `npm run build` (vite + tailwind) — CSS rebuilt so all demo classes exist

## Summary of results

| Check | Result |
|---|---|
| HTTP status on `/demo` | **200** (`view:clear` after rebuild) |
| Console/page errors | **0** (`SyntaxError: querySelector('[object HTMLElement]')` fixed in `app.js` + `public.js` reveal boot) |
| Horizontal overflow (desktop 1440px) | **0px** |
| Horizontal overflow (mobile 390px) | **0px** |
| Hero bg image opacity | **0.14** (full-opacity purple trophy graphic fixed) |
| Hero grid layout (desktop) | 2 columns, 521px + 444px |
| Hero grid layout (mobile) | 1 column (342px) — `lg:` breakpoint correct |
| Console errors during audit | **(none)** |

## Deliverables changed (header/footer untouched)

1. **`resources/views/demo/home.blade.php`** — redesigned landing page:
   - All 10 sections: Hero, Stats, Activities (dynamic from DB), Experience, Pricing, Why Us, Gallery w/ lightbox, Booking CTA, FAQ accordion, Location/Contact
   - Alpine category filter (`setFilter`/`setCategory`), FAQ accordion, gallery lightbox, count-up stats, reduced-motion support
   - All classes are Tailwind `neon-*` / `ink-*` brand utilities, Orbitron/Outfit fonts
   - Dynamic activities from DB (no hardcoding): images, names, categories, capacities, rates
2. **`app/Http/Controllers/PageController.php`** — demo method type hints softened (no more `Resource` hint; `instanceof` guards in gallery map)
3. **`resources/js/app.js`** — `reveal(el, ...)` boot now accepts an element or selector (was throwing `SyntaxError: querySelector '[object HTMLElement]'`); no console errors now
4. **`resources/js/public.js`** — `revealSection(target, ...)` same fix

Extra behind-the-scenes fixes in the blade (no behavior change, just valid CSS):
- `lg:py-22` → `lg:py-[5.5rem]`
- `h-4.5 w-4.5` → `h-[1.125rem] w-[1.125rem]`
- `bg-neon-500/18`, `bg-neon-700/22`, `via-ink-950/88`, `bg-ink-950/92` → arbitrary `/0.xx` forms

## Final verification artifact

Screenshots (clean, no injection artifacts): `final_desktop.png` (1440px) and `final_mobile.png` (390px). Both render the hero with the full-opacity purple artifact gone, correct 2-column layout, and no console errors.

## Notes

- Initial debug screenshots sometimes showed a bright purple "trophy" SVG artifact in `--screenshot` mode; live CDP inspection confirmed the hero background image is computed at opacity `0.14` and the layout is correct — the artifact only appeared during `--screenshot` windowing. Final screenshots are clean.
- `/demo` served with the rebuilt Vite manifest (`public/build/manifest.json` → `app-ZASrYQv9.css`); old manifest files are not served (404).
