"use client";

import Image from "next/image";
import { useMemo, useState } from "react";
import { ImageLightbox, type LightboxItem } from "@/components/ImageLightbox";
import { ImagePlaceholder } from "@/components/ImagePlaceholder";
import type { StrapiMedia } from "@/lib/strapi";
import { mediaSrc, unwrapMedia } from "@/lib/strapi";

export type GalleryPhoto = {
  id: number;
  caption?: string | null;
  image?: StrapiMedia | { data: StrapiMedia | null } | null;
};

export function GalleryPhotoGrid({ photos }: { photos: GalleryPhoto[] }) {
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);

  const { items, idToIndex } = useMemo(() => {
    const list: LightboxItem[] = [];
    const map = new Map<number, number>();
    for (const p of photos) {
      const raw = unwrapMedia<StrapiMedia>(p.image as never);
      const m = Array.isArray(raw) ? raw[0] : raw;
      const src = mediaSrc(m, "large");
      if (!src) continue;
      map.set(p.id, list.length);
      list.push({
        src,
        alt: m?.alternativeText || p.caption || "Gallery image",
      });
    }
    return { items: list, idToIndex: map };
  }, [photos]);

  return (
    <>
      <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {photos.map((p) => {
          const raw = unwrapMedia<StrapiMedia>(p.image as never);
          const m = Array.isArray(raw) ? raw[0] : raw;
          const src = mediaSrc(m, "large");
          const lb = idToIndex.get(p.id);
          return (
            <figure
              key={p.id}
              className="overflow-hidden rounded-2xl bg-stone-100 shadow-sm ring-1 ring-stone-200/80"
            >
              <div className="relative aspect-[4/3] w-full">
                {src ? (
                  <button
                    type="button"
                    className="group relative block h-full w-full cursor-zoom-in"
                    aria-label={p.caption ? `View larger: ${p.caption}` : "View image larger"}
                    onClick={() => {
                      if (lb !== undefined) {
                        setLightboxIndex(lb);
                        setLightboxOpen(true);
                      }
                    }}
                  >
                    <Image
                      src={src}
                      alt={m?.alternativeText || p.caption || "Gallery image"}
                      fill
                      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                      className="object-cover transition duration-300 group-hover:brightness-95"
                    />
                  </button>
                ) : (
                  <ImagePlaceholder label="Gallery image coming soon" />
                )}
              </div>
              {p.caption ? (
                <figcaption className="px-4 py-3 text-sm text-stone-600">
                  {p.caption}
                </figcaption>
              ) : null}
            </figure>
          );
        })}
      </div>
      {items.length > 0 ? (
        <ImageLightbox
          open={lightboxOpen}
          onClose={() => setLightboxOpen(false)}
          items={items}
          index={lightboxIndex}
          onIndexChange={setLightboxIndex}
        />
      ) : null}
    </>
  );
}
