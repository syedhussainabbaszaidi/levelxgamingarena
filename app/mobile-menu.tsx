"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { ArrowLeft, ArrowUpRight, ChevronRight, Menu, MessageCircle, X } from "lucide-react";
import { whatsappLink } from "./data";
import gsap from "gsap";

const links = [
  { href: "/", label: "Home", note: "Back to the arena" },
  { href: "/activities/", label: "Activities", note: "Every way to play" },
  { href: "/gallery/", label: "Gallery", note: "Inside Level X" },
  { href: "/food-drinks/", label: "Food & drinks", note: "Ask about today's menu" },
  { href: "/about-us/", label: "About us", note: "Our story" },
  { href: "/contact-us/", label: "Contact", note: "Plan your visit" },
];

const packageLinks = [
  { href: "/packages/#hourly-rates", label: "Hourly rates", note: "Published session rates" },
  { href: "/packages/#account-passes", label: "Prepaid passes", note: "Play-time bundles" },
  { href: "/packages/", label: "All packages", note: "Compare every option" },
];
const serviceLinks = [
  { href: "/activities/pc-games/", label: "PC Gaming" },
  { href: "/activities/ps5/", label: "PlayStation 5" },
  { href: "/activities/premium-snooker-room/", label: "Premium Snooker" },
  { href: "/activities/private-table-tennis-room/", label: "Private Table Tennis" },
  { href: "/activities/", label: "All activities" },
];

export function MobileMenu() {
  const [open, setOpen] = useState(false);
  const [nestedOpen, setNestedOpen] = useState(false);
  const panel = useRef<HTMLDivElement>(null);
  const closeButton = useRef<HTMLButtonElement>(null);
  const toggleButton = useRef<HTMLButtonElement>(null);
  const close = () => { setOpen(false); setNestedOpen(false); };

  useEffect(() => {
    if (!open) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        if (nestedOpen) setNestedOpen(false);
        else close();
      }
    };
    window.addEventListener("keydown", onKeyDown);
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const ctx = gsap.context(() => {
      if (!reduced) {
        gsap.fromTo(panel.current, { x: 34, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 0.38, ease: "power3.out" });
        gsap.fromTo(".mobile-nav-item", { x: 18, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 0.38, stagger: 0.045, delay: 0.06, ease: "power3.out" });
      }
    }, panel);
    closeButton.current?.focus();
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
      ctx.revert();
    };
  }, [open, nestedOpen]);

  return (
    <div className="mobile-menu">
      <button ref={toggleButton} className="mobile-menu-toggle" type="button" aria-label={open ? "Close navigation menu" : "Open navigation menu"} aria-expanded={open} aria-controls="mobile-navigation" onClick={() => { setOpen((value) => !value); setNestedOpen(false); }}>
        {open ? <X size={21} /> : <Menu size={21} />}
      </button>
      {open && (
        <div className="mobile-menu-backdrop" onClick={(event) => { if (event.target === event.currentTarget) close(); }}>
          <div className="mobile-nav-panel" id="mobile-navigation" ref={panel} role="dialog" aria-modal="true" aria-label="Site navigation">
            <div className="mobile-nav-head">
              {nestedOpen ? <button className="mobile-nav-back" type="button" onClick={() => setNestedOpen(false)}><ArrowLeft size={17} /> Back to menu</button> : <span className="eyebrow">Navigate Level X</span>}
              <button ref={closeButton} className="mobile-nav-close" type="button" onClick={close} aria-label="Close menu"><X size={20} /></button>
            </div>
            {nestedOpen ? (
              <div className="mobile-nested-panel">
                <div className="mobile-nested-heading"><span>LEVEL X / EXPLORE</span><h2>Packages & services</h2><p>Choose a session or explore the games available at the arena.</p></div>
                <div className="mobile-nested-columns">
                  <section><h3>Packages</h3>{packageLinks.map((item) => <Link href={item.href} key={item.href} onClick={close}><strong>{item.label}</strong><small>{item.note}</small></Link>)}</section>
                  <section><h3>Services</h3>{serviceLinks.map((item) => <Link href={item.href} key={item.href} onClick={close}>{item.label}<ChevronRight size={14}/></Link>)}</section>
                </div>
              </div>
            ) : (
              <>
                <nav className="mobile-nav" aria-label="Mobile navigation">
                  {links.slice(0,2).map((item,index)=><Link className="mobile-nav-item" href={item.href} onClick={close} key={item.href}><span className="mobile-nav-number">0{index+1}</span><span className="mobile-nav-label"><strong>{item.label}</strong><small>{item.note}</small></span><ArrowUpRight size={18}/></Link>)}
                  <button className="mobile-nav-item mobile-nav-parent" type="button" aria-expanded={nestedOpen} onClick={()=>setNestedOpen(true)}><span className="mobile-nav-number">03</span><span className="mobile-nav-label"><strong>Packages & services</strong><small>Rates, passes and gaming options</small></span><ChevronRight size={19}/></button>
                  {links.slice(2).map((item,index)=><Link className="mobile-nav-item" href={item.href} onClick={close} key={item.href}><span className="mobile-nav-number">0{index+4}</span><span className="mobile-nav-label"><strong>{item.label}</strong><small>{item.note}</small></span><ArrowUpRight size={18}/></Link>)}
                </nav>
                <div className="mobile-nav-bottom">
                  <a className="button button-primary" href={whatsappLink("Hi Level X, I would like to book a gaming session. Please share availability.")} target="_blank" rel="noopener noreferrer" onClick={close}><MessageCircle size={17}/> Book a session <ArrowUpRight size={15}/></a>
                  <span>Open 24 hours · Karachi</span>
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
