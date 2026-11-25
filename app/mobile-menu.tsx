"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { ArrowUpRight, Menu, MessageCircle, X } from "lucide-react";
import { whatsappLink } from "./data";
import gsap from "gsap";

const links = [
  { href: "/#activities", label: "Experiences", note: "PC, PS5 & table games" },
  { href: "/packages/", label: "Packages", note: "Hourly rates & passes" },
  { href: "/#gallery", label: "The arena", note: "Inside Level X" },
  { href: "/about-us/", label: "About us", note: "Our story" },
  { href: "/contact-us/", label: "Contact", note: "Plan your visit" },
];

export function MobileMenu() {
  const [open, setOpen] = useState(false);
  const panel = useRef<HTMLDivElement>(null);
  const closeButton = useRef<HTMLButtonElement>(null);
  const close = () => setOpen(false);

  useEffect(() => {
    if (!open) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") close();
    };
    window.addEventListener("keydown", onKeyDown);
    const ctx = gsap.context(() => {
      gsap.fromTo(panel.current, { autoAlpha: 0, y: -12 }, {
        autoAlpha: 1, y: 0, duration: 0.32, ease: "power3.out",
      });
      gsap.fromTo(".mobile-nav-item", { y: 18, autoAlpha: 0 }, {
        y: 0, autoAlpha: 1, duration: 0.42, stagger: 0.065, delay: 0.06, ease: "power3.out",
      });
      gsap.fromTo(".mobile-nav-bottom", { y: 12, autoAlpha: 0 }, {
        y: 0, autoAlpha: 1, duration: 0.35, delay: 0.28, ease: "power3.out",
      });
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
      <button
        className="mobile-menu-toggle"
        type="button"
        aria-label={open ? "Close navigation menu" : "Open navigation menu"}
        aria-expanded={open}
        aria-controls="mobile-navigation"
        onClick={() => setOpen((value) => !value)}
      >
        {open ? <X size={21} /> : <Menu size={21} />}
      </button>
      {open && (
        <div className="mobile-menu-backdrop" onClick={(event) => {
          if (event.target === event.currentTarget) close();
        }}>
          <div className="mobile-nav-panel" id="mobile-navigation" ref={panel} role="dialog" aria-modal="true" aria-label="Site navigation">
            <div className="mobile-nav-head">
              <span className="eyebrow">Navigate Level X</span>
              <button ref={closeButton} className="mobile-nav-close" type="button" onClick={close} aria-label="Close menu"><X size={20} /></button>
            </div>
            <nav className="mobile-nav" aria-label="Mobile navigation">
              {links.map((item, index) => (
                <Link className="mobile-nav-item" href={item.href} onClick={close} key={item.href}>
                  <span className="mobile-nav-number">0{index + 1}</span>
                  <span className="mobile-nav-label"><strong>{item.label}</strong><small>{item.note}</small></span>
                  <ArrowUpRight size={18} />
                </Link>
              ))}
            </nav>
            <div className="mobile-nav-bottom">
              <a className="button button-primary" href={whatsappLink("Hi Level X, I would like to book a gaming session. Please share availability.")} target="_blank" rel="noopener noreferrer" onClick={close}>
                <MessageCircle size={17} /> Book a session <ArrowUpRight size={15} />
              </a>
              <span>Open 24 hours · Karachi</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
