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
    const root = scope.current;
    if (!root) return;

    const header = root.querySelector(".site-header");
    const loader = root.querySelector<HTMLElement>(".site-loader");
    let frameId = 0;
    const onScroll = () => {
      if (frameId) return;
      frameId = window.requestAnimationFrame(() => {
        header?.classList.toggle("is-scrolled", window.scrollY > 18);
        frameId = 0;
      });
    };

    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      loader?.classList.add("site-loader-hidden");
      return () => {
        window.removeEventListener("scroll", onScroll);
        if (frameId) window.cancelAnimationFrame(frameId);
      };
    }

    gsap.registerPlugin(ScrollTrigger);
    const cleanups: Array<() => void> = [];

    const ctx = gsap.context(() => {
      if (loader) {
        gsap.fromTo(
          loader.querySelector("img"),
          { autoAlpha: 0, y: 12, scale: 0.94 },
          { autoAlpha: 1, y: 0, scale: 1, duration: 0.65, ease: "power3.out" }
        );
        gsap.to(loader, {
          autoAlpha: 0,
          duration: 0.5,
          delay: 1.05,
          ease: "power2.inOut",
          onComplete: () => loader.classList.add("site-loader-hidden"),
        });
      }

      const hero = root.querySelector(".hero");
      const pageHero = root.querySelector(
        ".page-hero, .activity-detail-hero, .about-editorial-hero, .contact-editorial-hero, .packages-hero, .activities-landing-hero, .food-page-hero, .gallery-page-hero"
      );

      if (hero) {
        gsap.timeline({ defaults: { ease: "power3.out" } })
          .from(".hero .eyebrow", { y: 18, autoAlpha: 0, duration: 0.5 })
          .from(".hero h1", { y: 42, autoAlpha: 0, duration: 0.8 }, "-=0.22")
          .from(".hero-copy", { y: 22, autoAlpha: 0, duration: 0.6 }, "-=0.42")
          .from(".hero-actions .button", { y: 16, autoAlpha: 0, duration: 0.45, stagger: 0.1 }, "-=0.3")
          .from(".hero-art", { x: 34, scale: 0.97, autoAlpha: 0, duration: 0.9 }, "-=0.65");
      } else if (pageHero) {
        gsap.from(pageHero.querySelectorAll(".eyebrow, h1, p, .button, .about-editorial-visual, .contact-editorial-art, .packages-hero-art, .activity-hero-price, .activity-hero-bottom, .activity-back-link"), {
          y: 25,
          autoAlpha: 0,
          duration: 0.68,
          stagger: 0.08,
          ease: "power3.out",
        });
        const activityHeroImage = pageHero.querySelector(".activity-detail-hero-image img");
        if (activityHeroImage) {
          gsap.fromTo(activityHeroImage, { scale: 1.12 }, { scale: 1, duration: 1.8, ease: "power3.out" });
        }
      }

      const revealSelector = [
        ".section-heading",
        ".stat",
        ".faq-item",
        ".activity-detail > img",
        ".activity-detail > div",
        ".activity-detail-panel",
        ".activity-detail-visual",
        ".activity-detail-intro > *",
        ".activity-features-heading",
        ".activity-next-inner > *",
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

      gsap.utils.toArray<HTMLElement>(revealSelector).forEach((element) => {
        gsap.from(element, {
          y: 30,
          autoAlpha: 0,
          duration: 0.7,
          ease: "power3.out",
          scrollTrigger: { trigger: element, start: "top 88%", once: true },
        });
      });

      const staggerSelector = [
        ".activity-grid",
        ".activity-feature-grid",
        ".package-grid",
        ".account-grid",
        ".feature-grid",
        ".gallery-grid",
        ".game-title-grid",
        ".quick-highlights-grid",
        ".about-lineup-grid",
        ".premium-package-grid",
        ".premium-account-grid",
        ".activities-directory-grid",
        ".activities-additional-grid",
        ".food-category-grid",
        ".gallery-editorial-grid",
      ].join(",");

      gsap.utils.toArray<HTMLElement>(staggerSelector).forEach((grid) => {
        if (!grid.children.length) return;
        gsap.from(grid.children, {
          y: 30,
          autoAlpha: 0,
          duration: 0.65,
          stagger: 0.09,
          ease: "power3.out",
          scrollTrigger: { trigger: grid, start: "top 86%", once: true },
        });
      });

      const footer = root.querySelector(".footer");
      const footerColumns = root.querySelectorAll(".footer-grid > div");
      if (footer && footerColumns.length) {
        gsap.from(footerColumns, {
          y: 20,
          autoAlpha: 0,
          duration: 0.55,
          stagger: 0.09,
          scrollTrigger: { trigger: footer, start: "top 92%", once: true },
        });
      }

      gsap.utils.toArray<HTMLElement>(".button").forEach((button) => {
        const enter = () => gsap.to(button, { y: -3, duration: 0.2, overwrite: true });
        const leave = () => gsap.to(button, { y: 0, duration: 0.22, overwrite: true });
        button.addEventListener("pointerenter", enter);
        button.addEventListener("pointerleave", leave);
        cleanups.push(() => {
          button.removeEventListener("pointerenter", enter);
          button.removeEventListener("pointerleave", leave);
        });
      });
    }, root);

    const refresh = window.requestAnimationFrame(() => ScrollTrigger.refresh());

    return () => {
      window.removeEventListener("scroll", onScroll);
      if (frameId) window.cancelAnimationFrame(frameId);
      window.cancelAnimationFrame(refresh);
      cleanups.forEach((cleanup) => cleanup());
      ctx.revert();
      header?.classList.remove("is-scrolled");
    };
  }, [pathname]);

  return <div ref={scope} className="site-motion-root">{children}</div>;
}
