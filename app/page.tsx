import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowRight, CheckCircle2, Gamepad2, ShieldCheck, Users } from "lucide-react";
import { activities, accountPackages, faqs, gallery, hourlyPackages, whatsappLink } from "./data";
import { SectionHeading, WhatsAppButton } from "./components";
import { pageMetadata } from "./seo";

export const metadata: Metadata = pageMetadata({
  title: "PC Gaming, PS5, Snooker & Table Tennis in Karachi",
  description: "Visit Level X Gaming Arena in Karachi for premium PC gaming, PS5, snooker and private table tennis. Explore rates and book your session through WhatsApp.",
  path: "/",
  keywords: ["gaming arena Karachi", "PC gaming Karachi", "PS5 gaming Karachi", "snooker club Karachi", "table tennis Karachi", "gaming zone Karachi"],
});

export default function HomePage() {
  return (
    <>
      <section className="hero">
        <div className="container hero-grid">
          <div>
            <span className="eyebrow">Level X Gaming Arena · Open 24/7</span>
            <h1>Game on.<em>Level up.</em></h1>
            <p className="hero-copy">Your next great match starts here. Discover premium PC gaming, PS5, snooker and private table tennis — all under one roof.</p>
            <div className="hero-actions">
              <WhatsAppButton message="Hi Level X, I would like to book a gaming session. Please share availability." />
              <a className="button button-outline" href="#activities">Explore activities <ArrowRight size={16} /></a>
            </div>
            <div className="hero-points">
              <span><CheckCircle2 size={15} /> Open 24 hours</span>
              <span><CheckCircle2 size={15} /> Walk-ins welcome</span>
              <span><CheckCircle2 size={15} /> Easy WhatsApp booking</span>
            </div>
          </div>
          <div className="hero-art">
            <div className="hero-tag"><span>Find your game</span>Four ways to play</div>
            <Image src="/img/gallery/esports-arena.jpg" alt="Wide view of an esports gaming arena with player stations" width={900} height={650} priority />
            <div className="art-caption"><small>Bring your crew</small><strong>Make every match count.</strong></div>
          </div>
        </div>
      </section>

      <div className="arena-promo-strip">
        <div className="container arena-promo-inner">
          <span className="promo-icon"><Sparkles size={17} /></span>
          <div><strong>Your next game night starts here.</strong><span>Explore four experiences, clear rates and easy WhatsApp booking.</span></div>
          <Link href="/packages/">Explore packages <ArrowRight size={15} /></Link>
        </div>
      </div>

      <div className="stats-strip">
        <div className="container stats-grid">
          <div className="stat"><b>24/7 Arena</b><span>Open every day, around the clock</span></div>
          <div className="stat"><b>Premium setups</b><span>Gaming, console and table experiences</span></div>
          <div className="stat"><b>Simple booking</b><span>Talk directly with our team on WhatsApp</span></div>
        </div>
      </div>

      <section className="section" id="activities">
        <div className="container">
          <SectionHeading eyebrow="Choose your experience" title="Find your next game" copy="From competitive PC sessions to a relaxed snooker match, choose the experience that fits your mood." />
          <div className="activity-grid">
            {activities.map((activity) => (
              <article className="activity-card" key={activity.slug}>
                <Image src={activity.image} alt={`${activity.name} gaming experience at Level X Gaming Arena`} width={600} height={380} />
                <div className="activity-body">
                  <span className="tag">{activity.category}</span>
                  <h3>{activity.name}</h3>
                  <p>{activity.description}</p>
                  <div className="price-line">
                    <strong>Rs. {activity.offer.toLocaleString("en-PK")}</strong>
                    <del>Rs. {activity.price.toLocaleString("en-PK")}</del>
                    <span className="muted">/ hour</span>
                  </div>
                  <Link className="text-link" href={`/activities/${activity.slug}/`}>Explore & book <ArrowRight size={15} /></Link>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section alt" id="packages">
        <div className="container">
          <SectionHeading eyebrow="Clear & simple pricing" title="Packages and hourly rates" copy="Browse the published activity rates and selected offers. Our team will confirm the applicable price when you enquire." />
          <div className="package-grid">
            {hourlyPackages.map((item) => (
              <article className="price-card" key={item.name}>
                <div className="package-card-image">
                  <Image src={item.image} alt={item.name + " gaming package at Level X Gaming Arena"} width={560} height={320} />
                  <span className="package-image-label">HOURLY PACKAGE</span>
                </div>
                <h3>{item.name}</h3>
                <div className="price">Rs. {item.offer.toLocaleString("en-PK")}<small> / {item.unit}</small></div>
                <p>{item.note}</p>
                <div className="deal"><span>Regular rate</span><b>Rs. {item.regular.toLocaleString("en-PK")}</b></div>
                {item.monday && <div className="deal"><span>Monday Madness</span><b>Rs. {item.monday.toLocaleString("en-PK")}</b></div>}
                {("midday" in item) && <div className="deal"><span>Mid Day PC</span><b>Rs. {item.midday}</b></div>}
                <a className="text-link" href={whatsappLink(`Hi Level X, please share details for ${item.name} rates and availability.`)} target="_blank" rel="noopener noreferrer">Ask about this rate <ArrowRight size={14} /></a>
              </article>
            ))}
          </div>
          <div className="section-heading account-heading">
            <span className="eyebrow">For regular players</span>
            <h2>Account packages</h2>
            <p>Prepaid play-hour bundles for players who visit regularly. Account packages are activated at the counter.</p>
          </div>
          <div className="account-grid">
            {accountPackages.map((item) => (
              <article className="account-card" key={item.name}>
                <span>{item.hours}</span><strong>Rs. {item.price.toLocaleString("en-PK")}</strong>
                <b>{item.name}</b><p className="muted">{item.description}</p>
                <a className="text-link" href={whatsappLink(`Hi Level X, I am interested in the ${item.name} account package.`)} target="_blank" rel="noopener noreferrer">Enquire <ArrowRight size={14} /></a>
              </article>
            ))}
          </div>
          <div className="section-link"><Link className="button button-outline" href="/packages/">View all packages <ArrowRight size={16} /></Link></div>
        </div>
      </section>

      <section className="experience-banner">
        <div className="container experience-banner-inner">
          <div className="experience-banner-copy">
            <span className="eyebrow">Make time for play</span>
            <h2>Good games.<br /><em>Great company.</em></h2>
            <p>Come solo, bring a friend or gather your crew. Choose your experience and let our team help plan your next visit.</p>
            <WhatsAppButton message="Hi Level X, I am planning a visit. Please share activity availability and current offers." label="Plan your visit" />
          </div>
          <div className="experience-banner-image">
            <Image src="/img/gallery/gaming-setup.jpg" alt="Gaming PC setup ready for a session at Level X Gaming Arena" width={900} height={650} />
            <span>YOUR NEXT SESSION AWAITS</span>
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <SectionHeading eyebrow="The Level X experience" title="A better place to play" copy="A premium gaming environment built around great equipment, straightforward pricing and a welcoming team." />
          <div className="feature-grid">
            <div className="info-card"><Gamepad2 size={25} /><h3>Premium setups</h3><p>Enjoy dedicated gaming PCs, PlayStation 5 and well-equipped table game spaces.</p></div>
            <div className="info-card"><ShieldCheck size={25} /><h3>Clear pricing</h3><p>See the standard rates and offers before you contact the team to arrange your session.</p></div>
            <div className="info-card"><Users size={25} /><h3>Bring your crew</h3><p>Choose a solo session or get your friends together for a competitive match.</p></div>
          </div>
        </div>
      </section>

      <section className="section alt" id="gallery">
        <div className="container">
          <SectionHeading eyebrow="Inside the arena" title="The gaming atmosphere" copy="A look at the spaces, setups and experiences that inspire the Level X vibe." />
          <div className="gallery-grid">
            {gallery.map((item) => (
              <div className="gallery-card" key={item.src}>
                <Image src={item.src} alt={`${item.title} at Level X Gaming Arena`} width={500} height={350} />
                <span>{item.title}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <SectionHeading eyebrow="Good to know" title="Frequently asked questions" />
          <div className="faq-list">
            {faqs.map((item) => <article className="faq-item" key={item.q}><h3>{item.q}</h3><p>{item.a}</p></article>)}
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <div className="cta-band">
            <span className="eyebrow">Ready when you are</span>
            <h2>Let&apos;s get your game started.</h2>
            <p>Tell us what you want to play and when. Our team will help you with the next step.</p>
            <WhatsAppButton message="Hi Level X, I would like to book a session. Please help me with availability and pricing." />
          </div>
        </div>
      </section>
    </>
  );
}
