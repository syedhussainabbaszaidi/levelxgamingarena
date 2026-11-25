import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowRight, CheckCircle2, Gamepad2, ShieldCheck, Sparkles, Users, Clock3, Trophy } from "lucide-react";
import { activities, additionalExperiences, accountPackages, faqs, gallery, hourlyPackages, whatsappLink } from "./data";
import { SectionHeading, WhatsAppButton } from "./components";
import { pageMetadata } from "./seo";

export const metadata: Metadata = pageMetadata({
  title: "PC Gaming, PS5, Snooker & Table Tennis in Karachi",
  description: "Explore Level X Gaming Arena in Karachi: PC gaming, PS5, premium snooker, private table tennis, pool and VIP rooms. View packages and enquire on WhatsApp.",
  path: "/",
  keywords: ["gaming arena Karachi", "PC gaming Karachi", "PS5 gaming Karachi", "snooker club Karachi", "table tennis Karachi", "pool table Karachi", "VIP gaming rooms Karachi", "gaming zone Karachi"],
});

export default function HomePage() {
  return (
    <>
      <section className="hero">
        <div className="container hero-grid">
          <div className="hero-content">
            <span className="eyebrow"><span className="live-dot" /> Level X Gaming Arena · Open 24/7</span>
            <h1>Game on.<em>Level up.</em></h1>
            <p className="hero-copy">Your next great match starts here. Discover competitive PC gaming, PS5 sessions, premium snooker and a private table tennis room — with pool and VIP room enquiries available too.</p>
            <div className="hero-actions">
              <WhatsAppButton message="Hi Level X, I would like to book a gaming session. Please share availability and current rates." />
              <a className="button button-outline" href="#activities">Explore activities <ArrowRight size={16} /></a>
            </div>
            <div className="hero-points">
              <span><CheckCircle2 size={15} /> Open 24 hours</span>
              <span><CheckCircle2 size={15} /> Walk-ins welcome</span>
              <span><CheckCircle2 size={15} /> WhatsApp enquiries</span>
            </div>
          </div>
          <div className="hero-art">
            <div className="hero-tag"><span>Find your game</span>Four featured activities</div>
            <Image src="/img/gallery/esports-arena.jpg" alt="Wide view of an esports gaming arena with player stations" width={900} height={650} priority />
            <div className="art-caption"><small>Bring your crew</small><strong>Make every match count.</strong></div>
          </div>
        </div>
      </section>

      <div className="arena-promo-strip">
        <div className="container arena-promo-inner">
          <span className="promo-icon"><Sparkles size={17} /></span>
          <div><strong>Your next game night starts here.</strong><span>Explore all activities, published rates and prepaid account packages.</span></div>
          <Link href="/packages/">Explore packages <ArrowRight size={15} /></Link>
        </div>
      </div>

      <div className="stats-strip">
        <div className="container stats-grid">
          <div className="stat"><b>24/7 Arena</b><span>Open every day, around the clock</span></div>
          <div className="stat"><b>4 Featured Activities</b><span>PC gaming, PS5, snooker and table tennis</span></div>
          <div className="stat"><b>More to Explore</b><span>Ask about pool and private VIP rooms</span></div>
        </div>
      </div>

      <section className="section" id="activities">
        <div className="container">
          <SectionHeading eyebrow="The original Level X activity catalogue" title="Pick your arena" copy="Explore the four activities with published rates in the original Laravel catalogue. Each activity has its own details, facilities and WhatsApp enquiry." />
          <div className="activity-grid">
            {activities.map((activity, index) => (
              <article className="activity-card" key={activity.slug}>
                <div className="activity-image-wrap">
                  <Image src={activity.image} alt={`${activity.name} gaming experience at Level X Gaming Arena`} width={600} height={380} />
                  <span className="activity-number">0{index + 1}</span>
                </div>
                <div className="activity-body">
                  <span className="tag">{activity.category}</span>
                  <h3>{activity.name}</h3>
                  <p>{activity.description}</p>
                  <div className="price-line">
                    <strong>Rs. {activity.offer.toLocaleString("en-PK")}</strong>
                    <del>Rs. {activity.price.toLocaleString("en-PK")}</del>
                    <span className="muted">/ {activity.unit}</span>
                  </div>
                  <Link className="text-link" href={`/activities/${activity.slug}/`}>Explore & book <ArrowRight size={15} /></Link>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section alt more-experiences">
        <div className="container">
          <SectionHeading eyebrow="More ways to play" title="Additional arena facilities" copy="The original Laravel About page also references pool and private VIP rooms. Their live rates are not published in the activity price list, so contact the team to confirm current availability." />
          <div className="extra-service-grid">
            {additionalExperiences.map((item) => (
              <article className="extra-service-card" key={item.name}>
                <div className="extra-service-art"><Image src={item.image} alt={item.name + " facility at Level X Gaming Arena"} width={100} height={100} /></div>
                <div className="extra-service-copy">
                  <span className="tag">{item.category}</span>
                  <h3>{item.name}</h3>
                  <p>{item.description}</p>
                  <a className="text-link" href={whatsappLink(`Hi Level X, please share current details, availability and rates for ${item.name}.`)} target="_blank" rel="noopener noreferrer">Ask about availability <ArrowRight size={15} /></a>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section alt" id="packages">
        <div className="container">
          <SectionHeading eyebrow="Clear & simple pricing" title="Packages and hourly rates" copy="These are the published rates from the original Laravel activity catalogue, including Monday Madness and the PC Mid Day package. Confirm the applicable rate with our team before visiting." />
          <div className="package-grid">
            {hourlyPackages.map((item) => (
              <article className="price-card" key={item.name}>
                <div className="package-card-image">
                  <Image src={item.image} alt={item.name + " gaming package at Level X Gaming Arena"} width={560} height={320} />
                  <span className="package-image-label">PUBLISHED RATE</span>
                </div>
                <h3>{item.name}</h3>
                <div className="price">Rs. {item.offer.toLocaleString("en-PK")}<small> / {item.unit}</small></div>
                <p>{item.note}</p>
                <div className="deal"><span>Regular rate</span><b>Rs. {item.regular.toLocaleString("en-PK")}</b></div>
                {item.monday && <div className="deal"><span>Monday Madness</span><b>Rs. {item.monday.toLocaleString("en-PK")}</b></div>}
                {("midday" in item) && <div className="deal"><span>Mid Day PC</span><b>Rs. {item.midday}</b></div>}
                <a className="text-link" href={whatsappLink(`Hi Level X, please confirm the ${item.name} rates and availability.`)} target="_blank" rel="noopener noreferrer">Ask about this rate <ArrowRight size={14} /></a>
              </article>
            ))}
          </div>
          <div className="account-package-panel">
            <div className="account-panel-heading">
              <div className="section-heading account-heading">
                <span className="eyebrow">For regular players</span>
                <h2>Account packages</h2>
                <p>Prepaid play-hour bundles for players who visit regularly. Account packages are activated at the counter.</p>
              </div>
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
            <p className="account-fine-print">Account opening: Rs. 1,000 one-time. Play-hour bundles are prepaid and managed at the arena counter.</p>
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
          <SectionHeading eyebrow="The Level X experience" title="A better place to play" copy="The original public site describes a venue built around multiple play zones, well-maintained equipment and a straightforward visit." />
          <div className="feature-grid">
            <div className="info-card"><Gamepad2 size={25} /><h3>Premium setups</h3><p>Enjoy dedicated gaming PCs, PlayStation 5 and well-equipped table game spaces.</p></div>
            <div className="info-card"><ShieldCheck size={25} /><h3>Clear pricing</h3><p>See the published standard rates and selected offers before contacting the team.</p></div>
            <div className="info-card"><Users size={25} /><h3>Bring your crew</h3><p>Choose a solo session or get your friends together for a competitive match.</p></div>
            <div className="info-card"><Clock3 size={25} /><h3>Open 24/7</h3><p>Plan your visit any time. The original business listing states the arena is open around the clock.</p></div>
            <div className="info-card"><Trophy size={25} /><h3>Different ways to play</h3><p>Switch between PC gaming, console gaming, cue sports and table tennis in one venue.</p></div>
            <div className="info-card"><Sparkles size={25} /><h3>Easy enquiries</h3><p>No online account or payment is needed. Talk directly with the team on WhatsApp.</p></div>
          </div>
        </div>
      </section>

      <section className="section alt" id="gallery">
        <div className="container">
          <SectionHeading eyebrow="Inside the arena" title="The gaming atmosphere" copy="Explore the visual spaces and gaming experiences represented in the original Level X image library." />
          <div className="gallery-grid">
            {gallery.map((item, index) => (
              <div className={`gallery-card gallery-card-${index + 1}`} key={item.src}>
                <Image src={item.src} alt={`${item.title} at Level X Gaming Arena`} width={500} height={350} />
                <span>{item.title}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container faq-layout">
          <div className="faq-intro">
            <span className="eyebrow">Good to know</span>
            <h2>Questions before your visit?</h2>
            <p>Here are the practical details about booking, payment, opening hours and the available activities.</p>
            <WhatsAppButton message="Hi Level X, I have a question before visiting. Could you please help?" label="Ask our team" />
          </div>
          <div className="faq-list">
            {faqs.map((item, index) => <article className="faq-item" key={item.q}><span className="faq-index">0{index + 1}</span><div><h3>{item.q}</h3><p>{item.a}</p></div></article>)}
          </div>
        </div>
      </section>

      <section className="section final-cta-section">
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
