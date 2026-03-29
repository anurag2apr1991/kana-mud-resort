"use client";

import { useEffect, useMemo, useState } from "react";
import Image from "next/image";
import { ImageLightbox } from "@/components/ImageLightbox";
import type { StrapiMedia } from "@/lib/strapi";
import { mediaSrc } from "@/lib/strapi";

type ImageCarouselProps = {
  images: StrapiMedia[];
  altFallback: string;
  sizes: string;
  priority?: boolean;
  className?: string;
  showControls?: boolean;
  /** Open full-screen zoom on image tap (off for hero backgrounds). Default true. */
  enableLightbox?: boolean;
};

export function ImageCarousel({
  images,
  altFallback,
  sizes,
  priority = false,
  className,
  showControls = true,
  enableLightbox = true,
}: ImageCarouselProps) {
  const list = useMemo(() => images.filter((img) => !!mediaSrc(img, "large")), [images]);
  const [index, setIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const total = list.length;

  const lightboxItems = useMemo(
    () =>
      list
        .map((img) => ({
          src: mediaSrc(img, "large")!,
          alt: img.alternativeText || altFallback,
        }))
        .filter((x) => x.src),
    [list, altFallback]
  );

  useEffect(() => {
    setIndex(0);
  }, [total]);

  useEffect(() => {
    if (total <= 1) return;
    if (lightboxOpen) return;
    const timer = setInterval(() => {
      setIndex((prev) => (prev + 1) % total);
    }, 6000);
    return () => clearInterval(timer);
  }, [total, lightboxOpen]);

  if (!total) return null;
  const current = list[index];
  const src = mediaSrc(current, "large");
  if (!src) return null;

  return (
    <div className={className}>
      <Image
        src={src}
        alt={current.alternativeText || altFallback}
        fill
        priority={priority}
        sizes={sizes}
        className={`object-cover ${enableLightbox ? "pointer-events-none" : ""}`}
      />

      {enableLightbox ? (
        <button
          type="button"
          className="absolute inset-0 z-10 cursor-zoom-in bg-transparent"
          aria-label="View images larger"
          onClick={() => setLightboxOpen(true)}
        />
      ) : null}

      {showControls && total > 1 ? (
        <>
          <button
            type="button"
            aria-label="Previous image"
            onClick={(e) => {
              e.stopPropagation();
              setIndex((prev) => (prev - 1 + total) % total);
            }}
            className="absolute left-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/45 px-3 py-2 text-sm font-semibold text-white hover:bg-black/60"
          >
            ‹
          </button>
          <button
            type="button"
            aria-label="Next image"
            onClick={(e) => {
              e.stopPropagation();
              setIndex((prev) => (prev + 1) % total);
            }}
            className="absolute right-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/45 px-3 py-2 text-sm font-semibold text-white hover:bg-black/60"
          >
            ›
          </button>
          <div className="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 gap-2">
            {list.map((img, idx) => (
              <button
                key={img.id ?? idx}
                type="button"
                aria-label={`Go to image ${idx + 1}`}
                onClick={(e) => {
                  e.stopPropagation();
                  setIndex(idx);
                }}
                className={`h-2.5 w-2.5 rounded-full ${
                  idx === index ? "bg-white" : "bg-white/50"
                }`}
              />
            ))}
          </div>
        </>
      ) : null}

      {enableLightbox && lightboxItems.length > 0 ? (
        <ImageLightbox
          open={lightboxOpen}
          onClose={() => setLightboxOpen(false)}
          items={lightboxItems}
          index={index}
          onIndexChange={setIndex}
        />
      ) : null}
    </div>
  );
}
