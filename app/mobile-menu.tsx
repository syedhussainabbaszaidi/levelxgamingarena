"use client";

import { useState } from "react";
import Link from "next/link";
import { Menu, X } from "lucide-react";

export function MobileMenu() {
  const [open, setOpen] = useState(false);
  const close = () => setOpen(false);

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
        <nav className="mobile-nav" id="mobile-navigation" aria-label="Mobile navigation">
          <Link href="/#activities" onClick={close}>Activities</Link>
          <Link href="/#packages" onClick={close}>Packages & pricing</Link>
          <Link href="/#gallery" onClick={close}>Gallery</Link>
          <Link href="/about-us/" onClick={close}>About us</Link>
          <Link href="/contact-us/" onClick={close}>Contact</Link>
        </nav>
      )}
    </div>
  );
}
