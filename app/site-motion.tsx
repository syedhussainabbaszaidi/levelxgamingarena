"use client";

import { useLayoutEffect, useRef } from "react";
import { usePathname } from "next/navigation";
import type { ReactNode } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

export function SiteMotion({ children }: { children: ReactNode }) {
  const scope = useRef<HTMLDivElement>(null);
  const pathname = usePathname();

  useLayoutEffect(() => {
    if (!scope.current) return;
    const header = scope.current.querySelector(".site-header");
    let scrollFrame = 0;
    const updateHeader = () => {
      if (scrollFrame) return;
      scrollFrame = window.requestAnimationFrame(() => {
        header?.classList.toggle("is-scrolled", window.scrollY > 18);
        scrollFrame = 0;
      });
    };
    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      return () => {
        window.removeEventListener("scroll", updateHeader);
        if (scrollFrame) window.cancelAnimationFrame(scrollFrame);
        header?.classList.remove("is-scrolled");
      };
    }

    gsap.registerPlugin(ScrollTrigger);
    const cleanups: Array<() => void> = [];
    const context = gsap.context(() => {
      const hero = scope.current?.querySelector(".hero");
      const pageHero = scope.current?.querySelector(".page-hero");

      if (hero) {
        gsap.timeline({ defaults: { ease: "power3.out" } })
          .from(".hero .eyebrow", { y: 18, autoAlpha: 0, duration: 0.5 })
          .from(".hero h1", { y: 42, autoAlpha: 0, duration: 0.8 }, "-=0.22")
          .from(".hero-copy", { y: 22, autoAlpha: 0, duration: 0.6 }, "-=0.42")
          .from(".hero-actions .button", { y: 16, autoAlpha: 0, duration: 0.45, stagger: 0.1 }, "-=0.3")
          .from(".hero-points span", { y: 12, autoAlpha: 0, duration: 0.35, stagger: 0.07 }, "-=0.2")
          .from(".hero-art", { x: 34, scale: 0.97, autoAlpha: 0, duration: 0.9 }, "-=0.85");
        const heroArt = scope.current?.querySelector(".hero-art");
        if (heroArt) {
          gsap.to(heroArt, {
            yPercent: -4, ease: "none",
            scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: 0.7 },
          });
        }
      } else if (pageHero) {
        gsap.from(".page-intro > *", {
          y: 24, autoAlpha: 0, duration: 0.65, stagger: 0.09, ease: "power3.out",
        });
      }

      const grids = gsap.utils.toArray<HTMLElement>(
        ".activity-grid, .package-grid, .account-grid, .feature-grid, .gallery-grid",
      );
      grids.forEach((grid) => {
        gsap.from(grid.children, {
          y: 28, autoAlpha: 0, duration: 0.62, stagger: 0.085, ease: "power3.out",
          scrollTrigger: { trigger: grid, start: "top 86%", once: true },
        });
      });

      const revealItems = gsap.utils.toArray<HTMLElement>(
        ".section-heading, .stat, .faq-item, .legal-card, .contact-item, .activity-detail > img, .activity-detail > div, .cta-band, .arena-promo-inner, .experience-banner-copy, .experience-banner-image",
      );
      revealItems.forEach((item) => {
        gsap.from(item, {
          y: 26, autoAlpha: 0, duration: 0.65, ease: "power3.out",
          scrollTrigger: { trigger: item, start: "top 89%", once: true },
        });
      });

      const footer = scope.current?.querySelector(".footer");
      const footerCta = scope.current?.querySelector(".footer-cta");
      if (footerCta) {
        gsap.from(footerCta, {
          y: 24, autoAlpha: 0, duration: 0.65, ease: "power3.out",
          scrollTrigger: { trigger: footerCta, start: "top 94%", once: true },
        });
      }
      const footerColumns = footer?.querySelectorAll(".footer-grid > div");
      if (footerColumns?.length) {
        gsap.from(footerColumns, {
          y: 20, autoAlpha: 0, duration: 0.55, stagger: 0.09,
          scrollTrigger: { trigger: footer, start: "top 92%", once: true },
        });
      }

      gsap.utils.toArray<HTMLElement>(".button").forEach((button) => {
        const enter = () => gsap.to(button, { y: -3, scale: 1.015, duration: 0.2, overwrite: true });
        const leave = () => gsap.to(button, { y: 0, scale: 1, duration: 0.22, overwrite: true });
        button.addEventListener("pointerenter", enter);
        button.addEventListener("pointerleave", leave);
        cleanups.push(() => {
          button.removeEventListener("pointerenter", enter);
          button.removeEventListener("pointerleave", leave);
        });
      });
    }, scope);

    const frame = window.requestAnimationFrame(() => ScrollTrigger.refresh());
    return () => {
      window.removeEventListener("scroll", updateHeader);
      if (scrollFrame) window.cancelAnimationFrame(scrollFrame);
      window.cancelAnimationFrame(frame);
      cleanups.forEach((cleanup) => cleanup());
      context.revert();
      header?.classList.remove("is-scrolled");
    };
  }, [pathname]);

  return <div ref={scope} className="site-motion-root">{children}</div>;
}
