"use client";

import Image from "next/image";
import { useCallback, useEffect } from "react";

export type LightboxItem = { src: string; alt: string };

type ImageLightboxProps = {
  open: boolean;
  onClose: () => void;
  items: LightboxItem[];
  index: number;
  onIndexChange: (i: number) => void;
};

export function ImageLightbox({
  open,
  onClose,
  items,
  index,
  onIndexChange,
}: ImageLightboxProps) {
  const total = items.length;
  const safeIndex = total ? ((index % total) + total) % total : 0;
  const current = items[safeIndex];

  const goPrev = useCallback(() => {
    if (total <= 1) return;
    onIndexChange((safeIndex - 1 + total) % total);
  }, [total, safeIndex, onIndexChange]);

  const goNext = useCallback(() => {
    if (total <= 1) return;
    onIndexChange((safeIndex + 1) % total);
  }, [total, safeIndex, onIndexChange]);

  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
      if (e.key === "ArrowLeft") goPrev();
      if (e.key === "ArrowRight") goNext();
    };
    window.addEventListener("keydown", onKey);
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      window.removeEventListener("keydown", onKey);
      document.body.style.overflow = prev;
    };
  }, [open, onClose, goPrev, goNext]);

  if (!open || !current?.src) return null;

  return (
    <div
      className="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6"
      role="dialog"
      aria-modal="true"
      aria-label="Enlarged image"
    >
      <button
        type="button"
        className="absolute inset-0 bg-black/90"
        aria-label="Close gallery"
        onClick={onClose}
      />
      <div
        className="relative z-10 flex h-[min(90vh,920px)] w-full max-w-6xl flex-col"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="mb-3 flex shrink-0 justify-end gap-2">
          <button
            type="button"
            onClick={onClose}
            className="rounded-full bg-white/15 px-4 py-2 text-sm font-medium text-white backdrop-blur hover:bg-white/25"
          >
            Close
          </button>
        </div>
        <div className="relative min-h-0 flex-1">
          {total > 1 ? (
            <>
              <button
                type="button"
                aria-label="Previous image"
                onClick={goPrev}
                className="absolute left-0 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/15 px-3 py-3 text-2xl text-white backdrop-blur hover:bg-white/25 sm:-left-2"
              >
                ‹
              </button>
              <button
                type="button"
                aria-label="Next image"
                onClick={goNext}
                className="absolute right-0 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/15 px-3 py-3 text-2xl text-white backdrop-blur hover:bg-white/25 sm:-right-2"
              >
                ›
              </button>
            </>
          ) : null}
          <div className="relative h-full w-full">
            <Image
              src={current.src}
              alt={current.alt}
              fill
              className="object-contain"
              sizes="100vw"
              priority
            />
          </div>
        </div>
        {total > 1 ? (
          <p className="mt-3 shrink-0 text-center text-sm text-white/80">
            {safeIndex + 1} / {total}
          </p>
        ) : null}
      </div>
    </div>
  );
}
