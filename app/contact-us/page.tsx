import type { Metadata } from "next";
import { ArrowUpRight, Clock3, Mail, MapPin, MessageCircle, Phone, Send, Gamepad2, CalendarDays, Users } from "lucide-react";
import { WhatsAppButton } from "../components";
import { emailAddress, phoneDisplay, whatsappLink } from "../data";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Contact Level X Gaming Arena | WhatsApp Bookings",
  description: "Contact Level X Gaming Arena in Karachi for PC gaming, PS5, snooker, table tennis, pool, VIP rooms, rates and session bookings.",
  path: "/contact-us/",
  keywords: ["contact Level X Gaming Arena", "gaming zone Karachi contact", "book gaming Karachi", "WhatsApp gaming booking Karachi"],
});

const bookingSteps = [
  { icon: Gamepad2, title: "Choose your game", copy: "PC gaming, PS5, snooker, table tennis or ask about pool and VIP rooms." },
  { icon: CalendarDays, title: "Share your timing", copy: "Tell us your preferred date and time so the team can check availability." },
  { icon: Users, title: "Confirm your group", copy: "Let us know how many players are joining and we’ll guide you on the rates." },
];

export default function Contact() {
  return (
    <>
      <section className="page-hero contact-hero">
        <div className="container contact-hero-grid">
          <div className="page-intro" data-reveal="intro">
            <span className="eyebrow">Contact · Book · Play</span>
            <h1>Your next game is just <em>a message away.</em></h1>
            <p>Want to book a PC or PS5 session, check snooker availability or ask about group play? Talk directly to the Level X team.</p>
            <div className="contact-hero-actions">
              <WhatsAppButton message="Hi Level X, I would like to check availability and book a session. Please guide me." label="Chat on WhatsApp" />
              <a className="contact-call-link" href="tel:+923422631057"><Phone size={16} /> {phoneDisplay}</a>
            </div>
            <div className="contact-open-note"><span className="live-dot" /> Open 24 hours · Every day</div>
          </div>
          <div className="contact-hero-visual" data-reveal="contact-visual">
            <div className="contact-visual-image" />
            <div className="contact-visual-stamp"><MessageCircle size={22} /><span>LET’S<br />GET YOU<br />PLAYING</span></div>
            <span className="contact-visual-caption">LEVEL X / PLAYER SUPPORT</span>
          </div>
        </div>
      </section>

      <section className="section contact-main-section">
        <div className="container">
          <div className="contact-section-heading"><div><span className="eyebrow">Reach the arena</span><h2>Talk to the <em>right people.</em></h2></div><p>For the quickest response about bookings, rates and availability, WhatsApp is the easiest way to reach us.</p></div>
          <div className="contact-main-grid">
            <div className="contact-methods">
              <a className="contact-method-card contact-method-whatsapp" href={whatsappLink("Hi Level X, I have an enquiry about visiting the gaming arena.")} target="_blank" rel="noopener noreferrer">
                <span className="contact-method-icon"><MessageCircle size={21} /></span><span className="contact-method-copy"><small>FASTEST WAY TO REACH US</small><strong>WhatsApp the team</strong><b>Chat about bookings & rates</b></span><ArrowUpRight className="contact-method-arrow" size={19} />
              </a>
              <a className="contact-method-card" href="tel:+923422631057"><span className="contact-method-icon"><Phone size={21} /></span><span className="contact-method-copy"><small>CALL THE ARENA</small><strong>{phoneDisplay}</strong><b>Speak with our team</b></span><ArrowUpRight className="contact-method-arrow" size={19} /></a>
              <a className="contact-method-card" href={`mailto:${emailAddress}`}><span className="contact-method-icon"><Mail size={21} /></span><span className="contact-method-copy"><small>EMAIL</small><strong>{emailAddress}</strong><b>For general enquiries</b></span><ArrowUpRight className="contact-method-arrow" size={19} /></a>
              <div className="contact-location-card"><span className="contact-method-icon"><MapPin size={21} /></span><div><small>LOCATION</small><strong>Karachi, Pakistan</strong><p>Message us on WhatsApp for location details and directions.</p></div></div>
            </div>
            <aside className="contact-booking-panel">
              <span className="eyebrow">Quick booking guide</span><h2>Tell us a few details.</h2><p>There’s no online booking form or payment checkout. Send these details to our team and we’ll confirm your session.</p>
              <div className="booking-step-list">{bookingSteps.map(({ icon: Icon, title, copy }, index) => <div className="booking-step" key={title}><span className="booking-step-icon"><Icon size={18} /></span><div><small>0{index + 1}</small><strong>{title}</strong><p>{copy}</p></div></div>)}</div>
              <WhatsAppButton message="Hi Level X, I would like to enquire about a visit. Activity: ____. Preferred date: ____. Preferred time: ____. Number of players: ____." label="Send booking enquiry" />
              <span className="contact-payment-note"><Send size={13} /> Payment is made at the arena counter.</span>
            </aside>
          </div>
        </div>
      </section>

      <section className="contact-bottom-strip"><div className="container"><div><span className="eyebrow">Open around the clock</span><h2>Day game or late-night match?</h2><p>We’re open 24/7. Check availability with our team before heading over.</p></div><div className="contact-bottom-hours"><Clock3 size={25} /><strong>24 / 7</strong><span>Every day of the week</span></div></div></section>
    </>
  );
}
