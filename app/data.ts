export const whatsappNumber = "923422631057";
export const phoneDisplay = "+92 342 2631057";
export const emailAddress = "info@levelxgamingarena.com";

export const activities = [
  {
    slug: "pc-games",
    name: "PC Gaming",
    category: "PC Gaming",
    price: 250,
    offer: 200,
    unit: "hour",
    image: "/img/site/pc-gaming.webp",
    description: "Play on high-performance gaming PCs with popular competitive titles including CS2, PUBG, VALORANT, DOTA and more. Ask about Monday Madness and the daily Mid Day package.",
    capacity: "1 player per station",
    features: ["High-performance gaming PCs", "CS2, PUBG, Valorant, DOTA and more", "Monday Madness: PKR 140/hour (minimum 3 hours)", "Mid Day package: PKR 150/hour, 9 AM–5 PM (minimum 3 hours)"],
  },
  {
    slug: "ps5",
    name: "PS5",
    category: "Console Gaming",
    price: 1000,
    offer: 650,
    unit: "hour",
    image: "/img/site/ps5.webp",
    description: "Enjoy PlayStation 5 gaming on a big screen with two controllers included. Bring a friend, choose your game and settle in for a competitive or casual session.",
    capacity: "2 players",
    features: ["PlayStation 5 console", "Two controllers included", "Additional controller: PKR 300/hour", "Monday Madness: PKR 600/hour"],
  },
  {
    slug: "premium-snooker-room",
    name: "Premium Snooker Room",
    category: "Snooker",
    price: 15,
    offer: 12,
    unit: "minute",
    image: "/img/site/snooker.webp",
    description: "Play in a premium snooker room with a professional table, cues and rests. Standard play is billed by the minute, so you pay for the time you actually play.",
    capacity: "Up to 4 players",
    features: ["Professional snooker table", "Cues and rests provided", "Standard rate: PKR 15/minute", "Opening offer: PKR 12/minute", "Monday Madness: PKR 8/minute"],
  },
  {
    slug: "private-table-tennis-room",
    name: "Private Table Tennis Room",
    category: "Table Tennis",
    price: 800,
    offer: 600,
    unit: "hour",
    image: "/img/site/table-tennis.webp",
    description: "Book your own private table tennis room with a table, bats and balls. Perfect for friendly rallies, competitive matches and group sessions.",
    capacity: "Up to 4 players",
    features: ["Private table tennis room", "Table, bats and balls included", "Hourly room booking", "Monday Madness: PKR 500/hour"],
  },
] as const;

// These facilities are mentioned in the original Laravel public About page and
// have image assets in the legacy project. No public rates were published.
export const additionalExperiences = [
  {
    name: "Pool Table",
    category: "Cue Sports",
    description: "A relaxed cue-sports session for friends and groups. Contact the arena team to confirm current availability and rates.",
    image: "/img/site/pool.webp",
  },
  {
    name: "VIP Private Rooms",
    category: "Private Gaming",
    description: "A more private space to enjoy your time at the arena. Message the team for room details, availability and pricing.",
    image: "/img/site/vip-room.webp",
  },
] as const;

export const hourlyPackages = [
  { name: "PC Gaming", regular: 250, offer: 200, monday: 140, midday: 150, unit: "per hour", image: "/img/site/pc-gaming.webp", note: "Mid Day: 9 AM–5 PM (minimum 3 hours). Monday Madness: 12 PM–midnight (minimum 3 hours)." },
  { name: "PS5", regular: 1000, offer: 650, monday: 600, unit: "per hour", image: "/img/site/ps5.webp", note: "Two controllers included. Extra controller: PKR 300/hour. Monday Madness: 12 PM–midnight." },
  { name: "Premium Snooker", regular: 900, offer: 720, monday: 480, unit: "per hour equivalent", image: "/img/site/snooker.webp", note: "Standard play is billed per minute: PKR 15 regular / PKR 12 opening offer. Monday Madness is PKR 8 per minute." },
  { name: "Private Table Tennis", regular: 800, offer: 600, monday: 500, unit: "per hour", image: "/img/site/table-tennis.webp", note: "Private room booking. Monday Madness: 12 PM–midnight." },
] as const;

export const accountPackages = [
  { name: "Optional Player Account", hours: "One-time", price: 1000, description: "Optional one-time player account. You can still play and enquire without opening an account." },
  { name: "5 Hours", hours: "5 hours", price: 900, description: "Prepaid play-time bundle." },
  { name: "8 Hours", hours: "8 hours", price: 1400, description: "Prepaid play-time bundle." },
  { name: "10 Hours", hours: "10 hours", price: 1700, description: "Prepaid play-time bundle." },
  { name: "20 Hours", hours: "20 hours", price: 3200, description: "Prepaid play-time bundle." },
] as const;

export const gallery = [
  { src: "/img/site/pc-gaming.webp", title: "PC Gaming" },
  { src: "/img/site/ps5.webp", title: "PlayStation 5" },
  { src: "/img/site/snooker.webp", title: "Premium Snooker Room" },
  { src: "/img/site/table-tennis.webp", title: "Private Table Tennis Room" },
  { src: "/img/site/pool.webp", title: "Pool Table" },
  { src: "/img/site/vip-room.webp", title: "VIP Private Rooms" },
] as const;

export const faqs = [
  { q: "How do I book an activity?", a: "Choose an activity and send your preferred date, time and group size to our team on WhatsApp. We will help confirm availability." },
  { q: "Do I need an account?", a: "No account is needed to play or enquire about a booking. An optional player account costs PKR 1,000 one-time; ask the team about its benefits and activation at the counter." },
  { q: "When do I pay?", a: "Pay at the arena counter. This website does not collect online payments." },
  { q: "Are the prices fixed?", a: "Published rates and selected offers are shown on the Packages page. Some offers have time windows or minimum sessions; confirm your final rate with the team before visiting." },
  { q: "What are the opening hours?", a: "Level X is open 24 hours a day, seven days a week." },
  { q: "Is table tennis available?", a: "Yes. The private table tennis room includes a table, bats and balls and is booked by the hour." },
  { q: "Can I ask about pool or VIP rooms?", a: "Yes. Contact our team on WhatsApp to confirm current availability, facilities and rates for pool and private VIP rooms." },
] as const;

export function whatsappLink(message: string) {
  return `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
}
