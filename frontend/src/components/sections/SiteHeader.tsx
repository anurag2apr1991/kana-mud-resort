import Link from "next/link";
import { normalizeMenuLinks, safeSiteBrandName } from "@/lib/menu-links";
import { safeTrimString } from "@/lib/strapi";

const defaultNav = [
  { href: "#rooms", label: "Rooms" },
  { href: "#gallery", label: "Gallery" },
  { href: "#nearby", label: "Nearby" },
  { href: "#amenities", label: "Amenities" },
  { href: "#offers", label: "Offers" },
  { href: "#testimonials", label: "Stories" },
  { href: "#contact", label: "Contact" },
];

type Booking = {
  primaryLabel?: string | null;
  primaryUrl?: string | null;
  secondaryLabel?: string | null;
  secondaryUrl?: string | null;
};

type SiteSetting = {
  brandName?: string | null;
  menuLinks?: Array<{ label?: string | null; href?: string | null }> | null;
};

export function SiteHeader({
  booking,
  siteSetting,
}: {
  booking: Booking | null;
  siteSetting: SiteSetting | null;
}) {
  const primaryRaw = safeTrimString(booking?.primaryUrl);
  const primary =
    !primaryRaw || primaryRaw.includes("example.com") ? "#contact" : primaryRaw;
  const secondary = safeTrimString(booking?.secondaryUrl);
  const brandName = safeSiteBrandName(siteSetting?.brandName);
  const navFromCms = normalizeMenuLinks(siteSetting?.menuLinks);
  const nav = navFromCms.length ? navFromCms : defaultNav;

  return (
    <header className="fixed inset-x-0 top-0 z-50 border-b border-stone-200/80 bg-stone-50/90 backdrop-blur-md">
      <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <a
          href="#hero"
          className="font-serif text-lg font-semibold tracking-tight text-stone-900 sm:text-xl"
        >
          {brandName}
        </a>
        <nav
          className="hidden items-center gap-1 text-sm text-stone-600 lg:flex"
          aria-label="Primary"
        >
          {nav.map((item, i) => (
            <a
              key={`${item.href}-${i}`}
              href={item.href}
              className="rounded-full px-3 py-1.5 transition hover:bg-stone-100 hover:text-stone-900"
            >
              {item.label}
            </a>
          ))}
        </nav>
        <div className="flex shrink-0 items-center gap-2">
          {secondary ? (
            <Link
              href={secondary}
              target="_blank"
              rel="noopener noreferrer"
              className="hidden rounded-full border border-stone-300 px-3 py-2 text-sm font-medium text-stone-800 transition hover:border-stone-400 sm:inline-flex"
            >
              {booking?.secondaryLabel ?? "WhatsApp concierge"}
            </Link>
          ) : null}
          <a
            href={primary}
            target={primary.startsWith("http") ? "_blank" : undefined}
            rel={primary.startsWith("http") ? "noopener noreferrer" : undefined}
            className="inline-flex rounded-full bg-emerald-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900"
          >
            {booking?.primaryLabel ?? "Book"}
          </a>
        </div>
      </div>
      <nav
        className="flex gap-2 overflow-x-auto border-t border-stone-100 px-4 py-2 lg:hidden"
        aria-label="Sections"
      >
        {nav.map((item, i) => (
          <a
            key={`${item.href}-${i}`}
            href={item.href}
            className="whitespace-nowrap rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-700"
          >
            {item.label}
          </a>
        ))}
      </nav>
    </header>
  );
}
