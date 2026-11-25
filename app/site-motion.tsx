"use client";
import { useLayoutEffect, useRef } from "react";
import { usePathname } from "next/navigation";
import type { ReactNode } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

export function SiteMotion({children}:{children:ReactNode}){
 const scope=useRef<HTMLDivElement>(null);
 const pathname=usePathname();
 useLayoutEffect(()=>{
  if(!scope.current)return;
  const root=scope.current;
  const header=root.querySelector(".site-header");
  const loader=root.querySelector(".site-loader");
  let frameId=0;
  const onScroll=()=>{if(frameId)return;frameId=window.requestAnimationFrame(()=>{header?.classList.toggle("is-scrolled",window.scrollY>18);frameId=0;});};
  onScroll();window.addEventListener("scroll",onScroll,{passive:true});
  if(window.matchMedia("(prefers-reduced-motion: reduce)").matches){loader?.classList.add("site-loader-hidden");return()=>window.removeEventListener("scroll",onScroll);}
  gsap.registerPlugin(ScrollTrigger);
  const cleanups:Array<()=>void>=[];
  const ctx=gsap.context(()=>{
   if(loader){gsap.fromTo(loader.querySelector("img"),{autoAlpha:0,y:12,scale:.94},{autoAlpha:1,y:0,scale:1,duration:.65,ease:"power3.out"});gsap.to(loader,{autoAlpha:0,duration:.5,delay:1.05,ease:"power2.inOut",onComplete:()=>loader.classList.add("site-loader-hidden")});}
   const hero=root.querySelector(".hero");
   const pageHero=root.querySelector(".page-hero, .about-editorial-hero, .contact-editorial-hero, .packages-hero, .activities-landing-hero, .food-page-hero, .gallery-page-hero");
   if(hero){gsap.timeline({defaults:{ease:"power3.out"}}).from(".hero .eyebrow",{y:18,autoAlpha:0,duration:.5}).from(".hero h1",{y:42,autoAlpha:0,duration:.8},"-=.22").from(".hero-copy",{y:22,autoAlpha:0,duration:.6},"-=.42").from(".hero-actions .button",{y:16,autoAlpha:0,duration:.45,stagger:.1},"-=.3").from(".hero-art",{x:34,scale:.97,autoAlpha:0,duration:.9},"-=.65");}
   else if(pageHero){gsap.from(pageHero.querySelectorAll(".eyebrow,h1,p,.button,.about-editorial-visual,.contact-editorial-art,.packages-hero-art"),{y:25,autoAlpha:0,duration:.68,stagger:.08,ease:"power3.out"});}
   const reveal=".section-heading,.stat,.faq-item,.activity-detail>img,.activity-detail>div,.cta-band,.about-manifesto-grid>*,.about-lineup-card,.about-hours-inner>*,.contact-channel-card,.contact-plan-card,.contact-final-strip .container>*,.premium-price-card,.premium-account-card,.packages-section-heading,.packages-booking-panel>*,.footer-cta";
   gsap.utils.toArray<HTMLElement>(reveal).forEach(el=>gsap.from(el,{y:30,autoAlpha:0,duration:.7,ease:"power3.out",scrollTrigger:{trigger:el,start:"top 88%",once:true}}));
   gsap.utils.toArray<HTMLElement>(".activity-grid,.package-grid,.account-grid,.feature-grid,.gallery-grid,.game-title-grid,.quick-highlights-grid,.about-lineup-grid,.premium-package-grid,.premium-account-grid,.activities-directory-grid,.activities-additional-grid,.food-category-grid,.gallery-editorial-grid").forEach(grid=>gsap.from(grid.children,{y:30,autoAlpha:0,duration:.65,stagger:.09,ease:"power3.out",scrollTrigger:{trigger:grid,start:"top 86%",once:true}}));
   const cols=root.querySelectorAll(".footer-grid>div");if(cols.length)gsap.from(cols,{y:20,autoAlpha:0,duration:.55,stagger:.09,scrollTrigger:{trigger:root.querySelector(".footer"),start:"top 92%",once:true}});
   gsap.utils.toArray<HTMLElement>(".button").forEach(button=>{const enter=()=>gsap.to(button,{y:-3,duration:.2,overwrite:true});const leave=()=>gsap.to(button,{y:0,duration:.22,overwrite:true});button.addEventListener("pointerenter",enter);button.addEventListener("pointerleave",leave);cleanups.push(()=>{button.removeEventListener("pointerenter",enter);button.removeEventListener("pointerleave",leave);});});
  },root);
  const refresh=window.requestAnimationFrame(()=>ScrollTrigger.refresh());
  return()=>{window.removeEventListener("scroll",onScroll);if(frameId)window.cancelAnimationFrame(frameId);window.cancelAnimationFrame(refresh);cleanups.forEach(fn=>fn());ctx.revert();header?.classList.remove("is-scrolled");};
 },[pathname]);
 return <div ref={scope} className="site-motion-root">{children}</div>;
}
