import Link from "next/link";

type Booking = {
  primaryLabel?: string | null;
  primaryUrl?: string | null;
  secondaryLabel?: string | null;
  secondaryUrl?: string | null;
  footerNote?: string | null;
};

export function SiteFooter({ booking }: { booking: Booking | null }) {
  const primaryRaw = booking?.primaryUrl?.trim();
  const primary =
    !primaryRaw || primaryRaw.includes("example.com") ? "#contact" : primaryRaw;

  return (
    <footer className="border-t border-stone-200 bg-stone-900 py-12 text-stone-300">
      <div className="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
          <p className="font-serif text-lg text-white">Kana Mud Resort</p>
          <p className="mt-2 max-w-md text-sm leading-relaxed text-stone-400">
            {booking?.footerNote ??
              "Himalayan-style calm, earth-built simplicity, and warm hospitality near Mussoorie."}
          </p>
        </div>
        <div className="flex flex-wrap gap-4">
          <Link
            href={primary}
            target={primary.startsWith("http") ? "_blank" : undefined}
            rel={primary.startsWith("http") ? "noopener noreferrer" : undefined}
            className="rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-stone-900 transition hover:bg-stone-100"
          >
            {booking?.primaryLabel ?? "Book"}
          </Link>
          {booking?.secondaryUrl ? (
            <Link
              href={booking.secondaryUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="rounded-full border border-stone-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:border-stone-400"
            >
              {booking.secondaryLabel ?? "WhatsApp concierge"}
            </Link>
          ) : null}
        </div>
      </div>
      <p className="mx-auto mt-10 max-w-6xl px-4 text-center text-xs text-stone-500 sm:px-6">
        © {new Date().getFullYear()} Kana Mud Resort. All rights reserved.
      </p>
    </footer>
  );
}
