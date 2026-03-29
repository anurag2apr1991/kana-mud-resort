import { AmenityIcon } from "@/components/sections/AmenityIcon";

export type Amenity = {
  id: number;
  title: string;
  description?: string | null;
  iconKey?: string | null;
};

export function AmenitiesSection({ amenities }: { amenities: Amenity[] }) {
  return (
    <section id="amenities" className="scroll-mt-28 bg-emerald-950 py-20 text-stone-100 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-300/90">
          Experience
        </p>
        <h2 className="mt-2 font-serif text-3xl sm:text-4xl">Amenities</h2>
        <p className="mt-4 max-w-2xl text-lg text-emerald-100/90">
          A Himalayan-style retreat in spirit—wholesome meals, forest trails, crisp air, and space to do very little.
        </p>
        {amenities.length === 0 ? (
          <p className="mt-8 text-emerald-200/80">
            We are updating this list. Check back soon for the full amenity guide.
          </p>
        ) : (
          <ul className="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {amenities.map((a) => (
              <li
                key={a.id}
                className="flex gap-4 rounded-2xl bg-emerald-900/40 p-5 ring-1 ring-emerald-700/50"
              >
                <AmenityIcon iconKey={a.iconKey} />
                <div>
                  <h3 className="font-serif text-xl text-white">{a.title}</h3>
                  {a.description ? (
                    <p className="mt-2 text-sm leading-relaxed text-emerald-100/85">
                      {a.description}
                    </p>
                  ) : null}
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </section>
  );
}
