"use client";

import { FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { Bot, MessageCircle, Send, X, PhoneCall, ChevronDown, Sparkles } from "lucide-react";
import { whatsappNumber } from "./data";

type Language = "en" | "ur";
type Stage = "details" | "language" | "chat";
type ChatMessage = { role: "assistant" | "user"; text: string };

const knowledge = [
  { keys: ["location","address","where","map","directions","kahan","kidhar","pata","address"], en: "We’re at Federal B Area, Block 12, Karachi. Open the Contact page for Google Maps directions.", ur: "Hum Federal B Area, Block 12, Karachi mein hain. Google Maps directions ke liye Contact page dekhein." },
  { keys: ["hour","timing","open","close","24","raat","time","kab"], en: "We’re open 24/7, every day.", ur: "Hum rozana 24 hours open hain." },
  { keys: ["pc","computer","gaming pc","desktop","valorant","pubg","cs2","dota"], en: "PC Gaming: PKR 200/hour offer, PKR 250 regular. Mid Day: PKR 150/hour (9 AM–5 PM, min. 3 hours). Monday Madness: PKR 140/hour (min. 3 hours).", ur: "PC Gaming: offer PKR 200/hour, regular PKR 250. Mid Day PKR 150/hour (9 AM–5 PM, kam az kam 3 hours). Monday Madness PKR 140/hour (min. 3 hours)." },
  { keys: ["ps5","playstation","console","controller","fifa"], en: "PS5: PKR 650/hour offer, PKR 1,000 regular. Two controllers included; an extra controller is PKR 300/hour.", ur: "PS5: offer PKR 650/hour, regular PKR 1,000. 2 controllers included hain; extra controller PKR 300/hour." },
  { keys: ["snooker","cue","table"], en: "Premium Snooker: PKR 12/minute offer, PKR 15 regular. Monday Madness: PKR 8/minute.", ur: "Premium Snooker: offer PKR 12/minute, regular PKR 15. Monday Madness PKR 8/minute." },
  { keys: ["tennis","table tennis","ping pong"], en: "Private Table Tennis Room: PKR 600/hour offer, PKR 800 regular. Bats and balls are included.", ur: "Private Table Tennis Room: offer PKR 600/hour, regular PKR 800. Bats aur balls included hain." },
  { keys: ["pool","vip","private room","room"], en: "Pool Table and VIP Private Rooms are available as experiences. Please message our team for current rates and availability.", ur: "Pool Table aur VIP Private Rooms available experiences hain. Current rates aur availability ke liye team ko message karein." },
  { keys: ["package","price","pricing","rate","cost","kitna","qeemat","discount","offer"], en: "You can compare all hourly rates and prepaid passes on the Packages page. Offers may have time limits or minimum sessions.", ur: "Tamam hourly rates aur prepaid passes Packages page par hain. Kuch offers ke time limits ya minimum session hours hain." },
  { keys: ["pass","prepaid","account","5 hour","8 hour","10 hour","20 hour"], en: "Prepaid passes: 5 hours PKR 900, 8 hours PKR 1,400, 10 hours PKR 1,700 and 20 hours PKR 3,200. Player account is optional at PKR 1,000 one-time.", ur: "Prepaid passes: 5 hours PKR 900, 8 hours PKR 1,400, 10 hours PKR 1,700 aur 20 hours PKR 3,200. Player account optional hai, one-time PKR 1,000." },
  { keys: ["book","booking","reserve","reservation","availability","book kar","booking kar"], en: "To book, share your activity, preferred date/time and number of players with our team on WhatsApp. They’ll confirm availability.", ur: "Booking ke liye activity, preferred date/time aur players ki tadaad WhatsApp par bhej dein. Team availability confirm kar degi." },
  { keys: ["food","drink","menu","eat","khana"], en: "For today’s food and drinks menu, please contact the arena team on WhatsApp.", ur: "Aaj ke food aur drinks menu ke liye WhatsApp par arena team se rabta karein." },
  { keys: ["payment","pay","cash","online","card"], en: "Payment is made at the arena counter. The website does not collect online payments.", ur: "Payment arena counter par hoti hai. Website par online payment nahi hoti." },
  { keys: ["phone","call","contact","whatsapp","number","live agent","human","person","team"], en: "You can reach the team on WhatsApp or call +92 342 2631057.", ur: "Aap WhatsApp ya call par team se rabta kar sakte hain: +92 342 2631057." },
  { keys: ["group","friend","player","people","capacity"], en: "Group size depends on the activity. Tell our team which game you’re planning and how many players are coming.", ur: "Group size activity par depend karta hai. Game aur players ki tadaad team ko bata dein." },
];

const suggestions = {
  en: ["What are PC gaming rates?","Where are you located?","How can I book a session?","Are you open 24/7?"],
  ur: ["PC gaming ke rates kya hain?","Location kahan hai?","Booking kaise karun?","Kya 24 hours open hain?"],
};

function getAnswer(question: string, language: Language) {
  const q = question.toLowerCase().replace(/[^a-z0-9\s]/g, " ");
  const tokens = q.split(/\s+/).filter((word) => word.length > 2);
  let best = -1;
  let score = 0;
  knowledge.forEach((item, index) => {
    const hits = item.keys.reduce((sum, key) => {
      const k = key.toLowerCase();
      return sum + (q.includes(k) ? (k.includes(" ") ? 2 : 1) : 0);
    }, 0);
    const tokenHits = item.keys.reduce((sum, key) => sum + (tokens.includes(key.toLowerCase()) ? 1 : 0), 0);
    const total = hits + tokenHits * 0.45;
    if (total > score) { score = total; best = index; }
  });
  if (best >= 0 && score >= 1) return knowledge[best][language];
  return language === "en"
    ? "I can help with games, rates, timings, location and bookings. Try a suggested question or chat with our team."
    : "Main games, rates, timings, location aur booking mein help kar sakta hoon. Suggested sawal try karein ya team se baat karein.";
}

export function AIChat() {
  const [open, setOpen] = useState(false);
  const [stage, setStage] = useState<Stage>("details");
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [language, setLanguage] = useState<Language>("en");
  const [draft, setDraft] = useState("");
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (open && stage === "chat" && scrollRef.current) scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
  }, [messages, open, stage]);

  const liveAgentUrl = useMemo(() => {
    const intro = "Hi Level X team, I need help.\nName: " + name + "\nPhone: " + phone + "\nLanguage: " + (language === "en" ? "English" : "Roman Urdu");
    return "https://wa.me/" + whatsappNumber + "?text=" + encodeURIComponent(intro);
  }, [name, phone, language]);

  function submitDetails(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!name.trim() || phone.replace(/\D/g, "").length < 10) return;
    setStage("language");
  }

  function startChat(selected: Language) {
    setLanguage(selected);
    setStage("chat");
    const first = name.trim().split(/\s+/)[0];
    setMessages([{ role: "assistant", text: selected === "en" ? "Hi " + first + "! I’m the Level X assistant. Ask me about games, rates, timings or bookings." : "Assalam-o-alaikum " + first + "! Main Level X assistant hoon. Games, rates, timings ya booking ke bare mein pooch lein." }]);
  }

  function sendMessage(value = draft) {
    const question = value.trim();
    if (!question) return;
    const answer = getAnswer(question, language);
    setMessages((current) => [...current, { role: "user", text: question }, { role: "assistant", text: answer }]);
    setDraft("");
  }

  return (
    <div className="lx-chat-root">
      {open && <section className="lx-chat-panel" aria-label="Level X chat assistant">
        <header className="lx-chat-header">
          <span className="lx-chat-avatar"><Bot size={21}/></span>
          <span className="lx-chat-heading"><strong>Level X Assistant</strong><small><i/> Online · Quick answers</small></span>
          <button type="button" className="lx-chat-close" onClick={() => setOpen(false)} aria-label="Close chat"><X size={19}/></button>
        </header>
        {stage === "details" && <div className="lx-chat-onboard">
          <span className="lx-chat-kicker"><Sparkles size={13}/> YOUR ARENA CONCIERGE</span>
          <h3>Let’s get you<br/><em>game-ready.</em></h3>
          <p>First, tell us who we’re chatting with.</p>
          <form onSubmit={submitDetails}>
            <label>Your name<input value={name} onChange={(e) => setName(e.target.value)} placeholder="Enter your name" autoComplete="name" required maxLength={60}/></label>
            <label>Mobile number<input value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="+92 3XX XXXXXXX" autoComplete="tel" inputMode="tel" required minLength={10}/></label>
            <button className="lx-chat-primary" type="submit">Continue <Send size={15}/></button>
          </form>
          <small className="lx-chat-privacy">Your details are used to personalise this chat. They are shared with our team only if you choose WhatsApp.</small>
        </div>}
        {stage === "language" && <div className="lx-chat-onboard lx-chat-language">
          <span className="lx-chat-kicker"><Sparkles size={13}/> ONE QUICK CHOICE</span>
          <h3>How would you<br/><em>like to chat?</em></h3>
          <p>Choose your preferred language.</p>
          <button className="lx-chat-language-option" onClick={() => startChat("en")}><span>EN</span><strong>English</strong><small>Let’s talk in English</small></button>
          <button className="lx-chat-language-option" onClick={() => startChat("ur")}><span>UR</span><strong>Roman Urdu</strong><small>Roman Urdu mein baat karein</small></button>
          <button className="lx-chat-back" onClick={() => setStage("details")}>Edit my details</button>
        </div>}
        {stage === "chat" && <>
          <div className="lx-chat-welcome"><span>WELCOME, {name.trim().split(/\s+/)[0].toUpperCase()}</span><strong>{language === "en" ? "What can I help you with?" : "Kis cheez mein help chahiye?"}</strong></div>
          <div className="lx-chat-messages" ref={scrollRef} aria-live="polite">
            {messages.map((message, index) => <div className={"lx-chat-message " + message.role} key={index}><span>{message.text}</span></div>)}
          </div>
          <div className="lx-chat-suggestions"><small>{language === "en" ? "QUICK QUESTIONS" : "AAM SAWAL"}</small>{suggestions[language].map((question) => <button key={question} onClick={() => sendMessage(question)}>{question}</button>)}</div>
          <form className="lx-chat-compose" onSubmit={(event) => { event.preventDefault(); sendMessage(); }}>
            <input value={draft} onChange={(event) => setDraft(event.target.value)} placeholder={language === "en" ? "Type your question..." : "Apna sawal likhein..."} aria-label="Your question"/>
            <button type="submit" aria-label="Send message" disabled={!draft.trim()}><Send size={17}/></button>
          </form>
          <div className="lx-chat-footer"><span>{language === "en" ? "Need a person?" : "Kisi team member se baat?"}</span><a href={liveAgentUrl} target="_blank" rel="noopener noreferrer"><PhoneCall size={13}/> {language === "en" ? "Live agent on WhatsApp" : "WhatsApp par live agent"}</a></div>
        </>}
      </section>}
      <button className={"lx-chat-launcher" + (open ? " is-open" : "")} onClick={() => setOpen((value) => !value)} aria-label={open ? "Close chat assistant" : "Chat with Level X"} aria-expanded={open}>
        {open ? <ChevronDown size={22}/> : <><MessageCircle size={22}/><span>Chat with us</span><i/></>}
      </button>
    </div>
  );
}
