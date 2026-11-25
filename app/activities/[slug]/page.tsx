import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { CheckCircle2, Clock3, Users } from "lucide-react";
import { activities } from "../../data";
import { PageIntro, WhatsAppButton } from "../../components";
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

  return (
    <>
      <section className="page-hero">
        <div className="container">
          <PageIntro eyebrow={activity.category} title={activity.name} copy={activity.description} />
        </div>
      </section>
      <section className="content-section">
        <div className="container activity-detail">
          <Image src={activity.image} alt={`${activity.name} gaming area at Level X Gaming Arena`} width={900} height={650} />
          <div>
            <span className="tag">{activity.category}</span>
            <h2>{activity.name}</h2>
            <p>{activity.description}</p>
            <div className="detail-price">
              <span className="muted">Opening offer</span>
              <div><strong>Rs. {activity.offer.toLocaleString("en-PK")}</strong><span className="muted"> / hour</span></div>
              <del className="muted">Regular: Rs. {activity.price.toLocaleString("en-PK")}</del>
            </div>
            <ul className="feature-list">
              {activity.features.map((feature) => <li key={feature}><CheckCircle2 size={17} />{feature}</li>)}
              <li><Users size={17} />Capacity: {activity.capacity}</li>
              <li><Clock3 size={17} />Open 24 hours, every day</li>
            </ul>
            <WhatsAppButton message={`Hi Level X, I want to enquire about booking ${activity.name}. Preferred date: ____. Preferred time: ____. Number of players: ____. Please confirm availability and final price.`} />
            <p className="muted detail-note">No account or online payment required. Booking enquiries are handled directly by our team on WhatsApp.</p>
          </div>
        </div>
      </section>
    </>
  );
}
