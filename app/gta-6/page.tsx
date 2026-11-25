import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowDown, ArrowUpRight, CalendarDays, Gamepad2, MapPin, MonitorPlay, ShieldCheck, Sparkles } from "lucide-react";
import "./gta-6.css";
import { pageMetadata } from "../seo";

const officialSite = "https://www.rockstargames.com/VI";
const launchNews = "https://www.rockstargames.com/newswire/article/39akk424a9oo53/grand-theft-auto-vi-now-available";
const extendedLook = "https://www.rockstargames.com/newswire/article/9k2kaa1o3297k9/grand-theft-auto-vi-an-extended-look";

export const metadata: Metadata = pageMetadata({
  title: "GTA 6 (Grand Theft Auto VI) – Release, Platforms & Leonida Guide",
  description: "Grand Theft Auto VI is out now. Explore Rockstar-confirmed release details, PS5 and Xbox platforms, Jason and Lucia, Vice City and the state of Leonida.",
  path: "/gta-6/",
  keywords: ["GTA 6", "GTA VI", "Grand Theft Auto VI", "GTA 6 release date", "GTA 6 platforms", "GTA 6 Vice City", "Leonida", "GTA 6 characters"],
});

const faqs = [
  { question: "Is GTA 6 released?", answer: "Yes. Rockstar Games announced that Grand Theft Auto VI became available worldwide on 19 November 2026." },
  { question: "Which platforms support GTA 6?", answer: "Rockstar lists PlayStation 5 and Xbox Series X|S as the launch platforms." },
  { question: "Where does GTA 6 take place?", answer: "The story is set in the fictional state of Leonida, including a modern-day return to Vice City." },
  { question: "Who are the main characters in GTA 6?", answer: "Rockstar's official story introduction identifies Jason and Lucia as the central duo." },
  { question: "Is GTA 6 available on PC?", answer: "Rockstar's current official platform listing names PlayStation 5 and Xbox Series X|S. A PC release has not been confirmed on that listing." },
];

const faqSchema = {
  "@context": "https://schema.org",
  "@type": "FAQPage",
  mainEntity: faqs.map(({ question, answer }) => ({
    "@type": "Question",
    name: question,
    acceptedAnswer: { "@type": "Answer", text: answer },
  })),
};

