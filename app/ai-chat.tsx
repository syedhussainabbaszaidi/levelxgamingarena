"use client";

import { FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { Bot, MessageCircle, Send, X, PhoneCall, ChevronDown, Sparkles } from "lucide-react";
import { whatsappNumber } from "./data";

type Language = "en" | "ur";
type Stage = "details" | "language" | "chat";
type ChatMessage = { role: "assistant" | "user"; text: string };

const knowledge = [
  { keys: ["location","address","where","map","directions","kahan","kidhar","pata"], en: "We’re at Federal B Area, Block 12, Karachi. Open the Contact page for Google Maps directions.", ur: "Hum Federal B Area, Block 12, Karachi mein hain. Google Maps directions ke liye Contact page dekhein." },
  { keys: ["hour","timing","open","close","24","raat","time","kab"], en: "Level X is open 24 hours a day, every day.", ur: "Level X rozana 24 hours open hai." },
  { keys: ["x madness","x-madness","madness offer","100 per hour","100 rupees","special offer"], en: "The X-Madness offer is PKR 100 per hour, Monday to Thursday, from 9 AM to 5 PM. Please confirm eligible games and availability with the arena team.", ur: "X-Madness offer Rs. 100 per hour hai, Monday se Thursday, subah 9 se shaam 5 baje tak. Eligible games aur availability team se confirm kar lein." },
  { keys: ["pc","computer","gaming pc","desktop","valorant","pubg","cs2","dota"], en: "PC Gaming is PKR 200 per hour. The setup poster lists an AMD Ryzen 5 5600, NVIDIA GeForce RTX 2060 Super, 260 Hz gaming LCD and HyperX Cloud Alpha headset. Keyboard and mouse options are also shown.", ur: "PC Gaming Rs. 200 per hour hai. Setup mein AMD Ryzen 5 5600, NVIDIA GeForce RTX 2060 Super, 260 Hz gaming LCD aur HyperX Cloud Alpha headset listed hain. Keyboard aur mouse ke options bhi hain." },
  { keys: ["ps5","playstation","console","controller","fifa"], en: "PS5 gaming is PKR 700 per hour. Please message the arena team to check session availability.", ur: "PS5 gaming Rs. 700 per hour hai. Session availability ke liye arena team ko message kar dein." },
  { keys: ["snooker","cue"], en: "Snooker is PKR 12 per minute.", ur: "Snooker Rs. 12 per minute hai." },
  { keys: ["tennis","table tennis","ping pong"], en: "Table Tennis is PKR 600 per hour.", ur: "Table Tennis Rs. 600 per hour hai." },
  { keys: ["gta 6","gta6","gta vi","grand theft auto"], en: "Play GTA 6 at Level X: 1 hour PKR 1,000 (poster shows the previous PKR 1,200 price crossed out), 3 hours PKR 3,000, or 5 hours PKR 5,000. Ask the team to confirm availability.", ur: "Level X par GTA 6 khelne ke rates: 1 hour Rs. 1,000 (poster par purana Rs. 1,200 rate cut hai), 3 hours Rs. 3,000, ya 5 hours Rs. 5,000. Availability team se confirm kar lein." },
  { keys: ["membership","member","prepaid","pass","5 hour","8 hour","10 hour","20 hour","account"], en: "PC Gaming membership bundles: 5 hours PKR 900, 8 hours PKR 1,400, 10 hours PKR 1,700 and 20 hours PKR 3,200. Account opening is free. These are PC Gaming membership packages.", ur: "PC Gaming membership bundles: 5 hours Rs. 900, 8 hours Rs. 1,400, 10 hours Rs. 1,700 aur 20 hours Rs. 3,200. Account opening free hai. Ye PC Gaming membership packages hain." },
  { keys: ["zinger","burger","sandwich","panini","ande wala"], en: "Cafe menu: Zinger Burger PKR 500, Grilled Chicken Cheese Burger PKR 450, Ande Wala Burger PKR 250 and Panini Sandwich PKR 600.", ur: "Cafe menu: Zinger Burger Rs. 500, Grilled Chicken Cheese Burger Rs. 450, Ande Wala Burger Rs. 250 aur Panini Sandwich Rs. 600." },
  { keys: ["fries","mayo fries","masala fries","chowmein","snack","snacks"], en: "Cafe snacks: Mayo Fries PKR 230, Plain / Masala Fries PKR 180 and Chowmein PKR 600.", ur: "Cafe snacks: Mayo Fries Rs. 230, Plain / Masala Fries Rs. 180 aur Chowmein Rs. 600." },
  { keys: ["pepsi","sting","water","x-water","beverage","drink","drinks"], en: "Beverages: Pepsi PKR 100, Sting PKR 120 and X-Water PKR 60.", ur: "Drinks: Pepsi Rs. 100, Sting Rs. 120 aur X-Water Rs. 60." },
  { keys: ["food","menu","eat","khana","cafe"], en: "Here are the latest listed cafe prices: Zinger Burger PKR 500; Grilled Chicken Cheese Burger PKR 450; Ande Wala Burger PKR 250; Panini Sandwich PKR 600; Mayo Fries PKR 230; Plain / Masala Fries PKR 180; Chowmein PKR 600; Pepsi PKR 100; Sting PKR 120; X-Water PKR 60. Availability can be confirmed with the team.", ur: "Latest cafe prices: Zinger Burger Rs. 500; Grilled Chicken Cheese Burger Rs. 450; Ande Wala Burger Rs. 250; Panini Sandwich Rs. 600; Mayo Fries Rs. 230; Plain / Masala Fries Rs. 180; Chowmein Rs. 600; Pepsi Rs. 100; Sting Rs. 120; X-Water Rs. 60. Availability team se confirm kar lein." },
  { keys: ["package","price","pricing","rate","cost","kitna","qeemat"], en: "Current rates: PC Gaming PKR 200/hour, PS5 PKR 700/hour, Snooker PKR 12/minute and Table Tennis PKR 600/hour. View the Packages page for memberships and the X-Madness offer.", ur: "Current rates: PC Gaming Rs. 200/hour, PS5 Rs. 700/hour, Snooker Rs. 12/minute aur Table Tennis Rs. 600/hour. Membership aur X-Madness offer ke liye Packages page dekhein." },
  { keys: ["pool","vip","private room","room"], en: "Pool Table and VIP Private Rooms are listed as available experiences. Please message our team for current rates and availability.", ur: "Pool Table aur VIP Private Rooms available experiences mein listed hain. Current rates aur availability ke liye team ko message karein." },
  { keys: ["book","booking","reserve","reservation","availability","book kar"], en: "To book, share your activity, preferred date/time and number of players with our team on WhatsApp. They’ll confirm availability.", ur: "Booking ke liye activity, preferred date/time aur players ki tadaad WhatsApp par bhej dein. Team availability confirm kar degi." },
  { keys: ["payment","pay","cash","online","card"], en: "Payment is made at the arena counter. The website does not collect online payments.", ur: "Payment arena counter par hoti hai. Website par online payment nahi hoti." },
  { keys: ["phone","call","contact","whatsapp","number","live agent","human","person","team"], en: "You can reach the team on WhatsApp or call +92 342 2631057.", ur: "Aap WhatsApp ya call par team se rabta kar sakte hain: +92 342 2631057." },
  { keys: ["group","friend","player","people","capacity"], en: "Group size depends on the activity. Tell our team which game you’re planning and how many players are coming.", ur: "Group size activity par depend karta hai. Game aur players ki tadaad team ko bata dein." },
];

const suggestions = {
  en: ["What are the current game rates?","What is the GTA 6 offer?","Show me the cafe menu","What is X-Madness?"],
  ur: ["Games ke current rates kya hain?","GTA 6 ka offer kya hai?","Cafe menu aur prices batao","X-Madness offer kya hai?"],
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
    ? "I can help with game rates, GTA 6 sessions, PC memberships, the cafe menu, special offers, timings, location and bookings. Try a suggested question or chat with our team."
    : "Main game rates, GTA 6 sessions, PC memberships, cafe menu, special offers, timings, location aur booking mein help kar sakta hoon. Suggested sawal try karein ya team se baat karein.";
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
        <span className="lx-chat-launcher-icon">{open ? <ChevronDown size={21}/> : <MessageCircle size={21}/>}</span>
        {!open && <span className="lx-chat-launcher-copy"><strong>Need a hand?</strong><small>Chat with our team</small></span>}
        {!open && <span className="lx-chat-launcher-status" aria-label="Online"/>}
      </button>
    </div>
  );
}
