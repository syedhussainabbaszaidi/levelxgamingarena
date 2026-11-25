import type { Metadata } from "next";

export const SITE_URL = "https://www.levelxgamingarena.com";
export const SITE_NAME = "Level X Gaming Arena";
export const DEFAULT_DESCRIPTION = "Play PC games, PS5, premium snooker and private table tennis at Level X Gaming Arena. Open 24/7 in Karachi, Pakistan. Enquire and book directly on WhatsApp.";

export function pageMetadata({
  title,
  description,
  path,
  keywords,
}: {
  title: string;
  description: string;
  path: string;
  keywords: string[];
}): Metadata {
  return {
    title,
    description,
    keywords,
    alternates: { canonical: path },
    openGraph: {
      type: "website",
      locale: "en_PK",
      siteName: SITE_NAME,
      title: `${title} | ${SITE_NAME}`,
      description,
      url: path,
      images: [{ url: "/img/gallery/esports-arena.jpg", alt: "Level X Gaming Arena esports and gaming floor" }],
    },
    twitter: {
      card: "summary_large_image",
      title: `${title} | ${SITE_NAME}`,
      description,
      images: ["/img/gallery/esports-arena.jpg"],
    },
  };
}
