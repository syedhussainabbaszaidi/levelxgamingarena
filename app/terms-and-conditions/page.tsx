import type { Metadata } from "next";
import { PageIntro } from "../components";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Terms & Conditions",
  description: "Review the general booking, pricing, payment, conduct and cancellation terms for Level X Gaming Arena.",
  path: "/terms-and-conditions/",
  keywords: ["Level X terms and conditions", "gaming arena booking terms"],
});

export default function Terms() {
  return (
    <>
      <section className="page-hero"><div className="container"><PageIntro eyebrow="Legal information" title="Terms & conditions" copy="Please review these general terms before visiting Level X Gaming Arena." /></div></section>
      <section className="content-section"><div className="container content-grid">
        <article className="legal-card"><h2>Enquiries and reservations</h2><p>Website booking buttons open WhatsApp so you can discuss your preferred activity, date and time with our team. A WhatsApp enquiry is not a confirmed reservation until the team confirms it directly.</p></article>
        <article className="legal-card"><h2>Pricing and payment</h2><p>Published rates and offers are provided as a guide and may be subject to time windows, minimum durations or availability. Confirm the applicable price with the team before your session. Payment is made at the venue counter.</p></article>
        <article className="legal-card"><h2>Arrival and conduct</h2><p>Please arrive at the agreed time and treat staff, other guests and equipment with respect. You may be responsible for damage caused by misuse of equipment.</p></article>
        <article className="legal-card"><h2>Changes and cancellations</h2><p>If your plans change, contact the team as early as possible using the WhatsApp channel used for your enquiry.</p></article>
      </div></section>
    </>
  );
}
