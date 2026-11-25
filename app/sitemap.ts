import type { MetadataRoute } from "next";
import { activities } from "./data";
export default function sitemap(): MetadataRoute.Sitemap {
 const routes = ["","about-us","contact-us","packages","privacy-policy","cookie-policy","terms-and-conditions","disclaimer","photo-credits"];
 const now = new Date();
 return [
  ...routes.map(route=>({url:`https://levelxgamingarena.com/${route ? route+"/" : ""}`,lastModified:now,changeFrequency:"monthly" as const,priority:route===""?1:0.6})),
  ...activities.map(a=>({url:`https://levelxgamingarena.com/activities/${a.slug}/`,lastModified:now,changeFrequency:"monthly" as const,priority:0.8}))
 ];
}
