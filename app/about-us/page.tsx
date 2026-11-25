import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowRight, ArrowUpRight, Clock3, Gamepad2, Monitor, Trophy, Users, Wallet } from "lucide-react";
import { WhatsAppButton, SectionHeading } from "../components";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "About Level X Gaming Arena in Karachi",
  description: "Discover Level X Gaming Arena in Karachi — PC gaming, PlayStation 5, premium snooker, table tennis, pool and private gaming rooms, open 24/7.",
  path: "/about-us/",
  keywords: ["about Level X Gaming Arena", "gaming zone Karachi", "gaming arena Karachi", "PC gaming Karachi", "PS5 Karachi"],
});

const arenaFeatures = [
  { icon: Monitor, title: "PC gaming stations", copy: "Get into competitive favourites including CS2, PUBG, VALORANT and DOTA." },
  { icon: Gamepad2, title: "PlayStation 5", copy: "Pick your game, grab a controller and enjoy a console session with friends." },
  { icon: Trophy, title: "Cue sports & table games", copy: "Take a break from the screen with premium snooker, pool and private table tennis." },
  { icon: Users, title: "Private spaces", copy: "Ask our team about VIP private rooms and options for your group." },
];

export default function About() {
  return (
    <>
      <section className="page-hero about-hero">
        <div className="container about-hero-grid">
          <div className="page-intro" data-reveal="intro">
            <span className="eyebrow">Inside Level X · Karachi</span>
            <h1>A gaming zone built for <em>every kind of player.</em></h1>
            <p>Level X brings PC gaming, PlayStation 5 and classic table games together in one place. Come for a competitive match, a casual session with friends or a night of games — we’re open around the clock.</p>
            <div className="about-hero-actions">
              <WhatsAppButton message="Hi Level X, I would like to plan a visit. Please share availability and current rates." label="Plan your visit" />
              <Link className="button button-outline" href="/#activities">Explore activities <ArrowRight size={16} /></Link>
            </div>
            <div className="about-quick-facts">
              <span><Clock3 size={16} /> Open 24/7</span>
              <span><Gamepad2 size={16} /> PC · PS5 · Table games</span>
            </div>
          </div>
          <div className="about-visual" data-reveal="about-visual">
            <Image src="/img/gallery/esports-arena.jpg" alt="Gaming arena floor at Level X Gaming Arena" width={1100} height={850} priority />
            <div className="about-visual-label"><span>LEVEL X / KARACHI</span><strong>Pick your game.<br />Make your move.</strong></div>
            <span className="about-visual-index">01 — THE ARENA</span>
          </div>
        </div>
      </section>

      <section className="section about-intro-section">
        <div className="container about-intro-grid">
          <div><span className="eyebrow">What we’re about</span><h2>One place.<br /><em>So many ways to play.</em></h2></div>
          <div className="about-intro-copy"><p>Not every visit has to look the same. Lock in at a PC station, challenge a friend on PS5, line up a shot at the snooker table or book a private table tennis room. Level X is a gaming zone where you can choose how you want to spend your time.</p><p>We keep booking simple: check the activities and published rates, then message our team on WhatsApp to confirm availability before you visit.</p><Link className="text-link" href="/packages/">View activities & rates <ArrowRight size={15} /></Link></div>
        </div>
      </section>

      <section className="section about-activities-section">
        <div className="container">
          <SectionHeading eyebrow="The line-up" title="Find your way to play." copy="From high-performance PC sessions to table games away from the screen, there’s something to bring your crew together." />
          <div className="feature-grid about-feature-grid">
            {arenaFeatures.map(({ icon: Icon, title, copy }, index) => <article className="info-card about-feature-card" key={title}><span className="about-feature-number">0{index + 1}</span><span className="about-feature-icon"><Icon size={23} /></span><h3>{title}</h3><p>{copy}</p></article>)}
          </div>
          <div className="about-extra-line"><span>ALSO AT LEVEL X</span><strong>Pool table <i>·</i> VIP private rooms</strong><a href="https://wa.me/923422631057?text=Hi%20Level%20X%2C%20please%20share%20availability%20and%20rates%20for%20pool%20and%20VIP%20private%20rooms." target="_blank" rel="noopener noreferrer">Ask for availability <ArrowUpRight size={14} /></a></div>
        </div>
      </section>

      <section className="about-availability">
        <div className="container about-availability-inner">
          <div className="about-availability-mark"><Clock3 size={25} /><span>24 / 7</span></div>
          <div><span className="eyebrow">Whenever you’re ready</span><h2>Open all day.<br /><em>Ready for your next game.</em></h2><p>Level X Gaming Arena is open 24 hours a day, seven days a week. Message us before your visit to check activity availability and current offers.</p></div>
          <div className="about-availability-action"><WhatsAppButton message="Hi Level X, I am planning to visit. Please confirm availability and current rates." label="Message the arena" /></div>
        </div>
      </section>
    </>
  );
}
