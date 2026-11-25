export const whatsappNumber = "923422631057";
export const phoneDisplay = "+92 342 2631057";
export const emailAddress = "info@levelxgamingarena.com";

export const activities = [
  {
    slug: "pc-games",
    name: "PC Games",
    category: "PC Gaming",
    price: 250,
    offer: 200,
    unit: "hour",
    image: "/img/activities/covers/pc-gaming.jpg",
    description: "Play on high-performance gaming PCs with popular competitive titles including CS2, PUBG, Valorant, DOTA and more. Ask about Monday Madness and the daily Mid Day package.",
    capacity: "1 player per station",
    features: ["High-performance gaming PCs", "CS2, PUBG, Valorant, DOTA and more", "Monday Madness: Rs. 140/hour (minimum 3 hours)", "Mid Day package: Rs. 150/hour, 9 AM–5 PM (minimum 3 hours)"],
  },
  {
    slug: "ps5",
    name: "PS5",
    category: "Console Gaming",
    price: 1000,
    offer: 650,
    unit: "hour",
    image: "/img/activities/covers/ps5.jpg",
    description: "Enjoy PlayStation 5 gaming on a big screen with two controllers included. Bring a friend, choose your game and settle in for a competitive or casual session.",
    capacity: "2 players",
    features: ["PlayStation 5 console", "Two controllers included", "Additional controller: Rs. 300/hour", "Monday Madness: Rs. 600/hour"],
  },
  {
    slug: "premium-snooker-room",
    name: "Premium Snooker Room",
    category: "Snooker",
    price: 15,
    offer: 12,
    unit: "minute",
    image: "/img/activities/covers/snooker.jpg",
    description: "Play in a premium snooker room with a professional table, cues and rests. Standard play is billed by the minute, so you pay for the time you actually play.",
    capacity: "Up to 4 players",
    features: ["Professional snooker table", "Cues and rests provided", "Standard rate: Rs. 15/minute", "Opening offer: Rs. 12/minute", "Monday Madness: Rs. 8/minute"],
  },
  {
    slug: "private-table-tennis-room",
    name: "Private Table Tennis Room",
    category: "Table Tennis",
    price: 800,
    offer: 600,
    unit: "hour",
    image: "/img/activities/covers/table-tennis.jpg",
    description: "Book your own private table tennis room with a table, bats and balls. Perfect for friendly rallies, competitive matches and group sessions.",
    capacity: "Up to 4 players",
    features: ["Private table tennis room", "Table, bats and balls included", "Hourly room booking", "Monday Madness: Rs. 500/hour"],
  },
] as const;

// These facilities are mentioned in the original Laravel public About page and
// have image assets in the legacy project. No public rates were published.
export const additionalExperiences = [
  {
    name: "Pool Table",
    category: "Cue Sports",
    description: "A relaxed cue-sports experience for friends and groups. Contact the arena team to confirm current availability and rates.",
    image: "/img/activities/pool.svg",
  },
  {
    name: "VIP Private Rooms",
    category: "Private Gaming",
    description: "A more private space to enjoy your time at the arena. Message the team for room details, availability and pricing.",
    image: "/img/activities/vip-rooms.svg",
  },
] as const;

export const hourlyPackages = [
  { name: "PC Games", regular: 250, offer: 200, monday: 140, midday: 150, unit: "per hour", image: "/img/activities/covers/pc-gaming.jpg", note: "Mid Day: 9 AM–5 PM (minimum 3 hours). Monday Madness: 12 PM–midnight (minimum 3 hours)." },
  { name: "PS5", regular: 1000, offer: 650, monday: 600, unit: "per hour", image: "/img/activities/covers/ps5.jpg", note: "Two controllers included. Extra controller: Rs. 300/hour. Monday Madness: 12 PM–midnight." },
  { name: "Premium Snooker", regular: 900, offer: 720, monday: 480, unit: "per hour equivalent", image: "/img/activities/covers/snooker.jpg", note: "Standard play is billed per minute: Rs. 15 regular / Rs. 12 opening offer. Monday Madness is Rs. 8 per minute." },
  { name: "Private Table Tennis", regular: 800, offer: 600, monday: 500, unit: "per hour", image: "/img/activities/covers/table-tennis.jpg", note: "Private room booking. Monday Madness: 12 PM–midnight." },
] as const;

export const accountPackages = [
  { name: "Account Opening", hours: "One-time", price: 1000, description: "One-time account opening charge." },
  { name: "5 Hours", hours: "5 hours", price: 900, description: "Prepaid play-time bundle." },
  { name: "8 Hours", hours: "8 hours", price: 1400, description: "Prepaid play-time bundle." },
  { name: "10 Hours", hours: "10 hours", price: 1700, description: "Prepaid play-time bundle." },
  { name: "20 Hours", hours: "20 hours", price: 3200, description: "Prepaid play-time bundle." },
] as const;

export const gallery = [
  { src: "/img/gallery/esports-arena.jpg", title: "Esports Arena" },
  { src: "/img/gallery/arcade.jpg", title: "Arcade Classics" },
  { src: "/img/gallery/racing-sim.jpg", title: "Racing Simulator" },
  { src: "/img/gallery/gaming-setup.jpg", title: "Gaming Setups" },
  { src: "/img/gallery/vr-zone.jpg", title: "VR Zone" },
] as const;

export const faqs = [
  { q: "How do I book an activity?", a: "Choose an activity and send your preferred date, time and group size to our team on WhatsApp. We will help confirm availability." },
  { q: "Do I need an account?", a: "No account is required. All booking enquiries are handled directly through WhatsApp." },
  { q: "When do I pay?", a: "Payment is made at the arena counter. No online payment is collected through this website." },
  { q: "Are the prices fixed?", a: "Standard prices and selected offers are shown on this website. Special time windows and promotions may apply; our team will confirm the applicable rate before your visit." },
  { q: "What are the opening hours?", a: "Level X Gaming Arena is open 24 hours a day, seven days a week." },
  { q: "Is table tennis available?", a: "Yes. The private table tennis room includes a table, bats and balls and is booked by the hour." },
  { q: "Can I ask about pool or VIP rooms?", a: "Yes. Contact our team on WhatsApp to confirm current availability, facilities and rates for pool and private VIP rooms." },
] as const;

export function whatsappLink(message: string) {
  return `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
}
