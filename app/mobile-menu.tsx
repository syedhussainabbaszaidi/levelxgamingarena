"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { ArrowLeft, ArrowUpRight, ChevronRight, Menu, MessageCircle, X, Gamepad2, Monitor, Trophy, Table2, CircleDot, Crown, Sparkles } from "lucide-react";
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

const serviceLinks = [
  { href: "/activities/pc-games/", label: "PC Gaming", note: "Competitive gaming stations", Icon: Monitor },
  { href: "/activities/ps5/", label: "PlayStation 5", note: "Console sessions", Icon: Gamepad2 },
  { href: "/activities/premium-snooker-room/", label: "Premium Snooker", note: "Private snooker room", Icon: Trophy },
  { href: "/activities/private-table-tennis-room/", label: "Table Tennis", note: "Private table tennis room", Icon: Table2 },
  { href: "/activities/", label: "Pool Table", note: "Explore cue sports", Icon: CircleDot },
  { href: "/activities/", label: "VIP Private Rooms", note: "Private gaming spaces", Icon: Crown },
  { href: "/activities/", label: "All activities", note: "See every experience", Icon: ArrowUpRight },
] as const;

export function MobileMenu() {
  const [open, setOpen] = useState(false);
  const [nestedOpen, setNestedOpen] = useState(false);
  const panel = useRef<HTMLDivElement>(null);
  const nestedPanel = useRef<HTMLDivElement>(null);
  const closing = useRef(false);
  const closeButton = useRef<HTMLButtonElement>(null);
  const toggleButton = useRef<HTMLButtonElement>(null);
  const close = () => {
    if (closing.current) return;
    closing.current = true;
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduced || !panel.current) {
      setOpen(false); setNestedOpen(false); closing.current = false; return;
    }
    gsap.to(panel.current, { x: "100%", autoAlpha: 0, duration: 0.32, ease: "power2.in", onComplete: () => {
      setOpen(false); setNestedOpen(false); closing.current = false;
    } });
  };
  const closeNested = () => {
    if (!nestedOpen || !nestedPanel.current) { setNestedOpen(false); return; }
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduced) { setNestedOpen(false); return; }
    gsap.to(nestedPanel.current, { x: "38px", autoAlpha: 0, duration: 0.24, ease: "power2.in", onComplete: () => setNestedOpen(false) });
  };

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
  }, [open]);

  return (
    <div className="mobile-menu">
      <button ref={toggleButton} className="mobile-menu-toggle" type="button" aria-label={open ? "Close navigation menu" : "Open navigation menu"} aria-expanded={open} aria-controls="mobile-navigation" onClick={() => { setOpen((value) => !value); setNestedOpen(false); }}>
        {open ? <X size={21} /> : <Menu size={21} />}
      </button>
      {open && (
        <div className="mobile-menu-backdrop" onClick={(event) => { if (event.target === event.currentTarget) close(); }}>
          <div className="mobile-nav-panel" id="mobile-navigation" ref={panel} role="dialog" aria-modal="true" aria-label="Site navigation">
            <div className="mobile-nav-head">
              {nestedOpen ? <button className="mobile-nav-back" type="button" onClick={closeNested}><ArrowLeft size={17} /> Back to menu</button> : <span className="eyebrow">Navigate Level X</span>}
              <button ref={closeButton} className="mobile-nav-close" type="button" onClick={close} aria-label="Close menu"><X size={20} /></button>
            </div>
            {nestedOpen ? (
              <div className="mobile-nested-panel" ref={nestedPanel}>
                <div className="mobile-nested-heading"><span>LEVEL X / EXPLORE</span><h2>Choose your game.</h2><p>Pick a gaming experience and explore its details, rates and availability.</p></div>
                <div className="mobile-nested-columns">
                  <section>
                    <h3>Packages</h3>
                    <Link href="/packages/#hourly-rates" onClick={close}><span><strong>Hourly rates</strong><small>Compare current session pricing</small></span><ArrowUpRight size={15}/></Link>
                    <Link href="/packages/#account-passes" onClick={close}><span><strong>Prepaid passes</strong><small>Play-time bundles</small></span><ArrowUpRight size={15}/></Link>
                    <Link href="/packages/" onClick={close}><span><strong>All packages</strong><small>View every available rate</small></span><ArrowUpRight size={15}/></Link>
                  </section>
                  <section>
                    <h3>Services</h3>
                    {serviceLinks.map(({ href, label, note, Icon }) => <Link href={href} key={label} onClick={close}><span><strong>{label}</strong><small>{note}</small></span><ArrowUpRight size={15}/></Link>)}
                  </section>
                </div>
              </div>
            ) : (
              <>
                <nav className="mobile-nav" aria-label="Mobile navigation">
                  {links.slice(0,2).map((item,index)=><Link className="mobile-nav-item" href={item.href} onClick={close} key={item.href}><span className="mobile-nav-number">0{index+1}</span><span className="mobile-nav-label"><strong>{item.label}</strong><small>{item.note}</small></span></Link>)}
                  <button className="mobile-nav-item mobile-nav-parent" type="button" aria-expanded={nestedOpen} onClick={()=>setNestedOpen(true)}><span className="mobile-nav-number">03</span><span className="mobile-nav-label"><strong>Packages & Services</strong><small>Browse rates and gaming options</small></span><ChevronRight size={19}/></button>
                  {links.slice(2).map((item,index)=><Link className="mobile-nav-item" href={item.href} onClick={close} key={item.href}><span className="mobile-nav-number">0{index+5}</span><span className="mobile-nav-label"><strong>{item.label}</strong><small>{item.note}</small></span></Link>)}
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
