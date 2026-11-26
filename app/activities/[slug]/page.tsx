import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, ArrowRight, ArrowUpRight, CheckCircle2, Clock3, Users, Zap } from "lucide-react";
import { activities } from "../../data";
import { WhatsAppButton } from "../../components";
import { pageMetadata } from "../../seo";

export function generateStaticParams() {
  return activities.map((activity) => ({ slug: activity.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const activity = activities.find((item) => item.slug === slug);
  if (!activity) return pageMetadata({
    title: "Gaming activity",
    description: "Explore gaming experiences at Level X Gaming Arena in Karachi.",
    path: "/activities/",
    keywords: ["gaming activities Karachi"],
  });
  return pageMetadata({
    title: `${activity.name} in Karachi`,
    description: `${activity.description} Explore rates and enquire about ${activity.name} sessions at Level X Gaming Arena in Karachi.`,
    path: `/activities/${activity.slug}/`,
    keywords: [activity.name, `${activity.name} Karachi`, "gaming arena Karachi", "Level X Gaming Arena"],
  });
}

export default async function ActivityPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const activity = activities.find((item) => item.slug === slug);
  if (!activity) notFound();

  const unitLabel = activity.unit === "minute" ? "per minute" : "per hour";
  const activityNumber = String(activities.findIndex((item) => item.slug === activity.slug) + 1).padStart(2, "0");
  const nextActivity = activities[(activities.findIndex((item) => item.slug === activity.slug) + 1) % activities.length];

  return (
    <>
      <section className="activity-detail-hero">
        <div className="activity-detail-hero-image">
          <Image src={activity.image} alt={`${activity.name} at Level X Gaming Arena`} fill priority sizes="100vw" />
        </div>
        <div className="activity-detail-hero-shade" />
        <div className="container activity-detail-hero-content">
          <Link href="/activities/" className="activity-back-link"><ArrowLeft size={15} /> All activities</Link>
          <div className="activity-hero-copy">
            <span className="activity-index">LEVEL X / EXPERIENCE {activityNumber}</span>
            <h1>{activity.name}</h1>
            <p>{activity.description}</p>
            <div className="activity-hero-actions">
              <WhatsAppButton message={`Hi Level X, I want to enquire about booking ${activity.name}. Preferred date: ____. Preferred time: ____. Number of players: ____. Please confirm availability and final price.`} label="Enquire on WhatsApp" />
              <Link className="button button-outline" href="/packages/">View all rates <ArrowUpRight size={15} /></Link>
            </div>
          </div>
          <div className="activity-hero-price">
            <span className="activity-price-kicker"><Zap size={14} /> CURRENT RATE</span>
            <strong>PKR {activity.offer.toLocaleString("en-PK")}</strong>
            <span className="activity-price-unit">/ {unitLabel}</span>
            <span className="activity-price-rule">Confirm today's offer with our team</span>
          </div>
          <div className="activity-hero-bottom"><span>GAME / COMPETE / CONNECT</span><span>{activityNumber} — 0{activities.length}</span></div>
        </div>
      </section>

      <section className="activity-detail-content">
        <div className="container">
          <div className="activity-detail-intro">
            <div><span className="eyebrow">THE EXPERIENCE</span><h2>Make every session <em>count.</em></h2></div>
            <p>Everything you need to know before you play. Choose your session, bring your crew and contact the arena team to confirm availability.</p>
          </div>
          <div className="activity-detail-layout">
            <div className="activity-detail-visual">
              <Image src={activity.image} alt={`${activity.name} gaming setup`} width={1000} height={760} sizes="(max-width: 760px) 100vw, 58vw" />
              <div className="activity-visual-caption"><span>LEVEL X EXPERIENCE</span><strong>{activity.name}</strong></div>
              <span className="activity-visual-number">{activityNumber}</span>
            </div>
            <div className="activity-detail-panel">
              <span className="tag">{activity.category}</span>
              <h3>Built around your game.</h3>
              <p>{activity.description}</p>
              <div className="activity-detail-facts">
                <div><Users size={18}/><span>PLAYER CAPACITY<strong>{activity.capacity}</strong></span></div>
                <div><Clock3 size={18}/><span>OPENING HOURS<strong>24 hours · Every day</strong></span></div>
              </div>
              <div className="activity-detail-rate">
                <div><span>Current offer</span><strong>PKR {activity.offer.toLocaleString("en-PK")} <small>/ {unitLabel}</small></strong></div>
                </div>
              <WhatsAppButton message={`Hi Level X, I want to enquire about booking ${activity.name}. Preferred date: ____. Preferred time: ____. Number of players: ____. Please confirm availability and final price.`} label="Check availability" />
              <small className="activity-detail-disclaimer">Rates and offers are subject to confirmation by the arena team.</small>
            </div>
          </div>
        </div>
      </section>

      <section className="activity-features-section">
        <div className="container">
          <div className="activity-features-heading"><span className="eyebrow">SESSION DETAILS / 01</span><h2>What’s included <em>in your session.</em></h2><p>Key details and features for {activity.name.toLowerCase()}.</p></div>
          <div className="activity-feature-grid">
            {activity.features.map((feature, index) => <article className="activity-feature-card" key={feature}><span>0{index + 1}</span><CheckCircle2 size={20}/><p>{feature}</p></article>)}
          </div>
        </div>
      </section>

      <section className="activity-next-section">
        <div className="container activity-next-inner">
          <div><span className="eyebrow">KEEP THE GAME GOING</span><h2>Explore another <em>experience.</em></h2><p>There’s more to play at Level X. Discover the next session.</p></div>
          <Link href={`/activities/${nextActivity.slug}/`} className="activity-next-card"><span>NEXT EXPERIENCE</span><strong>{nextActivity.name}</strong><span>Explore activity <ArrowRight size={16}/></span></Link>
        </div>
      </section>
    </>
  );
}
