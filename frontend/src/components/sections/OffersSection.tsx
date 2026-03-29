import Image from "next/image";
import Link from "next/link";
import { ImagePlaceholder } from "@/components/ImagePlaceholder";
import type { StrapiMedia } from "@/lib/strapi";
import { mediaSrc, unwrapMedia } from "@/lib/strapi";

export type Offer = {
  id: number;
  title: string;
  description?: string | null;
  badge?: string | null;
  validUntil?: string | null;
  ctaLabel?: string | null;
  ctaUrl?: string | null;
  coverImage?: StrapiMedia | { data: StrapiMedia | null } | null;
};

function formatDate(iso?: string | null) {
  if (!iso) return null;
  try {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return null;
    return new Intl.DateTimeFormat(undefined, {
      month: "short",
      day: "numeric",
      year: "numeric",
    }).format(d);
  } catch {
    return null;
  }
}

export function OffersSection({ offers }: { offers: Offer[] }) {
  return (
    <section id="offers" className="scroll-mt-28 bg-white py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800">
          Value
        </p>
        <h2 className="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl">
          Offers & packages
        </h2>
        {offers.length === 0 ? (
          <p className="mt-6 max-w-2xl text-stone-600">
            Seasonal packages and special rates will be listed here when available. Ask us about current offers.
          </p>
        ) : (
          <div className="mt-12 grid gap-8 lg:grid-cols-2">
            {offers.map((o) => {
              const raw = unwrapMedia<StrapiMedia>(o.coverImage as never);
              const img = Array.isArray(raw) ? raw[0] : raw;
              const src = mediaSrc(img, "medium");
              return (
                <article
                  key={o.id}
                  className="flex flex-col overflow-hidden rounded-3xl ring-1 ring-stone-200/80 sm:flex-row"
                >
                  <div className="relative aspect-[16/10] w-full bg-stone-100 sm:aspect-auto sm:w-2/5">
                    {src ? (
                      <Image
                        src={src}
                        alt={img?.alternativeText || o.title}
                        fill
                        sizes="(max-width: 1024px) 100vw, 40vw"
                        className="object-cover"
                      />
                    ) : (
                      <ImagePlaceholder label="Offer image coming soon" className="min-h-[200px]" />
                    )}
                  </div>
                  <div className="flex flex-1 flex-col p-6 sm:p-8">
                    {o.badge ? (
                      <span className="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">
                        {o.badge}
                      </span>
                    ) : null}
                    <h3 className="mt-3 font-serif text-2xl text-stone-900">{o.title}</h3>
                    {o.description ? (
                      <p className="mt-3 text-stone-600">{o.description}</p>
                    ) : null}
                    {formatDate(o.validUntil) ? (
                      <p className="mt-4 text-sm text-stone-500">
                        Valid through {formatDate(o.validUntil)}
                      </p>
                    ) : null}
                    <div className="mt-6">
                      <Link
                        href="#contact"
                        className="inline-flex rounded-full bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900"
                      >
                        Enquire now
                      </Link>
                    </div>
                  </div>
                </article>
              );
            })}
          </div>
        )}
      </div>
    </section>
  );
}
