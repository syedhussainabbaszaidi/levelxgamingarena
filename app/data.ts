export const whatsappNumber = "923422631057";
export const phoneDisplay = "+92 342 2631057";
export const emailAddress = "info@levelxgamingarena.com";

export const activities = [
  {
    slug: "pc-games",
    name: "PC Gaming",
    category: "PC Gaming",
    price: 200,
    offer: 200,
    unit: "hour",
    image: "/img/site/pc-gaming.webp",
    description: "Play on high-performance gaming PCs featuring AMD Ryzen 5 5600, NVIDIA GeForce RTX 2060 Super graphics and a 260 Hz gaming display. Popular titles include CS2, PUBG, VALORANT and DOTA.",
    capacity: "1 player per station",
    features: ["AMD Ryzen 5 5600 processor", "NVIDIA GeForce RTX 2060 Super graphics", "260 Hz gaming LCD", "HyperX Cloud Alpha headset", "Keyboard and mouse options available", "Current rate: PKR 200/hour""],
  },
  {
    slug: "ps5",
    name: "PS5",
    category: "Console Gaming",
    price: 700,
    offer: 700,
    unit: "hour",
    image: "/img/site/ps5.webp",
    description: "Enjoy PlayStation 5 gaming on a big screen with two controllers included. Bring a friend, choose your game and settle in for a competitive or casual session.",
    capacity: "2 players",
    features: ["PlayStation 5 console", "Two controllers included", "Current rate: PKR 700/hour"],
  },
  {
    slug: "premium-snooker-room",
    name: "Premium Snooker Room",
    category: "Snooker",
    price: 12,
    offer: 12,
    unit: "minute",
    image: "/img/site/snooker.webp",
    description: "Play in a premium snooker room with a professional table, cues and rests. Standard play is billed by the minute, so you pay for the time you actually play.",
    capacity: "Up to 4 players",
    features: ["Professional snooker table", "Cues and rests provided", "Current rate: PKR 12/minute"],
  },
  {
    slug: "private-table-tennis-room",
    name: "Private Table Tennis Room",
    category: "Table Tennis",
    price: 600,
    offer: 600,
    unit: "hour",
    image: "/img/site/table-tennis.webp",
    description: "Book your own private table tennis room with a table, bats and balls. Perfect for friendly rallies, competitive matches and group sessions.",
    capacity: "Up to 4 players",
    features: ["Private table tennis room", "Table, bats and balls included", "Current rate: PKR 600/hour"],
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
  { name: "PC Gaming", regular: 200, offer: 200, unit: "per hour", image: "/img/site/pc-gaming.webp", note: "AMD Ryzen 5 5600, GeForce RTX 2060 Super and 260 Hz gaming display." },
  { name: "PS5", regular: 700, offer: 700, unit: "per hour", image: "/img/site/ps5.webp", note: "Two controllers included." },
  { name: "Premium Snooker", regular: 12, offer: 12, unit: "per minute", image: "/img/site/snooker.webp", note: "Billed by the minute." },
  { name: "Private Table Tennis", regular: 600, offer: 600, unit: "per hour", image: "/img/site/table-tennis.webp", note: "Private room booking. Bats and balls included." },
] as const;

export const accountPackages = [
  { name: "5 Hours", hours: "5 hours", price: 900, description: "PC Gaming membership bundle. Free account opening." },
  { name: "8 Hours", hours: "8 hours", price: 1400, description: "PC Gaming membership bundle. Free account opening." },
  { name: "10 Hours", hours: "10 hours", price: 1700, description: "PC Gaming membership bundle. Free account opening." },
  { name: "20 Hours", hours: "20 hours", price: 3200, description: "PC Gaming membership bundle. Free account opening." },
] as const;

export const cafeMenu = [
  { category: "Burgers & sandwiches", name: "Zinger Burger", price: 500 },
  { category: "Burgers & sandwiches", name: "Grilled Chicken Cheese Burger", price: 450 },
  { category: "Burgers & sandwiches", name: "Ande Wala Burger", price: 250 },
  { category: "Burgers & sandwiches", name: "Panini Sandwich", price: 600 },
  { category: "Snacks", name: "Mayo Fries", price: 230 },
  { category: "Snacks", name: "Plain / Masala Fries", price: 180 },
  { category: "Snacks", name: "Chowmein", price: 600 },
  { category: "Beverages", name: "Pepsi", price: 100 },
  { category: "Beverages", name: "Sting", price: 120 },
  { category: "Beverages", name: "X-Water", price: 60 },
] as const;

export const gta6Packages = [
  { duration: "1 Hour", price: 1000, previousPrice: 1200 },
  { duration: "3 Hours", price: 3000 },
  { duration: "5 Hours", price: 5000 },
] as const;

export const xMadnessOffer = {
  price: 100,
  unit: "hour",
  days: "Monday to Thursday",
  hours: "9 AM to 5 PM",
} as const;

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
  { q: "Do I need an account?", a: "PC Gaming membership account opening is free. Membership bundles: 5 hours PKR 900, 8 hours PKR 1,400, 10 hours PKR 1,700 and 20 hours PKR 3,200." },
  { q: "When do I pay?", a: "Pay at the arena counter. This website does not collect online payments." },
  { q: "What are the current game rates?", a: "PC Gaming PKR 200/hour, PS5 PKR 700/hour, Snooker PKR 12/minute and Table Tennis PKR 600/hour. X-Madness PC offer is PKR 100/hour Monday to Thursday, 9 AM to 5 PM." },
  { q: "What are the opening hours?", a: "Level X is open 24 hours a day, seven days a week." },
  { q: "Is table tennis available?", a: "Yes. The private table tennis room includes a table, bats and balls and is booked by the hour." },
  { q: "Can I ask about pool or VIP rooms?", a: "Yes. Contact our team on WhatsApp to confirm current availability, facilities and rates for pool and private VIP rooms." },
] as const;

export function whatsappLink(message: string) {
  return `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
}