export default function GTA6Page() {
  return (
    <div className="gta-page">
      <section className="page-hero gta-hero">
        <div className="gta-hero-art" aria-hidden="true">
          <Image
            src="https://www.rockstargames.com/VI/_next/static/media/poster_full.0az_iud2g3y4j.jpg?akim=1&imdensity=1&imwidth=3840"
            alt="Grand Theft Auto VI official cover artwork"
            fill
            priority
            sizes="(max-width: 760px) 100vw, 62vw"
            unoptimized
          />
        </div>
        <div className="gta-hero-shade" />
        <div className="container gta-hero-content">
          <span className="eyebrow"><span className="gta-live-dot" /> ROCKSTAR GAMES / NOW AVAILABLE</span>
          <p className="gta-kicker">WELCOME TO LEONIDA</p>
          <h1>GRAND THEFT<br /><em>AUTO VI</em></h1>
          <p className="gta-hero-copy">The wait is over. Step into a modern-day Vice City and the wider state of Leonida in Rockstar Games' latest open-world crime saga.</p>
          <div className="gta-hero-actions">
            <a className="button button-primary" href={officialSite} target="_blank" rel="noopener noreferrer">Visit the official game site <ArrowUpRight size={16} /></a>
            <a className="button button-outline" href="#release-details">Explore the guide <ArrowDown size={15} /></a>
          </div>
          <div className="gta-release-stamp"><span>WORLDWIDE RELEASE</span><strong>19 NOV 2026</strong><small>PLAYSTATION 5 · XBOX SERIES X|S</small></div>
        </div>
        <div className="gta-hero-bottom"><span>LEVEL X / GAME SPOTLIGHT 001</span><span>INDEPENDENT GAMING GUIDE</span></div>
      </section>

      <section className="gta-facts-strip" id="release-details">
        <div className="container gta-facts-grid">
          <article><CalendarDays size={20} /><span>RELEASE DATE</span><strong>19 November 2026</strong></article>
          <article><Gamepad2 size={20} /><span>PLATFORMS</span><strong>PS5 · Xbox Series X|S</strong></article>
          <article><MapPin size={20} /><span>SETTING</span><strong>Leonida · Vice City</strong></article>
          <article><ShieldCheck size={20} /><span>STATUS</span><strong>Released worldwide</strong></article>
        </div>
      </section>

      <section className="gta-intro-section">
        <div className="container gta-intro-grid">
          <div><span className="eyebrow">01 / THE NEXT CHAPTER</span><h2>A new story.<br /><em>A familiar coastline.</em></h2></div>
          <div><p>Grand Theft Auto VI follows Jason and Lucia as an easy score goes wrong and pulls them into a criminal conspiracy stretching across Leonida. Rockstar describes the setting as a return to modern-day Vice City, surrounded by a broader state with its own distinct atmosphere.</p><p>This guide focuses on details Rockstar has officially published. Unconfirmed rumours, leaks and community theories are not presented as facts.</p><a className="gta-inline-link" href={launchNews} target="_blank" rel="noopener noreferrer">Read Rockstar's launch announcement <ArrowUpRight size={15} /></a></div>
        </div>
      </section>

      <section className="gta-world-section">
        <div className="container gta-world-grid">
          <div className="gta-world-visual">
            <Image src="https://www.rockstargames.com/VI/_next/static/media/shard0.0_vm6ya9-h67x.png?akim=1&imdensity=1&imwidth=3840" alt="Official Grand Theft Auto VI promotional artwork" width={1200} height={900} unoptimized />
            <span>THE STATE OF LEONIDA / OFFICIAL ARTWORK</span>
          </div>
          <div className="gta-world-copy"><span className="eyebrow">02 / PEOPLE & PLACES</span><h2>Vice City is only<br /><em>the beginning.</em></h2><p>Leonida is the fictional state at the heart of GTA VI. Its best-known city, Vice City, returns in a contemporary setting. Rockstar's official world introduction frames the story around a sun-soaked place with a darker criminal undercurrent.</p><div className="gta-location-points"><div><MapPin size={17} /><span><strong>Vice City</strong><small>A modern-day return to the iconic city</small></span></div><div><Sparkles size={17} /><span><strong>Leonida</strong><small>The wider state surrounding the story</small></span></div></div><a className="gta-inline-link" href={officialSite} target="_blank" rel="noopener noreferrer">Explore the official world guide <ArrowUpRight size={15} /></a></div>
        </div>
      </section>

      <section className="gta-characters-section">
        <div className="container">
          <div className="gta-section-heading"><span className="eyebrow">03 / THE CENTRAL DUO</span><h2>Meet Jason <em>&amp; Lucia.</em></h2><p>Rockstar's official introduction places the pair at the centre of a story about trust, survival and a score that spirals out of control.</p></div>
          <div className="gta-character-grid">
            <article className="gta-character-card gta-jason"><span>01 / CHARACTER</span><h3>Jason</h3><p>Jason and Lucia's plans are tested when an easy score goes wrong, drawing them into a conspiracy across Leonida.</p></article>
            <article className="gta-character-card gta-lucia"><span>02 / CHARACTER</span><h3>Lucia</h3><p>Lucia is the other half of the central partnership. Rockstar's official story summary places their reliance on each other at the heart of the journey.</p></article>
          </div>
          <p className="gta-source-note">Character descriptions are intentionally limited to Rockstar's published story introduction.</p>
        </div>
      </section>

      <section className="gta-trailer-section">
        <div className="container gta-trailer-panel">
          <div><span className="eyebrow">04 / OFFICIAL VIDEO</span><h2>Watch the <em>launch trailer.</em></h2><p>Watch official Grand Theft Auto VI videos, including the launch trailer and extended look, through Rockstar's own channels.</p></div>
          <div className="gta-trailer-actions"><a className="button button-primary" href={officialSite} target="_blank" rel="noopener noreferrer"><MonitorPlay size={17} /> Watch official videos <ArrowUpRight size={15} /></a><a className="gta-inline-link" href={extendedLook} target="_blank" rel="noopener noreferrer">Read about the extended look <ArrowUpRight size={15} /></a></div>
        </div>
      </section>

      <section className="gta-updates-section">
        <div className="container">
          <div className="gta-section-heading"><span className="eyebrow">05 / ROCKSTAR NEWSWIRE</span><h2>Latest official <em>updates.</em></h2></div>
          <div className="gta-update-list">
            <a href={launchNews} target="_blank" rel="noopener noreferrer"><span>19 NOV 2026</span><strong>Grand Theft Auto VI is now available worldwide</strong><ArrowUpRight size={18} /></a>
            <a href={extendedLook} target="_blank" rel="noopener noreferrer"><span>06 AUG 2026</span><strong>Grand Theft Auto VI: An Extended Look</strong><ArrowUpRight size={18} /></a>
            <a href="https://www.rockstargames.com/newswire?tag=vi" target="_blank" rel="noopener noreferrer"><span>ROCKSTAR NEWSWIRE</span><strong>Browse all GTA VI announcements and updates</strong><ArrowUpRight size={18} /></a>
          </div>
        </div>
      </section>

      <section className="gta-faq-section">
        <div className="container gta-faq-grid">
          <div><span className="eyebrow">06 / QUICK ANSWERS</span><h2>GTA VI <em>questions.</em></h2><p>Key facts based on Rockstar's official information available at the time this page was last reviewed.</p></div>
          <div className="gta-faq-list">{faqs.map((item) => <details key={item.question}><summary>{item.question}<span>+</span></summary><p>{item.answer}</p></details>)}</div>
        </div>
      </section>

      <section className="gta-final-section">
        <div className="container gta-final-panel"><span className="eyebrow">LEVEL X / GAME SPOTLIGHT</span><h2>Ready for your <em>next session?</em></h2><p>Explore the games and experiences available at Level X Gaming Arena in Karachi.</p><div><Link className="button button-primary" href="/activities/">Explore Level X activities <ArrowUpRight size={15} /></Link><Link className="button button-outline" href="/packages/">View gaming packages <ArrowUpRight size={15} /></Link></div></div>
      </section>

      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(faqSchema) }} />
    </div>
  );
}
