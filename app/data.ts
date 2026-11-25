export const whatsappNumber = "923422631057";
export const phoneDisplay = "+92 342 2631057";
export const emailAddress = "info@levelxgamingarena.com";
export const activities = [
  { slug: "pc-games", name: "PC Games", category: "PC Gaming", price: 250, offer: 200, unit: "hour", image: "/img/activities/covers/pc-gaming.jpg", description: "High-end gaming PCs running CS2, PUBG, Valorant, DOTA and more. Monday Madness and the Mid Day package are available on selected hours.", capacity: "1 player", features: ["High-performance gaming PCs", "Popular competitive titles", "Monday Madness rates", "Mid Day package"] },
  { slug: "ps5", name: "PS5", category: "Console Gaming", price: 1000, offer: 650, unit: "hour", image: "/img/activities/covers/ps5.jpg", description: "PlayStation 5 gaming with two controllers included. Bring a friend, choose your game and enjoy the big-screen experience.", capacity: "2 players", features: ["PlayStation 5 console", "Two controllers included", "Extra controller: Rs. 300/hour", "Opening offer available"] },
  { slug: "premium-snooker-room", name: "Premium Snooker Room", category: "Snooker", price: 900, offer: 720, unit: "hour", image: "/img/activities/covers/snooker.jpg", description: "A premium snooker room with a professional table, cues and everything you need for a great frame. Standard rates are billed by play time.", capacity: "Up to 4 players", features: ["Professional snooker table", "Cues and rests", "Per-minute standard billing", "Monday Madness offer"] },
  { slug: "private-table-tennis-room", name: "Private Table Tennis Room", category: "Table Tennis", price: 800, offer: 600, unit: "hour", image: "/img/activities/covers/table-tennis.jpg", description: "Enjoy a private table tennis room with your own table, bats and balls. A great option for friendly matches and group sessions.", capacity: "Up to 4 players", features: ["Private room", "Table, bats and balls", "Hourly room booking", "Monday Madness offer"] }
] as const;
export const hourlyPackages = [
  { name: "PC Games", regular: 250, offer: 200, monday: 140, midday: 150, unit: "per hour", image: "/img/activities/covers/pc-gaming.jpg", note: "Mid Day: 9 AM–5 PM (minimum 3 hours). Monday Madness: 12 PM–midnight (minimum 3 hours)." },
  { name: "PS5", regular: 1000, offer: 650, monday: 600, unit: "per hour", image: "/img/activities/covers/ps5.jpg", note: "Two controllers included. Extra controller: Rs. 300/hour. Monday Madness: 12 PM–midnight." },
  { name: "Premium Snooker", regular: 900, offer: 720, monday: 480, unit: "per hour equivalent", image: "/img/activities/covers/snooker.jpg", note: "Standard play is billed per minute. Monday Madness rate shown as an hourly equivalent." },
  { name: "Private Table Tennis", regular: 800, offer: 600, monday: 500, unit: "per hour", image: "/img/activities/covers/table-tennis.jpg", note: "Private room booking. Monday Madness: 12 PM–midnight." }
] as const;
export const accountPackages = [
  { name: "Account Opening", hours: "One-time", price: 1000, description: "One-time account opening charge." },
  { name: "5 Hours", hours: "5 hours", price: 900, description: "Prepaid play-time bundle." },
  { name: "8 Hours", hours: "8 hours", price: 1400, description: "Prepaid play-time bundle." },
  { name: "10 Hours", hours: "10 hours", price: 1700, description: "Prepaid play-time bundle." },
  { name: "20 Hours", hours: "20 hours", price: 3200, description: "Prepaid play-time bundle." }
] as const;
export const gallery = [
  { src: "/img/gallery/esports-arena.jpg", title: "Esports Arena" },
  { src: "/img/gallery/arcade.jpg", title: "Arcade Classics" },
  { src: "/img/gallery/racing-sim.jpg", title: "Racing Simulator" },
  { src: "/img/gallery/gaming-setup.jpg", title: "Gaming Setups" },
  { src: "/img/gallery/vr-zone.jpg", title: "VR Zone" }
] as const;
export const faqs = [
  { q: "How do I book an activity?", a: "Choose an activity and send your preferred date, time and group size to our team on WhatsApp. We will help confirm availability." },
  { q: "Do I need an account?", a: "No account is required. All booking enquiries are handled directly through WhatsApp." },
  { q: "When do I pay?", a: "Payment is made at the arena counter. No online payment is collected through this website." },
  { q: "Are the prices fixed?", a: "Standard prices and selected offers are shown on this website. Special time windows and promotions may apply; our team will confirm the applicable rate before your visit." },
  { q: "What are the opening hours?", a: "Level X Gaming Arena is open 24 hours a day, seven days a week." }
] as const;
export function whatsappLink(message: string) { return `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`; }
