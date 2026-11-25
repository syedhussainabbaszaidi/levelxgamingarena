import Link from "next/link";
import { ArrowUpRight, Gamepad2, Clock3, MapPin, Phone, Mail, Instagram, MessageCircle, ChevronRight } from "lucide-react";
import { emailAddress, phoneDisplay, whatsappLink } from "./data";

export function Header() {
  return <header className="site-header"><div className="container nav-wrap">
    <Link href="/" className="brand"><span className="brand-mark">LX</span><span>LEVEL X<small>GAMING ARENA</small></span></Link>
    <nav className="desktop-nav"><Link href="/#activities">Activities</Link><Link href="/#packages">Packages</Link><Link href="/#gallery">Gallery</Link><Link href="/about-us/">About</Link><Link href="/contact-us/">Contact</Link></nav>
    <a className="button button-primary nav-cta" href={whatsappLink("Hi Level X, I would like to book a gaming session.")} target="_blank" rel="noreferrer">Book on WhatsApp <ArrowUpRight size={16}/></a>
  </div></header>;
}
export function Footer() {
  return <footer className="footer"><div className="container footer-grid">
    <div><Link href="/" className="brand"><span className="brand-mark">LX</span><span>LEVEL X<small>GAMING ARENA</small></span></Link><p className="muted footer-copy">A place for great games, friendly competition and unforgettable sessions. Open 24 hours, every day.</p></div>
    <div><h4>Explore</h4><Link href="/#activities">Activities</Link><Link href="/#packages">Packages & pricing</Link><Link href="/#gallery">Gallery</Link><Link href="/about-us/">About us</Link></div>
    <div><h4>Information</h4><Link href="/contact-us/">Contact</Link><Link href="/terms-and-conditions/">Terms & conditions</Link><Link href="/privacy-policy/">Privacy policy</Link><Link href="/cookie-policy/">Cookie policy</Link><Link href="/disclaimer/">Disclaimer</Link><Link href="/photo-credits/">Photo credits</Link></div>
    <div><h4>Get in touch</h4><a href={whatsappLink("Hi Level X, I have an enquiry.")} target="_blank" rel="noreferrer"><MessageCircle size={16}/> WhatsApp us</a><a href="tel:+923422631057"><Phone size={16}/> {phoneDisplay}</a><a href={`mailto:${emailAddress}`}><Mail size={16}/> {emailAddress}</a><span><Clock3 size={16}/> Open 24/7</span></div>
  </div><div className="container footer-bottom"><span>© {new Date().getFullYear()} Level X Gaming Arena. All rights reserved.</span><span>Built for players.</span></div></footer>;
}
export function PageIntro({eyebrow,title,copy}:{eyebrow:string,title:string,copy:string}) {
 return <div className="page-intro"><span className="eyebrow">{eyebrow}</span><h1>{title}</h1><p>{copy}</p></div>;
}
export function WhatsAppButton({message,label="Book on WhatsApp"}:{message:string,label?:string}) {
 return <a className="button button-primary" href={whatsappLink(message)} target="_blank" rel="noreferrer"><MessageCircle size={17}/>{label}<ArrowUpRight size={16}/></a>;
}
export function SectionHeading({eyebrow,title,copy}:{eyebrow:string,title:string,copy?:string}) {
 return <div className="section-heading"><span className="eyebrow">{eyebrow}</span><h2>{title}</h2>{copy&&<p>{copy}</p>}</div>;
}
