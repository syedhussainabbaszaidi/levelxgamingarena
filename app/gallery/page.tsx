import type { Metadata } from "next";
import Image from "next/image";
import { GalleryView } from "./gallery-view";
import { PageIntro } from "../components";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Gaming Arena Gallery in Karachi",
  description: "Explore the gaming setups, arena spaces and activities shown in the Level X Gaming Arena gallery.",
  path: "/gallery/",
  keywords: ["Level X Gaming Arena gallery", "gaming setups Karachi", "gaming arena photos Karachi"],
});

export default function GalleryPage(){return <>
  <section className="gallery-page-hero"><div className="container gallery-page-hero-grid"><div><span className="eyebrow">LEVEL X / BEHIND THE SCREENS</span><PageIntro eyebrow="Inside the arena" title="A closer look at game time." copy="Explore the spaces, setups and activities represented in the Level X gallery. Select an image to view it up close."/></div><div className="gallery-page-hero-image"><Image src="/img/gallery/gaming-setup.jpg" alt="Gaming setup at Level X Gaming Arena" width={900} height={650} priority/><span>THE LEVEL X EXPERIENCE</span></div></div></section>
  <section className="gallery-page-content"><div className="container"><div className="gallery-page-heading"><span className="eyebrow">01 / THE PHOTO COLLECTION</span><h2>Inside <em>Level X.</em></h2><p>Use the filters to explore different parts of the arena.</p></div><GalleryView/><p className="gallery-source-note">Gallery imagery currently uses the assets already included in the website project. Official social photographs can be added when approved source files are supplied.</p></div></section>
</>;}
