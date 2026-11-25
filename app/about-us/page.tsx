import type { Metadata } from "next";
import { BadgeCheck, Clock3, Gamepad2, Wallet } from "lucide-react";
import { PageIntro, WhatsAppButton } from "../components";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "About Level X Gaming Arena",
  description: "Get to know Level X Gaming Arena in Karachi, a 24/7 venue for PC gaming, PS5, premium snooker and private table tennis.",
  path: "/about-us/",
  keywords: ["about Level X Gaming Arena", "gaming arena Karachi", "PC gaming Karachi", "PS5 Karachi"],
});

export default function About() {
  return (
    <>
      <section className="page-hero"><div className="container"><PageIntro eyebrow="Who we are" title="About Level X Gaming Arena" copy="A place for great games, friendly competition and memorable sessions. Level X brings premium gaming and table experiences together in one welcoming arena." /></div></section>
      <section className="content-section">
        <div className="container">
          <div className="feature-grid">
            <article className="info-card"><Gamepad2 /><h2>Premium experiences</h2><p>From high-performance PC gaming and PS5 to snooker and private table tennis, there is a game for every mood.</p></article>
            <article className="info-card"><Clock3 /><h2>Open around the clock</h2><p>We welcome players 24 hours a day, seven days a week. Plan your visit whenever it works for you.</p></article>
            <article className="info-card"><Wallet /><h2>Straightforward pricing</h2><p>Browse our published rates and offers. Our team confirms the final details before your session.</p></article>
          </div>
          <div className="cta-band about-cta">
            <BadgeCheck size={30} className="cta-icon" />
            <h2>Come play at Level X.</h2>
            <p>Have a question or want to plan your visit? Our team is one message away.</p>
            <WhatsAppButton message="Hi Level X, I would like to know more about the arena." />
          </div>
        </div>
      </section>
    </>
  );
}
