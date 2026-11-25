# Level X Gaming Arena

Static business website built with **Next.js App Router** and exported as plain HTML, CSS, JavaScript and image assets for cPanel hosting.

## Stack

- Next.js 15
- React 19
- TypeScript
- Static export (`output: "export"`)
- Plain responsive CSS
- Lucide icons

## Local development

```bash
npm install
npm run dev
```

## Production build

```bash
npm run build
```

The fully static website is generated in `out/`. No Node.js server, PHP runtime, Laravel application, database, login system, admin dashboard, customer dashboard or booking API is required in production.

## Website pages

- Homepage
- Activity detail pages (PC Games, PS5, Premium Snooker Room, Private Table Tennis Room)
- Packages and pricing
- About us
- Contact us
- Privacy policy
- Cookie policy
- Terms and conditions
- Disclaimer
- Photo credits

## Booking

Every booking CTA opens WhatsApp with a pre-filled enquiry. The visitor can add a preferred date, time and number of players before sending. The website does not accept online payments or claim to reserve a slot automatically.

## Content updates

Activity descriptions, published prices, promotional windows and prepaid account bundles are maintained in `app/data.ts`. The WhatsApp number is configured there as well.

## Deployment

GitHub Actions builds the static export and uploads the contents of `out/` to the cPanel `public_html/` directory. The deployment workflow preserves existing server-side `.env`, `database/` and `storage/` data rather than deleting potential historical records. These paths are denied by the static site's Apache rules and are not used by the website.
