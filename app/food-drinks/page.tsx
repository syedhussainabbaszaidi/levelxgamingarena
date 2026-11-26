import type { Metadata } from "next";
import Image from "next/image";
import { ArrowUpRight, Coffee, Sandwich, CupSoda } from "lucide-react";
import { PageIntro, WhatsAppButton } from "../components";
import { cafeMenu, whatsappLink } from "../data";
import { pageMetadata } from "../seo";

export const metadata: Metadata = pageMetadata({
  title: "Food & Drinks at Level X Gaming Arena",
  description: "Explore the latest listed cafe menu prices for burgers, sandwiches, snacks and drinks at Level X Gaming Arena in Karachi.",
  path: "/food-drinks/",
  keywords: ["gaming cafe food Karachi", "food and drinks Level X", "gaming arena refreshments Karachi"],
});

const menuGroups=[
  {icon:Sandwich,title:"Burgers & sandwiches",items:cafeMenu.filter(item=>item.category==="Burgers & sandwiches")},
  {icon:Coffee,title:"Snacks",items:cafeMenu.filter(item=>item.category==="Snacks")},
  {icon:CupSoda,title:"Beverages",items:cafeMenu.filter(item=>item.category==="Beverages")},
];

export default function FoodDrinksPage(){return <>
  <section className="food-page-hero"><div className="container food-page-hero-grid"><div><PageIntro eyebrow="Food & drinks" title="Keep the good times going." copy="Take a break between matches, catch up with your crew and ask the arena team what is on the menu today."/><div className="food-hero-note"><span className="live-dot"/>Published cafe menu and prices</div><WhatsAppButton message="Hi Level X, could you please share today's food and drinks menu, current prices and combo availability?" label="Check menu availability"/></div><div className="food-orbit-art" aria-hidden="true"><div className="food-orbit-halo"></div><div className="food-orbit-ring food-orbit-ring-outer"><i></i></div><div className="food-orbit-ring food-orbit-ring-inner"><i></i></div><div className="food-orbit-core"><span>LEVEL X</span><strong>REFUEL<br/> & REPEAT</strong><small>FOOD / DRINKS / GOOD TIMES</small></div><span className="food-orbit-label food-orbit-label-top">TAKE A BREAK</span><span className="food-orbit-label food-orbit-label-bottom">BACK TO THE GAME</span></div></div></section>
  <section className="food-menu-section"><div className="container"><div className="food-menu-heading"><span className="eyebrow">01 / THE REFRESHMENT BREAK</span><h2>Something for <em>the in-between.</em></h2><p>Cafe menu and prices from the latest Level X menu shared on 26 November 2026. Availability can be confirmed with the arena team.</p></div><div className="food-category-grid">{menuGroups.map(({icon:Icon,title,items},index)=><article key={title}><span className="food-category-number">0{index+1}</span><span className="food-category-icon"><Icon size={25}/></span><small>LEVEL X CAFE MENU</small><h3>{title}</h3><div className="food-menu-items">{items.map(item=><div className="food-menu-item" key={item.name}><span>{item.name}</span><strong>Rs. {item.price.toLocaleString("en-PK")}</strong></div>)}</div><a href={whatsappLink(`Hi Level X, please confirm availability of ${title.toLowerCase()} from the cafe menu.`)} target="_blank" rel="noopener noreferrer">Ask about availability <ArrowUpRight size={15}/></a></article>)}</div></div></section>
  <section className="food-experience-section"><div className="container food-experience-grid"><div className="food-experience-image"><Image src="/img/site/arena.webp" alt="Level X Gaming Arena gaming floor" width={900} height={700}/></div><div><span className="eyebrow">02 / MAKE A SESSION OF IT</span><h2>Game together.<br/><em>Take a breather.</em></h2><p>Plan your gaming time and ask about refreshments before you arrive. Our team can confirm the available menu, current prices and any running combos.</p><WhatsAppButton message="Hi Level X, I am planning a visit. Please share gaming availability and today's food and drinks options." label="Plan your visit"/></div></div></section>
</>;}
