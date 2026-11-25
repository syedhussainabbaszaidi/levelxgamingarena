import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowRight, ArrowUpRight, CheckCircle2, ChevronDown, Clock3, Gamepad2, Headphones, MessageCircle, ShieldCheck, Trophy, Users } from "lucide-react";
import { activities, additionalExperiences, accountPackages, faqs, gallery, hourlyPackages, whatsappLink } from "./data";
import { SectionHeading, WhatsAppButton } from "./components";
import { pageMetadata } from "./seo";

export const metadata: Metadata = pageMetadata({
  title: "Gaming Arena in Karachi – PC, PS5 & Snooker | Level X",
  description: "Experience PC gaming, PS5, premium snooker, private table tennis, pool and VIP rooms at Level X Gaming Arena in Karachi. Explore rates and book on WhatsApp.",
  path: "/",
  keywords: ["gaming arena Karachi", "PC gaming Karachi", "PS5 gaming Karachi", "snooker club Karachi", "table tennis Karachi", "pool table Karachi", "VIP gaming rooms Karachi"],
});

const featuredGames = ["Counter-Strike 2", "PUBG", "VALORANT", "DOTA"];

export default function HomePage() {
  return (
    <>
      <section className="hero flagship-hero">
        <div className="hero-grid container">
          <div className="hero-content">
            <span className="eyebrow"><span className="live-dot" /> KARACHI · OPEN 24 HOURS</span>
            <p className="hero-kicker">THE NEXT LEVEL OF PLAY</p>
            <h1>Enter the arena.<em>Own the moment.</em></h1>
            <p className="hero-copy">From high-energy PC and PS5 sessions to private table games, Level X brings your favourite ways to play together under one roof.</p>
            <div className="hero-actions">
              <WhatsAppButton message="Hi Level X, I would like to book a gaming session. Please share availability and current rates." label="Book your session" />
              <a className="button button-outline" href="#activities">Explore the arena <ArrowRight size={16} /></a>
            </div>
            <div className="hero-proof"><span><CheckCircle2 size={15} /> Open 24/7</span><span><CheckCircle2 size={15} /> Six activities</span><span><MessageCircle size={15} /> Direct WhatsApp booking</span></div>
          </div>
          <div className="hero-art">
            <div className="hero-image-frame"><Image src="/img/gallery/esports-arena.jpg" alt="Gaming stations inside Level X Gaming Arena" width={1100} height={820} priority /><div className="hero-image-shade" /><div className="hero-image-caption"><span>LEVEL X / 001</span><strong>Find your game.</strong></div></div>
            <div className="hero-floating-card"><span className="live-dot" /><div><b>Always game-ready</b><small>Open every hour, every day</small></div><Clock3 size={20} /></div>
            <span className="hero-vertical-label">GAME · COMPETE · CONNECT</span>
          </div>
        </div>
        <div className="hero-bottom-line container"><span>01 / THE ARENA</span><span>SCROLL TO EXPLORE <i className="scroll-line" /></span></div>
      </section>

      <section className="quick-highlights"><div className="container quick-highlights-grid">
        <div><span className="highlight-icon"><Clock3 size={19} /></span><b>24/7</b><small>Open all day</small></div>
        <div><span className="highlight-icon"><Gamepad2 size={19} /></span><b>PC + PS5</b><small>Console & PC gaming</small></div>
        <div><span className="highlight-icon"><Users size={19} /></span><b>Private spaces</b><small>Room-based experiences</small></div>
        <div><span className="highlight-icon"><MessageCircle size={19} /></span><b>Easy booking</b><small>Talk to our team</small></div>
      </div></section>

      <section className="section flagship-activities" id="activities"><div className="container">
        <div className="section-heading-row"><SectionHeading eyebrow="Choose your game" title="Six ways to play." copy="Whether you are here for a competitive session or a relaxed game with friends, find your game at Level X." /><span className="section-index">02 <i>/ 06</i></span></div>
        <div className="activity-grid flagship-activity-grid">
          {activities.map((activity, index) => <article className="activity-card flagship-activity-card" key={activity.slug}>
            <div className="activity-image-wrap"><Image src={activity.image} alt={activity.name + " at Level X Gaming Arena"} width={720} height={520} /><span className="activity-number">0{index + 1}</span><span className="activity-image-action"><ArrowUpRight size={19} /></span></div>
            <div className="activity-body"><span className="tag">{activity.category} · Available</span><h3>{activity.name}</h3><p>{activity.description}</p>
              <div className="activity-capacity"><span>{activity.capacity}</span><span>{activity.unit === "minute" ? "Per-minute billing" : "Hourly session"}</span></div>
              <div className="price-line"><small className="price-current-label">Current price</small><strong>PKR {activity.offer.toLocaleString("en-PK")}</strong><del>PKR {activity.price.toLocaleString("en-PK")}</del><span className="muted">/ {activity.unit === "minute" ? "min" : "hr"}</span></div>
              <Link className="text-link" href={`/activities/${activity.slug}/`}>{`Explore ${activity.name}`} <ArrowRight size={15} /></Link>
            </div>
          </article>)}
          {additionalExperiences.map((item, index) => <article className="activity-card flagship-activity-card enquiry-card" key={item.name}>
            <div className="activity-image-wrap"><Image src={item.image} alt={item.name + " facility at Level X Gaming Arena"} width={720} height={520} /><span className="activity-number">0{activities.length + index + 1}</span><span className="activity-image-action"><ArrowUpRight size={19} /></span></div>
            <div className="activity-body"><span className="tag">{item.category} · Limited availability</span><h3>{item.name}</h3><p>{item.description}</p>
              <div className="price-line enquiry-price"><strong>Enquire for rates</strong><span className="muted">Availability on request</span></div>
              <a className="text-link" href={whatsappLink(`Hi Level X, please share current details, availability and rates for ${item.name}.`)} target="_blank" rel="noopener noreferrer">Ask about this experience <ArrowUpRight size={15} /></a>
            </div>
          </article>)}
        </div>
      </div></section>

      <section className="games-section"><div className="container games-layout">
        <div className="games-copy"><span className="eyebrow">PC GAMING / THE LINE-UP</span><h2>Queue up.<br /><em>Lock in.</em></h2><p>Get into the action with popular competitive titles listed for the PC gaming experience at Level X.</p><Link className="text-link" href="/activities/pc-games/">Explore PC gaming <ArrowRight size={15} /></Link></div>
        <div className="game-title-grid">{featuredGames.map((game, index) => <div className="game-title" key={game}><span>0{index + 1}</span><b>{game}</b><ArrowUpRight size={17} /></div>)}</div>
      </div></section>

      <section className="section pricing-section" id="packages"><div className="container">
        <div className="section-heading-row"><SectionHeading eyebrow="Straightforward rates" title="Make every hour count." copy="Compare the published regular rates and offers. Time windows and minimum session durations apply to selected promotions; confirm the final rate with our team." /><span className="section-index">03 <i>/ PRICING</i></span></div>
        <div className="package-grid flagship-package-grid">{hourlyPackages.map((item, index) => <article className="price-card flagship-price-card" key={item.name}>
          <div className="package-card-image"><Image src={item.image} alt={item.name + " at Level X"} width={620} height={360} /><span className="package-image-label">0{index + 1} / PUBLISHED RATE</span></div>
          <div className="price-card-content"><span className="tag">{item.name}</span><h3>{item.name === "Private Table Tennis" ? "Private Table Tennis Room" : item.name}</h3>
            <div className="price">{item.name === "Premium Snooker" ? <>PKR 12<small> / minute</small></> : <>PKR {item.offer.toLocaleString("en-PK")}<small> / {item.unit}</small></>}</div><p>{item.note}</p>
            <div className="deal"><span>Regular rate</span><b>{item.name === "Premium Snooker" ? "PKR 15 / minute" : `PKR ${item.regular.toLocaleString("en-PK")}`}</b></div>
            {item.monday && <div className="deal"><span>Monday Madness</span><b>{item.name === "Premium Snooker" ? "PKR 8 / minute" : `PKR ${item.monday.toLocaleString("en-PK")}`}</b></div>}
            {("midday" in item) && <div className="deal"><span>PC Mid Day · 9 AM–5 PM</span><b>PKR {item.midday} / hour</b></div>}
            {item.name === "PS5" && <div className="deal"><span>Extra controller</span><b>PKR 300 / hour</b></div>}
            <a className="text-link" href={whatsappLink(`Hi Level X, please confirm the current ${item.name} rates and availability.`)} target="_blank" rel="noopener noreferrer">{`Confirm ${item.name} rate`} <ArrowUpRight size={14} /></a>
          </div>
        </article>)}</div>
        <div className="account-package-panel flagship-account-panel">
          <div className="account-panel-heading"><div className="section-heading account-heading"><span className="eyebrow">For regular players</span><h2>Top up your play time.</h2><p>Prepaid hour bundles for players who visit regularly. Accounts are activated at the arena counter.</p></div><span className="account-panel-mark">LX / PASS</span></div>
          <div className="account-grid">{accountPackages.map((item, index) => <article className="account-card" key={item.name}><span>0{index + 1} / {item.hours}</span><strong>PKR {item.price.toLocaleString("en-PK")}</strong><b>{item.name}</b><p className="muted">{item.description}</p><a className="text-link" href={whatsappLink(`Hi Level X, I am interested in the ${item.name} account package.`)} target="_blank" rel="noopener noreferrer">Enquire <ArrowUpRight size={14} /></a></article>)}</div>
          <p className="account-fine-print">Account opening: PKR 1,000 one-time. No online payment is collected through this website.</p>
        </div>
        <div className="section-link"><Link className="button button-outline" href="/packages/">View all packages <ArrowRight size={16} /></Link></div>
      </div></section>

      <section className="experience-banner flagship-experience"><div className="container experience-banner-inner">
        <div className="experience-banner-copy"><span className="eyebrow">Bring your crew. Pick your game.</span><h2>Make it a night<br /><em>to remember.</em></h2><p>Bring your crew, choose your experience and let our team help organise your next visit.</p><WhatsAppButton message="Hi Level X, I am planning a visit. Please share activity availability and current offers." label="Plan your visit" /></div>
        <div className="experience-banner-image"><Image src="/img/gallery/gaming-setup.jpg" alt="Gaming setup at Level X Gaming Arena" width={1000} height={720} /><span>YOUR NEXT GAME AWAITS</span></div>
      </div></section>

      <section className="section experience-details"><div className="container">
        <SectionHeading eyebrow="Inside the gaming zone" title="Made for players." copy="A welcoming place to play, compete and spend time together, with different games and activities to suit your mood." />
        <div className="feature-grid">
          <article className="info-card"><Gamepad2 size={25} /><h3>PC & console play</h3><p>Choose from dedicated PC gaming and PlayStation 5 sessions.</p></article>
          <article className="info-card"><ShieldCheck size={25} /><h3>Clear published rates</h3><p>Review standard prices and selected offers before you enquire.</p></article>
          <article className="info-card"><Users size={25} /><h3>Bring your crew</h3><p>Enjoy a session with friends or make it a friendly competition.</p></article>
          <article className="info-card"><Clock3 size={25} /><h3>Open 24/7</h3><p>Plan your visit any time, day or night.</p></article>
          <article className="info-card"><Trophy size={25} /><h3>More ways to play</h3><p>Choose PC gaming, PS5, snooker, table tennis or ask about pool.</p></article>
          <article className="info-card"><Headphones size={25} /><h3>Easy enquiries</h3><p>Talk directly with the arena team through WhatsApp.</p></article>
        </div>
      </div></section>


      <section className="food-preview-section">
        <div className="container food-preview-grid">
          <div className="food-preview-art"><Image src="/img/gallery/arcade.jpg" alt="The Level X gaming lounge" width={900} height={650}/><span>TAKE A BREAK / STAY IN THE GAME</span></div>
          <div className="food-preview-copy"><span className="eyebrow">MORE THAN GAME TIME</span><h2>Good games.<br/><em>Good company.</em></h2><p>Make room for a refreshment between sessions. Ask the Level X team about the current food, drinks and combo selection before your visit.</p><Link className="button button-outline" href="/food-drinks/">Explore food & drinks <ArrowRight size={16}/></Link><small>Menu items, availability and prices are confirmed directly by the arena.</small></div>
        </div>
      </section>

      <section className="section alt gallery-section" id="gallery"><div className="container">
        <div className="section-heading-row"><SectionHeading eyebrow="Inside Level X" title="A look around the arena." copy="Take a look at the gaming setups and spaces at Level X." /><span className="section-index">04 <i>/ GALLERY</i></span></div>
        <div className="gallery-grid flagship-gallery-grid">{gallery.map((item, index) => <div className={`gallery-card gallery-card-${index + 1}`} key={item.src}><Image src={item.src} alt={item.title + " at Level X Gaming Arena"} width={700} height={480} /><span>{item.title}</span></div>)}</div>
      </div></section>

      <section className="section faq-section"><div className="container faq-layout">
        <div className="faq-intro"><span className="eyebrow">Before you visit</span><h2>Good to know.<br /><em>Ready to play.</em></h2><p>Quick answers about booking, payment, opening hours and the activities available at Level X.</p><WhatsAppButton message="Hi Level X, I have a question before visiting. Could you please help?" label="Ask our team" /></div>
        <div className="faq-list">{faqs.map((item, index) => <details className="faq-item" key={item.q}><summary><span className="faq-index">0{index + 1}</span><h3>{item.q}</h3><ChevronDown size={18} /></summary><p>{item.a}</p></details>)}</div>
      </div></section>

      <section className="section final-cta-section"><div className="container"><div className="cta-band flagship-cta"><span className="eyebrow">THE ARENA IS READY</span><h2>Your next level starts here.</h2><p>Tell us what you want to play, when you want to visit and how many people are joining.</p><WhatsAppButton message="Hi Level X, I would like to book a session. Please help me with availability and pricing." label="Start a WhatsApp enquiry" /></div></div></section>
    </>
  );
}
