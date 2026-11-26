import type { Metadata } from "next";
import { GalleryView } from "./gallery-view";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Gaming Arena Gallery in Karachi",
  description: "Explore the gaming setups, arena spaces and activities shown in the Level X Gaming Arena gallery.",
  path: "/gallery/",
  keywords: ["Level X Gaming Arena gallery", "gaming setups Karachi", "gaming arena photos Karachi"],
});

export default function GalleryPage(){return <>
  <section className="gallery-page-intro"><div className="container gallery-page-intro-inner"><span className="eyebrow"><span className="gallery-intro-dot"/> LEVEL X / THE PHOTO COLLECTION</span><h1>Inside <em>the arena.</em></h1><p>Real spaces. Game-ready setups. A closer look at the Level X experience.</p><div className="gallery-intro-rule"><span>EXPLORE THE GALLERY</span><span>01 — 06</span></div></div></section>
  <section className="gallery-page-content"><div className="container"><div className="gallery-page-heading"><span className="eyebrow">THE LEVEL X EXPERIENCE</span><h2>Every angle. <em>Every game.</em></h2><p>Use the filters to explore different parts of the arena. Select any image to view it up close.</p></div><GalleryView/><p className="gallery-source-note">Gallery imagery currently uses the assets already included in the website project. Official social photographs can be added when approved source files are supplied.</p></div></section>
</>;}
