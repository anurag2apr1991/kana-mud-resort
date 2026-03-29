import Image from "next/image";
import type { StrapiMedia } from "@/lib/strapi";
import { mediaSrc, unwrapMedia } from "@/lib/strapi";

export type Testimonial = {
  id: number;
  quote: string;
  authorName: string;
  authorTitle?: string | null;
  rating?: number | null;
  avatar?: StrapiMedia | { data: StrapiMedia | null } | null;
};

function Stars({ n }: { n: number }) {
  const c = Math.min(5, Math.max(0, Math.round(n)));
  return (
    <span className="text-amber-400" aria-label={`${c} out of 5 stars`}>
      {"★".repeat(c)}
      <span className="text-stone-300">{"★".repeat(5 - c)}</span>
    </span>
  );
}

export function TestimonialsSection({ testimonials }: { testimonials: Testimonial[] }) {
  return (
    <section id="testimonials" className="scroll-mt-28 bg-stone-100 py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800">
          Guests
        </p>
        <h2 className="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl">
          What visitors say
        </h2>
        {testimonials.length === 0 ? (
          <p className="mt-6 max-w-2xl text-stone-600">
            Guest stories will appear here soon.
          </p>
        ) : (
          <div className="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            {testimonials.map((t) => {
              const av = unwrapMedia<StrapiMedia>(t.avatar as never) as StrapiMedia | null;
              const avSrc = av ? mediaSrc(av, "thumbnail") : null;
              return (
                <blockquote
                  key={t.id}
                  className="flex h-full flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-stone-200/80"
                >
                  {typeof t.rating === "number" ? (
                    <div className="mb-4 text-sm">
                      <Stars n={t.rating} />
                    </div>
                  ) : null}
                  <p className="flex-1 text-lg leading-relaxed text-stone-700">
                    &ldquo;{t.quote}&rdquo;
                  </p>
                  <footer className="mt-6 flex items-center gap-3 border-t border-stone-100 pt-6">
                    {avSrc ? (
                      <div className="relative h-12 w-12 shrink-0 overflow-hidden rounded-full bg-stone-200">
                        <Image
                          src={avSrc}
                          alt=""
                          fill
                          sizes="48px"
                          className="object-cover"
                        />
                      </div>
                    ) : (
                      <div
                        className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-900"
                        aria-hidden
                      >
                        {t.authorName.slice(0, 1)}
                      </div>
                    )}
                    <div>
                      <cite className="not-italic font-semibold text-stone-900">
                        {t.authorName}
                      </cite>
                      {t.authorTitle ? (
                        <p className="text-sm text-stone-500">{t.authorTitle}</p>
                      ) : null}
                    </div>
                  </footer>
                </blockquote>
              );
            })}
          </div>
        )}
      </div>
    </section>
  );
}
