import type { Metadata } from "next";
import { PageIntro } from "../components";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Cookie Policy",
  description: "Read how cookies and similar browser technologies are used on the Level X Gaming Arena website.",
  path: "/cookie-policy/",
  keywords: ["Level X cookie policy", "website cookies"],
});

export default function Cookies() {
  return (
    <>
      <section className="page-hero"><div className="container"><PageIntro eyebrow="Legal information" title="Cookie policy" copy="A simple explanation of cookies and similar technologies on this website." /></div></section>
      <section className="content-section"><div className="container content-grid">
        <article className="legal-card"><h2>What are cookies?</h2><p>Cookies are small files stored by your browser. They can help websites remember preferences and understand basic site usage.</p></article>
        <article className="legal-card"><h2>How this site works</h2><p>Level X is delivered as a static website. It does not use login sessions, customer dashboards or server-side booking cookies.</p></article>
        <article className="legal-card"><h2>Managing cookies</h2><p>You can review, block or delete cookies through your browser settings. Third-party services you choose to open, such as WhatsApp, may use their own technologies under their policies.</p></article>
      </div></section>
    </>
  );
}
