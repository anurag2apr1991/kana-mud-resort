"use client";

import { useState } from "react";
import Image from "next/image";
import { ImagePlaceholder } from "@/components/ImagePlaceholder";
import type { StrapiMedia } from "@/lib/strapi";
import { mediaSrc, unwrapMedia } from "@/lib/strapi";

export type NearbyPlace = {
  id: number;
  name: string;
  distanceLabel: string;
  description?: string | null;
  image?: StrapiMedia | { data: StrapiMedia | null } | null;
};

const CARDS_PER_PAGE = 3;

function NearbyPlaceCard({ place }: { place: NearbyPlace }) {
  const raw = unwrapMedia<StrapiMedia>(place.image as never);
  const img = Array.isArray(raw) ? raw[0] : raw;
  const src = mediaSrc(img, "medium");

  return (
    <article className="overflow-hidden rounded-2xl bg-white ring-1 ring-stone-200/80">
      <div className="relative aspect-[16/10] w-full bg-stone-100">
        {src ? (
          <Image
            src={src}
            alt={img?.alternativeText || place.name}
            fill
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
            className="object-cover"
          />
        ) : (
          <ImagePlaceholder label="Place image coming soon" />
        )}
      </div>
      <div className="p-5">
        <p className="text-sm font-semibold uppercase tracking-wide text-emerald-800">
          {place.distanceLabel}
        </p>
        <h3 className="mt-2 font-serif text-xl text-stone-900">{place.name}</h3>
        {place.description ? <p className="mt-2 text-sm text-stone-600">{place.description}</p> : null}
      </div>
    </article>
  );
}

export function NearbyPlacesSection({ places }: { places: NearbyPlace[] }) {
  const [currentPage, setCurrentPage] = useState(0);

  const totalPages =
    places.length > 0 ? Math.ceil(places.length / CARDS_PER_PAGE) : 0;
  const lastPage = Math.max(0, totalPages - 1);
  const safePage = Math.min(currentPage, lastPage);
  const start = safePage * CARDS_PER_PAGE;
  const visiblePlaces = places.slice(start, start + CARDS_PER_PAGE);

  if (!places.length) {
    return (
      <section id="nearby" className="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
        <div className="mx-auto max-w-6xl px-4 sm:px-6">
          <h2 className="font-serif text-3xl text-stone-900 sm:text-4xl">Nearby places</h2>
          <p className="mt-4 text-stone-600">
            Nearby walks, villages, and viewpoints will be listed here soon.
          </p>
        </div>
      </section>
    );
  }

  return (
    <section id="nearby" className="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800">
          Explore
        </p>
        <h2 className="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl">Nearby places</h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-stone-600">
          Mussoorie, ridge walks, and the villages along the slopes are within easy reach—sunset
          viewpoints, craft corners, and day trips you can pair with slow days at the retreat.
        </p>
        {totalPages > 1 ? (
          <div className="mt-8 flex items-center justify-between">
            <p className="text-sm font-medium text-stone-600">
              Showing {safePage + 1} of {totalPages}
            </p>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 0))}
                disabled={safePage === 0}
                aria-label="Previous nearby places"
                className="h-9 w-9 rounded-full border border-stone-300 bg-white text-stone-700 transition hover:border-stone-400 disabled:cursor-not-allowed disabled:opacity-40"
              >
                {"<"}
              </button>
              <button
                type="button"
                onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages - 1))}
                disabled={safePage === totalPages - 1}
                aria-label="Next nearby places"
                className="h-9 w-9 rounded-full border border-stone-300 bg-white text-stone-700 transition hover:border-stone-400 disabled:cursor-not-allowed disabled:opacity-40"
              >
                {">"}
              </button>
            </div>
          </div>
        ) : null}
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {visiblePlaces.map((p) => (
            <NearbyPlaceCard key={p.id} place={p} />
          ))}
        </div>
      </div>
    </section>
  );
}
