"use client";

import { useLayoutEffect, useRef } from "react";
import { usePathname } from "next/navigation";
import type { ReactNode } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export function SiteMotion({ children }: { children: ReactNode }) {
  const scope = useRef<HTMLDivElement>(null);
  const pathname = usePathname();

  useLayoutEffect(() => {
    const root = scope.current;
    if (!root) return;

    const header = root.querySelector<HTMLElement>(".site-header");
    const loader = root.querySelector<HTMLElement>(".site-loader");
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let scrollFrame = 0;
    let refreshFrame = 0;
    let disposed = false;

    const updateHeader = () => {
      if (scrollFrame) return;
      scrollFrame = window.requestAnimationFrame(() => {
        header?.classList.toggle("is-scrolled", window.scrollY > 18);
        scrollFrame = 0;
      });
    };

    window.addEventListener("scroll", updateHeader, { passive: true });
    updateHeader();

    if (reducedMotion) {
      loader?.classList.add("site-loader-hidden");
      return () => {
        window.removeEventListener("scroll", updateHeader);
        if (scrollFrame) window.cancelAnimationFrame(scrollFrame);
      };
    }

    const cleanups: Array<() => void> = [];
    const context = gsap.context(() => {
      if (loader) {
        gsap.set(loader, { autoAlpha: 1 });
        gsap.fromTo(
          loader.querySelector("img"),
          { autoAlpha: 0, y: 12, scale: 0.94 },
          { autoAlpha: 1, y: 0, scale: 1, duration: 0.65, ease: "power3.out" }
        );
        gsap.to(loader, {
          autoAlpha: 0,
          duration: 0.45,
          delay: 0.9,
          ease: "power2.inOut",
          onComplete: () => loader.classList.add("site-loader-hidden"),
        });
      }

      const hero = root.querySelector<HTMLElement>(".hero");
      const heroEyebrow = hero?.querySelector(".eyebrow");
      const heroTitle = hero?.querySelector("h1");
      const heroCopy = hero?.querySelector(".hero-copy");
      const heroActions = hero?.querySelectorAll(".hero-actions .button");
      const heroVisual = hero?.querySelector(".hero-art, .hero-image-frame, .hero-image-wrap");

      if (hero) {
        const intro = gsap.timeline({ defaults: { ease: "power3.out" } });
        if (heroEyebrow) intro.from(heroEyebrow, { y: 16, autoAlpha: 0, duration: 0.45 });
        if (heroTitle) intro.from(heroTitle, { y: 34, autoAlpha: 0, duration: 0.7 }, "-=0.18");
        if (heroCopy) intro.from(heroCopy, { y: 18, autoAlpha: 0, duration: 0.55 }, "-=0.35");
        if (heroActions?.length) intro.from(heroActions, { y: 14, autoAlpha: 0, duration: 0.4, stagger: 0.08 }, "-=0.28");
        if (heroVisual) intro.from(heroVisual, { x: 26, scale: 0.98, autoAlpha: 0, duration: 0.75 }, "-=0.45");
      } else {
        const pageHero = root.querySelector<HTMLElement>(".page-hero, .about-editorial-hero, .contact-editorial-hero, .packages-hero");
        if (pageHero) {
          const introItems = pageHero.querySelectorAll(".eyebrow, h1, p, .button, .about-editorial-visual, .contact-editorial-art, .packages-hero-art");
          if (introItems.length) {
            gsap.from(introItems, { y: 22, autoAlpha: 0, duration: 0.62, stagger: 0.07, ease: "power3.out" });
          }
        }
      }

      const revealSelector = [
        "[data-reveal]",
        ".section-heading",
        ".stat",
        ".faq-item",
        ".activity-detail > img",
        ".activity-detail > div",
        ".cta-band",
        ".about-manifesto-grid > *",
        ".about-lineup-card",
        ".about-hours-inner > *",
        ".contact-channel-card",
        ".contact-plan-card",
        ".contact-final-strip .container > *",
        ".premium-price-card",
        ".premium-account-card",
        ".packages-section-heading",
        ".packages-booking-panel > *",
        ".footer-cta",
      ].join(",");

      root.querySelectorAll<HTMLElement>(revealSelector).forEach((element) => {
        gsap.fromTo(
          element,
          { y: 24, autoAlpha: 0 },
          {
            y: 0,
            autoAlpha: 1,
            duration: 0.62,
            ease: "power3.out",
            immediateRender: false,
            scrollTrigger: {
              trigger: element,
              start: "top 90%",
              once: true,
              invalidateOnRefresh: true,
            },
          }
        );
      });

      const gridSelector = [
        ".activity-grid",
        ".package-grid",
        ".account-grid",
        ".feature-grid",
        ".gallery-grid",
        ".game-title-grid",
        ".quick-highlights-grid",
        ".about-lineup-grid",
        ".premium-package-grid",
        ".premium-account-grid",
      ].join(",");

      root.querySelectorAll<HTMLElement>(gridSelector).forEach((grid) => {
        const items = Array.from(grid.children).filter((item): item is HTMLElement => item instanceof HTMLElement);
        if (!items.length) return;
        gsap.fromTo(
          items,
          { y: 22, autoAlpha: 0 },
          {
            y: 0,
            autoAlpha: 1,
            duration: 0.58,
            stagger: 0.07,
            ease: "power3.out",
            immediateRender: false,
            scrollTrigger: {
              trigger: grid,
              start: "top 88%",
              once: true,
              invalidateOnRefresh: true,
            },
          }
        );
      });

      const footerColumns = root.querySelectorAll<HTMLElement>(".footer-grid > div, .footer-grid > details");
      if (footerColumns.length) {
        gsap.fromTo(
          footerColumns,
          { y: 18, autoAlpha: 0 },
          {
            y: 0,
            autoAlpha: 1,
            duration: 0.5,
            stagger: 0.07,
            ease: "power3.out",
            immediateRender: false,
            scrollTrigger: { trigger: root.querySelector(".footer"), start: "top 94%", once: true },
          }
        );
      }

      root.querySelectorAll<HTMLElement>(".button").forEach((button) => {
        const enter = () => gsap.to(button, { y: -3, duration: 0.18, overwrite: true });
        const leave = () => gsap.to(button, { y: 0, duration: 0.2, overwrite: true });
        button.addEventListener("pointerenter", enter);
        button.addEventListener("pointerleave", leave);
        cleanups.push(() => {
          button.removeEventListener("pointerenter", enter);
          button.removeEventListener("pointerleave", leave);
        });
      });
    }, root);

    const refresh = () => {
      if (disposed) return;
      ScrollTrigger.refresh();
    };
    refreshFrame = window.requestAnimationFrame(refresh);
    window.addEventListener("load", refresh, { once: true });
    const fontsReady = document.fonts?.ready;
    fontsReady?.then(refresh).catch(() => undefined);

    return () => {
      disposed = true;
      window.removeEventListener("scroll", updateHeader);
      window.removeEventListener("load", refresh);
      if (scrollFrame) window.cancelAnimationFrame(scrollFrame);
      if (refreshFrame) window.cancelAnimationFrame(refreshFrame);
      cleanups.forEach((cleanup) => cleanup());
      context.revert();
      ScrollTrigger.clearScrollMemory();
      header?.classList.remove("is-scrolled");
    };
  }, [pathname]);

  return <div ref={scope} className="site-motion-root">{children}</div>;
}
