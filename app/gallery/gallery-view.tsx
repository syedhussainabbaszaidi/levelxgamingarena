"use client";

import { useEffect, useMemo, useState } from "react";
import Image from "next/image";
import { X, ChevronLeft, ChevronRight, ArrowUpRight } from "lucide-react";

const images = [
  {src:"/img/site/arena.webp",title:"Gaming Arena",category:"The Arena"},
  {src:"/img/site/arena.webp",title:"Gaming Floor",category:"The Arena"},
  {src:"/img/site/pc-gaming.webp",title:"PC Gaming Setup",category:"Gaming Setups"},
  {src:"/img/site/pc-gaming.webp",title:"PC Gaming",category:"Gaming Setups"},
  {src:"/img/activities/covers/ps5.jpg",title:"PlayStation 5",category:"Gaming Setups"},
  {src:"/img/activities/covers/snooker.jpg",title:"Premium Snooker",category:"Experiences"},
  {src:"/img/activities/covers/table-tennis.jpg",title:"Private Table Tennis",category:"Experiences"},
];
const filters=["All","The Arena","Gaming Setups","Experiences"];

export function GalleryView(){
  const [filter,setFilter]=useState("All");
  const [active,setActive]=useState<number|null>(null);
  const visible=useMemo(()=>images.map((item,index)=>({...item,index})).filter(item=>filter==="All"||item.category===filter),[filter]);
  useEffect(()=>{
    if(active===null)return;
    const previous=document.body.style.overflow;
    document.body.style.overflow="hidden";
    const onKey=(event:KeyboardEvent)=>{if(event.key==="Escape")setActive(null);if(event.key==="ArrowRight")setActive(value=>value===null?null:(value+1)%visible.length);if(event.key==="ArrowLeft")setActive(value=>value===null?null:(value-1+visible.length)%visible.length);};
    window.addEventListener("keydown",onKey);
    return()=>{document.body.style.overflow=previous;window.removeEventListener("keydown",onKey);};
  },[active,visible.length]);
  useEffect(()=>{setActive(null);},[filter]);
  const current=active===null?null:visible[active];
  return <>
    <div className="gallery-filter-row" role="group" aria-label="Filter gallery images">{filters.map(item=><button type="button" key={item} className={filter===item?"is-active":""} aria-pressed={filter===item} onClick={()=>setFilter(item)}>{item}</button>)}</div>
    <div className="gallery-editorial-grid">{visible.map(item=><button className="gallery-editorial-card" type="button" key={`${item.src}-${item.title}`} onClick={()=>setActive(visible.findIndex(entry=>entry.index===item.index))} aria-label={`View ${item.title} image`}><Image src={item.src} alt={item.title+" at Level X Gaming Arena"} width={900} height={680} loading="lazy"/><span><small>{item.category}</small><strong>{item.title}</strong><ArrowUpRight size={18}/></span></button>)}</div>
    {current&&<div className="gallery-lightbox" role="dialog" aria-modal="true" aria-label={current.title} onClick={()=>setActive(null)}><button className="gallery-lightbox-close" type="button" onClick={()=>setActive(null)} aria-label="Close image"><X size={23}/></button><button className="gallery-lightbox-prev" type="button" onClick={event=>{event.stopPropagation();setActive(value=>value===null?null:(value-1+visible.length)%visible.length);}} aria-label="Previous image"><ChevronLeft size={28}/></button><figure onClick={event=>event.stopPropagation()}><Image src={current.src} alt={current.title+" at Level X Gaming Arena"} width={1400} height={1000} priority/><figcaption><small>{current.category}</small><strong>{current.title}</strong></figcaption></figure><button className="gallery-lightbox-next" type="button" onClick={event=>{event.stopPropagation();setActive(value=>value===null?null:(value+1)%visible.length);}} aria-label="Next image"><ChevronRight size={28}/></button></div>}
  </>;
}
