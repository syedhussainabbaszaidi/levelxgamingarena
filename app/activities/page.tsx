import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowRight, ArrowUpRight, Clock3 } from "lucide-react";
import { activities, additionalExperiences, whatsappLink } from "../data";
import { PageIntro, WhatsAppButton } from "../components";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Gaming Activities & Facilities in Karachi",
  description: "Explore PC gaming, PlayStation 5, premium snooker, private table tennis, pool and VIP room enquiries at Level X Gaming Arena Karachi.",
  path: "/activities/",
  keywords: ["gaming activities Karachi", "PC gaming Karachi", "PS5 Karachi", "snooker Karachi", "table tennis Karachi", "gaming arena facilities"],
});

export default function ActivitiesPage() {
  return <>
    <section className="activities-editorial-hero"><div className="container activities-editorial-grid"><div className="activities-editorial-copy"><span className="eyebrow">THE ARENA / KARACHI</span><p className="activities-hero-kicker">EXPLORE THE LEVEL X LINE-UP</p><h1>Find your way to play.<br/><em>Make it your game.</em></h1><p className="activities-hero-description">From competitive PC sessions to PlayStation matches and classic table games, explore the experiences available at Level X. Pick your session and ask our team to confirm the latest availability.</p><div className="activities-hero-actions"><WhatsAppButton message="Hi Level X, please share today's activity availability and current rates." label="Ask about availability"/><Link className="button button-outline" href="/packages/">View packages <ArrowRight size={16}/></Link></div><div className="activities-hero-meta"><span><Clock3 size={15}/> OPEN 24 HOURS</span><span>KARACHI · PAKISTAN</span></div></div><div className="activities-editorial-art"><Image src="/img/site/arena.webp" alt="Gaming area at Level X Gaming Arena" width={1000} height={760} priority/><div className="activities-art-outline"></div><span className="activities-art-caption">LEVEL X / THE LINE-UP</span><div className="activities-art-card"><strong>GAME.<br/><em>COMPETE.</em><br/>CONNECT.</strong><span>YOUR NEXT SESSION STARTS HERE</span></div></div></div></section>
    <section className="activities-directory"><div className="container"><div className="activities-directory-heading"><span className="eyebrow">01 / THE MAIN EXPERIENCES</span><h2>Pick your <em>next session.</em></h2><p>Published rates are shown where available. Contact the arena for current offers and session availability.</p></div><div className="activities-directory-grid">
      {activities.map((item,index)=><article className="activities-directory-card" key={item.slug}><Link href={`/activities/${item.slug}/`} className="activities-directory-image"><Image src={item.image} alt={item.name+" at Level X Gaming Arena"} width={760} height={520}/><span>0{index+1}</span><ArrowUpRight size={20}/></Link><div><small>{item.category}</small><h3>{item.name}</h3><p>{item.description}</p><div className="activities-directory-price"><strong>PKR {item.offer.toLocaleString("en-PK")}</strong><span>/ {item.unit}</span></div><Link href={`/activities/${item.slug}/`}>{`Explore ${item.name}`} <ArrowRight size={15}/></Link></div></article>)}
    </div></div></section>
    <section className="activities-additional"><div className="container"><div className="activities-directory-heading"><span className="eyebrow">02 / ASK THE ARENA</span><h2>More ways to <em>spend time.</em></h2><p>These facilities are listed in the existing business content, but current rates and availability need confirmation.</p></div><div className="activities-additional-grid">{additionalExperiences.map((item,index)=><article key={item.name}><Image src={item.image} alt={item.name+" facility"} width={520} height={360}/><div><small>0{index+1} / ENQUIRY</small><h3>{item.name}</h3><p>{item.description}</p><a href={whatsappLink(`Hi Level X, please confirm current availability and rates for ${item.name}.`)} target="_blank" rel="noopener noreferrer">Ask about this facility <ArrowUpRight size={15}/></a></div></article>)}</div></div></section>
    <section className="activities-final-cta"><div className="container"><span className="eyebrow">YOUR NEXT SESSION STARTS HERE</span><h2>Got a game in mind?</h2><p>Send your preferred activity, date, time and group size. The team will confirm the details with you.</p><WhatsAppButton message="Hi Level X, I would like to plan a gaming session. Please help me with availability and current rates."/></div></section>
  </>;
}
