import type { Metadata } from "next";
import "./globals.css";
import { Header, Footer } from "./components";
export const metadata: Metadata = {
  title: { default: "Level X Gaming Arena | Game On. Level Up.", template: "%s | Level X Gaming Arena" },
  description: "Explore PC gaming, PS5, premium snooker and private table tennis at Level X Gaming Arena. Open 24/7. Book directly on WhatsApp.",
  metadataBase: new URL("https://levelxgamingarena.com"),
  icons: { icon: "/img/favicon.png" },
  openGraph: { title: "Level X Gaming Arena", description: "Your next great match starts here.", url: "https://levelxgamingarena.com", siteName: "Level X Gaming Arena", type: "website" }
};
export default function RootLayout({children}:{children:React.ReactNode}) {
 return <html lang="en"><body><Header/><main>{children}</main><Footer/></body></html>;
}
