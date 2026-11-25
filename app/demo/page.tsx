import type { Metadata } from "next";
import Link from "next/link";
import { pageMetadata } from "../seo";

export const metadata: Metadata = {
  ...pageMetadata({
    title: "Level X Gaming Arena",
    description: "Explore gaming experiences, packages and WhatsApp booking at Level X Gaming Arena in Karachi.",
    path: "/",
    keywords: ["Level X Gaming Arena"],
  }),
  robots: { index: false, follow: true },
};

export default function DemoAlias() {
  return (
    <section className="page-hero">
      <div className="container page-intro">
        <span className="eyebrow">Level X Gaming Arena</span>
        <h1>Our homepage has moved.</h1>
        <p>Visit the Level X website for gaming activities, packages and WhatsApp booking.</p>
        <Link className="button button-primary" href="/">Go to homepage</Link>
      </div>
    </section>
  );
}
