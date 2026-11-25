import type { Metadata } from "next";
import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { PageIntro, WhatsAppButton } from "../components";
import { accountPackages, hourlyPackages, whatsappLink } from "../data";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Gaming Packages, Hourly Rates & Offers",
  description: "Compare PC gaming, PS5, premium snooker and table tennis hourly rates, Monday Madness offers and prepaid gaming packages at Level X Karachi.",
  path: "/packages/",
  keywords: ["gaming packages Karachi", "PC gaming rates Karachi", "PS5 hourly price Karachi", "snooker rates Karachi", "table tennis price Karachi"],
});

export default function Packages() {
  return (
    <>
      <section className="page-hero"><div className="container"><PageIntro eyebrow="Packages & pricing" title="Play more. Choose your package." copy="Browse our published activity rates, time-based promotions and prepaid account bundles. Contact our team to confirm availability and the applicable offer." /></div></section>
      <section className="content-section">
        <div className="container">
          <div className="package-grid">
            {hourlyPackages.map((item) => (
              <article className="price-card" key={item.name}>
                <Image src={item.image} alt={`${item.name} gaming package at Level X Gaming Arena`} width={500} height={300} className="package-image" />
                <h2>{item.name}</h2>
                <div className="price">Rs. {item.offer.toLocaleString("en-PK")}<small> / {item.unit}</small></div>
                <p>{item.note}</p>
                <div className="deal"><span>Regular rate</span><b>Rs. {item.regular.toLocaleString("en-PK")}</b></div>
                {item.monday && <div className="deal"><span>Monday Madness</span><b>Rs. {item.monday.toLocaleString("en-PK")}</b></div>}
                {("midday" in item) && <div className="deal"><span>Mid Day PC</span><b>Rs. {item.midday}</b></div>}
                <a className="text-link" href={whatsappLink(`Hi Level X, please confirm the current ${item.name} price and availability.`)} target="_blank" rel="noopener noreferrer">Enquire on WhatsApp <ArrowUpRight size={14} /></a>
              </article>
            ))}
          </div>
          <div className="section-heading account-heading"><span className="eyebrow">Prepaid play time</span><h2>Account packages</h2><p>Prepaid bundles for regular players. Packages are activated at the counter; no online payment is collected.</p></div>
          <div className="account-grid">
            {accountPackages.map((item) => <article className="account-card" key={item.name}><span>{item.hours}</span><strong>Rs. {item.price.toLocaleString("en-PK")}</strong><b>{item.name}</b><p className="muted">{item.description}</p><a className="text-link" href={whatsappLink(`Hi Level X, I am interested in the ${item.name} package.`)} target="_blank" rel="noopener noreferrer">Ask about package <ArrowUpRight size={14} /></a></article>)}
          </div>
          <div className="cta-band packages-cta"><h2>Need help choosing?</h2><p>Message us with your preferred game and we will guide you to the right option.</p><WhatsAppButton message="Hi Level X, please help me choose a gaming package." /></div>
        </div>
      </section>
    </>
  );
}
