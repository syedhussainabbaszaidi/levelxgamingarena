import Image from "next/image";
import { notFound } from "next/navigation";
import { CheckCircle2, Clock3, Users } from "lucide-react";
import { activities, whatsappLink } from "../../data";
import { PageIntro, WhatsAppButton } from "../../components";
import type { Metadata } from "next";
export function generateStaticParams(){return activities.map(a=>({slug:a.slug}));}
export function generateMetadata({params}:{params:Promise<{slug:string}>}):Promise<Metadata>{return params.then(({slug})=>{const a=activities.find(x=>x.slug===slug);return {title:a?.name??"Activity",description:a?.description};});}
export default async function ActivityPage({params}:{params:Promise<{slug:string}>}){
 const {slug}=await params;const a=activities.find(x=>x.slug===slug);if(!a)return notFound();
 return <><section className="page-hero"><div className="container"><PageIntro eyebrow={a.category} title={a.name} copy={a.description}/></div></section><section className="content-section"><div className="container activity-detail"><Image src={a.image} alt={a.name} width={900} height={650}/><div><span className="tag">{a.category}</span><h1>{a.name}</h1><p>{a.description}</p><div className="detail-price"><span className="muted">Opening offer</span><div><strong>Rs. {a.offer.toLocaleString("en-PK")}</strong><span className="muted"> / hour</span></div><del className="muted">Regular: Rs. {a.price.toLocaleString("en-PK")}</del></div><ul className="feature-list">{a.features.map(f=><li key={f}><CheckCircle2 size={17}/>{f}</li>)}<li><Users size={17}/>Capacity: {a.capacity}</li><li><Clock3 size={17}/>Open 24 hours, every day</li></ul><WhatsAppButton message={`Hi Level X, I want to enquire about booking ${a.name}. Preferred date: ____. Preferred time: ____. Number of players: ____. Please confirm availability and final price.`}/><p className="muted" style={{fontSize:12,marginTop:15}}>No account or online payment required. Booking enquiries are handled directly by our team on WhatsApp.</p></div></div></section></>;
}
