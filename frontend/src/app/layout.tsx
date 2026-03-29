import type { Metadata } from "next";
import { DM_Sans, Fraunces } from "next/font/google";
import { ScrollLandingOnReload } from "@/components/ScrollLandingOnReload";
import "./globals.css";

const dmSans = DM_Sans({
  subsets: ["latin"],
  variable: "--font-sans",
  display: "swap",
});

const fraunces = Fraunces({
  subsets: ["latin"],
  variable: "--font-serif",
  display: "swap",
});

export const metadata: Metadata = {
  title: {
    default: "Kana Mud Resort",
    template: "%s | Kana Mud Resort",
  },
  description:
    "Himalayan-style calm near Mussoorie—mud cottages, trails, seasonal offers, and contact details.",
  icons: {
    icon: [{ url: "/icon.svg", type: "image/svg+xml" }],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className="scroll-smooth">
      <body
        className={`${dmSans.variable} ${fraunces.variable} min-h-screen bg-stone-50 font-sans text-stone-900 antialiased`}
      >
        <ScrollLandingOnReload />
        {children}
      </body>
    </html>
  );
}
