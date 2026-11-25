import Image from "next/image";
import Link from "next/link";
import { ArrowRight, ArrowUpRight, ChevronDown, Clock3, Mail, MapPin, MessageCircle, Phone, Sparkles } from "lucide-react";
import { emailAddress, phoneDisplay, whatsappLink } from "./data";
import { MobileMenu } from "./mobile-menu";

export function Header() {
  return (
    <>
      <div className="announcement-bar"><div className="container announcement-inner"><span><Sparkles size={14} /> Karachi · Open 24 hours, every day</span></div></div>
      <header className="site-header">
        <div className="container nav-wrap">
          <Link href="/" className="brand" aria-label="Level X Gaming Arena home">
            <Image src="/img/logo.png" alt="Level X Gaming Arena logo" width={156} height={58} priority />
          </Link>
          <nav className="desktop-nav" aria-label="Main navigation">
            <Link href="/">Home</Link>
            <Link href="/activities/">Activities</Link>
            <div className="nav-dropdown">
              <Link href="/packages/" aria-haspopup="true">Packages <ChevronDown size={13} /></Link>
              <div className="nav-dropdown-panel">
                <span className="nav-dropdown-kicker">PLAY MORE / PAY SMART</span>
                <Link href="/packages/#hourly-rates"><strong>Hourly rates</strong><small>Compare current session pricing</small></Link>
                <Link href="/packages/#account-passes"><strong>Prepaid passes</strong><small>Play-time bundles</small></Link>
              </div>
            </div>
            <Link href="/gallery/">Gallery</Link>
            <Link href="/food-drinks/">Food & Drinks</Link>
            <Link href="/about-us/">About</Link>
            <Link href="/contact-us/">Contact</Link>
          </nav>
          <div className="nav-actions">
            <a className="button button-primary nav-cta" href={whatsappLink("Hi Level X, I would like to book a gaming session. Please share availability.")} target="_blank" rel="noopener noreferrer">
              <MessageCircle size={16} /> Book a session <ArrowUpRight size={15} />
            </a>
            <MobileMenu />
          </div>
        </div>
      </header>
    </>
  );
}

export function LogoLoader() { return <div className="site-loader" aria-label="Loading Level X Gaming Arena"><Image src="/img/logo.png" alt="Level X Gaming Arena" width={190} height={72} priority/><span className="site-loader-line"/><small>LOADING THE ARENA</small></div>; }

export function Footer() {
  return (
    <footer className="footer">
      <div className="container footer-cta">
        <div>
          <span className="eyebrow">THE LEVEL X GAMING ZONE</span>
          <h2>Your next game starts at Level X.</h2>
          <p>PC gaming, PS5, snooker and table games — choose what you want to play and message our team to check availability.</p>
        </div>
        <div className="footer-cta-actions">
          <a className="button button-light" href={whatsappLink("Hi Level X, I would like to plan a visit. Please share availability.")} target="_blank" rel="noopener noreferrer"><MessageCircle size={17} /> Chat on WhatsApp <ArrowUpRight size={15} /></a>
          <Link className="footer-secondary-link" href="/packages/">View packages <ArrowRight size={15} /></Link>
        </div>
        <div className="footer-cta-glow" aria-hidden="true" />
      </div>
      <div className="container footer-grid">
        <div className="footer-brand-column">
          <Link href="/" className="brand" aria-label="Level X Gaming Arena home">
            <Image src="/img/logo.png" alt="Level X Gaming Arena logo" width={156} height={58} />
          </Link>
          <p className="footer-copy">Level X Gaming Arena in Karachi brings PC gaming, PlayStation 5, premium snooker, pool and private table tennis together. Open 24/7 for players, friends and groups.</p>
          <div className="footer-social-note"><span className="live-dot" /> Open 24 hours · Every day</div>
        </div>
        <details className="footer-group footer-accordion" open>
          <summary><h4>Explore</h4><ChevronDown size={15}/></summary>
          <div className="footer-accordion-links">
          <Link href="/activities/">All activities</Link>
          <Link href="/activities/pc-games/">PC gaming</Link>
          <Link href="/activities/ps5/">PlayStation 5</Link>
          <Link href="/activities/premium-snooker-room/">Premium snooker</Link>
          <Link href="/activities/private-table-tennis-room/">Private table tennis</Link>
          <Link href="/gallery/">Gallery</Link>
          </div>
        </details>
        <details className="footer-group footer-accordion" open>
          <summary><h4>Plan your visit</h4><ChevronDown size={15}/></summary>
          <div className="footer-accordion-links">
          <Link href="/packages/">Packages & pricing</Link>
          <Link href="/food-drinks/">Food & drinks</Link>
          <Link href="/about-us/">About Level X</Link>
          <Link href="/contact-us/">Contact & booking</Link>
          </div>
        </details>
        <div className="footer-contact-column">
          <h4>Talk to our team</h4>
          <a href={whatsappLink("Hi Level X, I have an enquiry.")} target="_blank" rel="noopener noreferrer"><span className="footer-icon"><MessageCircle size={16} /></span><span><small>WhatsApp</small><strong>Chat with us</strong></span></a>
          <a href="tel:+923422631057"><span className="footer-icon"><Phone size={16} /></span><span><small>Call us</small><strong>{phoneDisplay}</strong></span></a>
          <a href={`mailto:${emailAddress}`}><span className="footer-icon"><Mail size={16} /></span><span><small>Email</small><strong>{emailAddress}</strong></span></a>
          <span className="footer-location"><MapPin size={15} /> Karachi, Pakistan</span>
        </div>
      </div>
      <div className="container footer-bottom">
        <span>© {new Date().getFullYear()} Level X Gaming Arena. All rights reserved.</span>
        <span>PC · PS5 · SNOOKER · TABLE GAMES</span>
      </div>
    </footer>
  );
}

export function PageIntro({ eyebrow, title, copy }: { eyebrow: string; title: string; copy: string }) {
  return (
    <div className="page-intro" data-reveal="intro">
      <span className="eyebrow">{eyebrow}</span>
      <h1>{title}</h1>
      <p>{copy}</p>
    </div>
  );
}

export function WhatsAppButton({ message, label = "Book on WhatsApp" }: { message: string; label?: string }) {
  return (
    <a className="button button-primary" href={whatsappLink(message)} target="_blank" rel="noopener noreferrer">
      <MessageCircle size={17} />{label}<ArrowUpRight size={16} />
    </a>
  );
}

export function SectionHeading({ eyebrow, title, copy }: { eyebrow: string; title: string; copy?: string }) {
  return (
    <div className="section-heading" data-reveal="heading">
      <span className="eyebrow">{eyebrow}</span>
      <h2>{title}</h2>
      {copy && <p>{copy}</p>}
    </div>
  );
}
