import type { Metadata } from "next";
import { Outfit } from "next/font/google";
import "./globals.css";
import { Header, Footer, LogoLoader } from "./components";
import { SiteMotion } from "./site-motion";
import { DEFAULT_DESCRIPTION, SITE_NAME, SITE_URL } from "./seo";

const outfit = Outfit({ subsets: ["latin"], variable: "--font-outfit", display: "swap", weight: ["300", "400", "500", "600", "700", "800", "900"] });

export const metadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  title: { default: "Level X Gaming Arena | PC Gaming, PS5 & Snooker in Karachi", template: "%s | Level X Gaming Arena" },
  description: DEFAULT_DESCRIPTION,
  keywords: ["gaming arena Karachi", "PC gaming Karachi", "PS5 gaming Karachi", "snooker club Karachi", "table tennis Karachi", "gaming zone near me", "Level X Gaming Arena"],
  alternates: { canonical: "/" },
  icons: { icon: "/img/favicon.png" },
  openGraph: { type: "website", locale: "en_PK", url: SITE_URL, siteName: SITE_NAME, title: "Level X Gaming Arena | Game On. Level Up.", description: DEFAULT_DESCRIPTION, images: [{ url: "/img/gallery/esports-arena.jpg", alt: "Level X Gaming Arena gaming floor" }] },
  twitter: { card: "summary_large_image", title: "Level X Gaming Arena | Game On. Level Up.", description: DEFAULT_DESCRIPTION, images: ["/img/gallery/esports-arena.jpg"] },
};

const businessSchema = {
  "@context": "https://schema.org",
  "@type": "EntertainmentBusiness",
  name: SITE_NAME,
  url: SITE_URL,
  description: DEFAULT_DESCRIPTION,
  telephone: "+923422631057",
  email: "info@levelxgamingarena.com",
  openingHours: "Mo-Su 00:00-23:59",
  priceRange: "PKR",
  areaServed: { "@type": "City", name: "Karachi" },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en-PK">
      <body className={outfit.variable}>
        <a className="skip-link" href="#main-content">Skip to content</a>
        <SiteMotion><LogoLoader /><Header /><main id="main-content">{children}</main><Footer /></SiteMotion>
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(businessSchema) }} />
      </body>
    </html>
  );
}
