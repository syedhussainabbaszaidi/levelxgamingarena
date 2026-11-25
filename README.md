# Level X Gaming Arena

Production website built with **Next.js 15, React 19, TypeScript and GSAP**. The app is exported as static HTML, CSS, JavaScript and image assets for cPanel hosting.

## Stack
- Next.js App Router with static export
- React 19 and TypeScript
- GSAP + ScrollTrigger for hero entrances, scroll reveals, staggered grids and interactive button motion
- Responsive CSS and Lucide icons
- Page-level metadata, canonical URLs, Open Graph, Twitter cards, JSON-LD, robots.txt and sitemap.xml

## Local development
```bash
npm install
npm run dev
```

## Production build
```bash
npm run lint
npm run build
```

The complete static website is generated in `out/`. cPanel serves these files directly; no Node.js production server, PHP runtime, Laravel application, database, login system, admin dashboard, customer dashboard or booking API is required.

## Pages
Homepage; PC Games; PS5; Premium Snooker Room; Private Table Tennis Room; Packages and pricing; About; Contact; Privacy policy; Cookie policy; Terms and conditions; Disclaimer; Photo credits.

## Booking
Every booking CTA opens WhatsApp with a pre-filled enquiry. No online payments or automatic reservations are processed by this website.

## Content
Activity details, pricing, offers, account bundles, WhatsApp number and gallery data are maintained in `app/data.ts`.

## Deployment
GitHub Actions builds and audits the static export, then uploads the contents of `out/` to cPanel `public_html/`. The canonical public hostname is `https://www.levelxgamingarena.com/`; the apex domain redirects to it.
