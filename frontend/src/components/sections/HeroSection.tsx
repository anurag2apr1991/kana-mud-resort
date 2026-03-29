import type { StrapiMedia } from "@/lib/strapi";
import { mediaSrc, unwrapMedia } from "@/lib/strapi";
import { ImageCarousel } from "@/components/ImageCarousel";

export type Hero = {
  eyebrow?: string | null;
  title?: string | null;
  subtitle?: string | null;
  ctaLabel?: string | null;
  ctaTargetSection?: string | null;
  backgroundImage?: StrapiMedia | null;
  backgroundGallery?:
    | StrapiMedia[]
    | StrapiMedia
    | { data: StrapiMedia[] | StrapiMedia | null }
    | null;
};

export function HeroSection({ hero }: { hero: Hero | null }) {
  const bgRaw = unwrapMedia<StrapiMedia>(hero?.backgroundGallery as never);
  const gallery = Array.isArray(bgRaw) ? bgRaw : bgRaw ? [bgRaw] : [];
  const images = gallery.length
    ? gallery
    : hero?.backgroundImage
      ? [hero.backgroundImage]
      : [];
  const validImages = images.filter((img) => !!mediaSrc(img, "large"));
  const target = hero?.ctaTargetSection ?? "rooms";

  return (
    <section
      id="hero"
      className="relative flex min-h-[100svh] flex-col justify-end pb-16 pt-28 sm:pb-24"
    >
      {validImages.length ? (
        <ImageCarousel
          images={validImages}
          altFallback="Kana Mud Resort — hillside view"
          priority
          sizes="100vw"
          className="absolute inset-0"
          enableLightbox={false}
        />
      ) : (
        <div
          className="absolute inset-0 bg-gradient-to-br from-emerald-950 via-stone-800 to-stone-900"
          aria-hidden
        />
      )}
      <div
        className="absolute inset-0 bg-gradient-to-t from-stone-950/90 via-stone-900/40 to-stone-900/30"
        aria-hidden
      />
      <div className="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6">
        {hero?.eyebrow ? (
          <p className="mb-3 text-base font-semibold uppercase tracking-[0.25em] text-emerald-200/90">
            {hero.eyebrow}
          </p>
        ) : null}
        <h1 className="max-w-3xl font-serif text-4xl font-medium leading-tight text-white sm:text-5xl md:text-6xl">
          {hero?.title ?? "A quiet place to arrive and breathe."}
        </h1>
        {hero?.subtitle ? (
          <p className="mt-6 max-w-xl text-lg leading-relaxed text-stone-200 sm:text-xl">
            {hero.subtitle}
          </p>
        ) : null}
        <div className="mt-10 flex flex-wrap gap-4">
          <a
            href={`#${target}`}
            className="inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-stone-900 shadow-lg transition hover:bg-stone-100"
          >
            {hero?.ctaLabel ?? "Explore rooms"}
          </a>
          <a
            href="#contact"
            className="inline-flex rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10"
          >
            Plan your visit
          </a>
        </div>
      </div>
    </section>
  );
}
