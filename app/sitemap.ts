import type { MetadataRoute } from "next";
import { activities } from "./data";
import { SITE_URL } from "./seo";

export const dynamic = "force-static";

export default function sitemap(): MetadataRoute.Sitemap {
  const routes = ["", "about-us", "activities", "packages", "gallery", "food-drinks", "contact-us", "gta-6"];
  const lastModified = new Date();
  return [
    ...routes.map((route) => ({
      url: `${SITE_URL}/${route ? `${route}/` : ""}`,
      lastModified,
      changeFrequency: "monthly" as const,
      priority: route === "" ? 1 : route === "gta-6" ? 0.85 : 0.7,
    })),
    ...activities.map((activity) => ({
      url: `${SITE_URL}/activities/${activity.slug}/`,
      lastModified,
      changeFrequency: "monthly" as const,
      priority: 0.8,
    })),
  ];
}
