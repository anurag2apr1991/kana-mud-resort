import { RichText } from "@/components/RichText";
import { ImageCarousel } from "@/components/ImageCarousel";
import { ImagePlaceholder } from "@/components/ImagePlaceholder";
import type { StrapiMedia } from "@/lib/strapi";
import { unwrapMedia } from "@/lib/strapi";

export type Room = {
  id: number;
  name: string;
  slug?: string;
  shortDescription?: string | null;
  description?: unknown;
  priceLabel?: string | null;
  originalPrice?: number | null;
  discountedPrice?: number | null;
  capacity?: number | null;
  featuredImage?: StrapiMedia | { data: StrapiMedia | null } | null;
  gallery?: StrapiMedia[] | { data: StrapiMedia[] | null } | null;
};

function priceInr(value: number): string {
  return new Intl.NumberFormat("en-IN").format(value);
}

/** Strapi may return integers as numbers or strings; normalize for comparisons. */
function toPrice(value: unknown): number | null {
  if (value == null) return null;
  if (typeof value === "number" && Number.isFinite(value)) return value;
  if (typeof value === "string") {
    const n = parseInt(value.replace(/,/g, ""), 10);
    return Number.isFinite(n) ? n : null;
  }
  return null;
}

/** If admin only set discounted price, use the amount in priceLabel (e.g. "From ₹14,000 / night") as original. */
function parseRupeeFromPriceLabel(label: string | null | undefined): number | null {
  if (!label) return null;
  const m = label.match(/₹\s*([\d,]+)/);
  if (!m) return null;
  const n = parseInt(m[1].replace(/,/g, ""), 10);
  return Number.isFinite(n) ? n : null;
}

function effectivePrice(room: Room): number | null {
  const orig = toPrice(room.originalPrice) ?? parseRupeeFromPriceLabel(room.priceLabel);
  const disc = toPrice(room.discountedPrice);
  if (disc != null && orig != null && disc < orig) return disc;
  if (orig != null) return orig;
  if (disc != null) return disc;
  return null;
}

/** Strapi enum can be wrong if schema changed; avoid treating unknown values as high-to-low. */
function normalizeRoomSortOrder(
  v: unknown
): "default" | "price-low-high" | "price-high-low" {
  if (v === "price-low-high" || v === "price-high-low" || v === "default") return v;
  return "default";
}

export function RoomsSection({
  rooms,
  roomSortOrder = "default",
}: {
  rooms: Room[];
  roomSortOrder?: "default" | "price-low-high" | "price-high-low" | null;
}) {
  const sort = normalizeRoomSortOrder(roomSortOrder);
  const sortedRooms =
    sort === "default"
      ? rooms
      : [...rooms].sort((a, b) => {
          const pa = effectivePrice(a);
          const pb = effectivePrice(b);
          if (pa == null && pb == null) return 0;
          if (pa == null) return 1;
          if (pb == null) return -1;
          return sort === "price-low-high" ? pa - pb : pb - pa;
        });

  if (!rooms.length) {
    return (
      <section id="rooms" className="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
        <div className="mx-auto max-w-6xl px-4 sm:px-6">
          <h2 className="font-serif text-3xl text-stone-900 sm:text-4xl">Rooms</h2>
          <p className="mt-4 max-w-2xl text-stone-600">
            Room descriptions and rates will appear here soon. Contact us to check availability.
          </p>
        </div>
      </section>
    );
  }

  return (
    <section id="rooms" className="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800">
          Stay
        </p>
        <h2 className="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl">
          Rooms & cottages
        </h2>
        <p className="mt-4 max-w-2xl text-lg text-stone-600">
          Each space is curated for rest—earthy textures, soft light, and views you will want to wake up to.
        </p>
        <div className="mt-14 grid items-stretch gap-8 lg:grid-cols-2">
          {sortedRooms.map((room) => {
            const fi = unwrapMedia<StrapiMedia>(room.featuredImage as never);
            const img = Array.isArray(fi) ? fi[0] ?? null : fi;
            const galleryRaw = unwrapMedia<StrapiMedia>(room.gallery as never);
            const gallery = Array.isArray(galleryRaw)
              ? galleryRaw
              : galleryRaw
                ? [galleryRaw]
                : [];
            const images = img ? [img, ...gallery] : gallery;
            const originalNum = toPrice(room.originalPrice) ?? parseRupeeFromPriceLabel(room.priceLabel);
            const discountedNum = toPrice(room.discountedPrice);
            const hasDiscount =
              originalNum != null &&
              discountedNum != null &&
              discountedNum < originalNum;
            const discountPct =
              hasDiscount && originalNum! > 0
                ? Math.min(
                    100,
                    Math.max(
                      0,
                      Math.round(
                        ((originalNum! - discountedNum!) / originalNum!) * 100
                      )
                    )
                  )
                : 0;

            return (
              <article
                key={room.id}
                className="flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200/80"
              >
                <div className="relative aspect-[4/3] w-full bg-stone-200">
                  {images.length ? (
                    <ImageCarousel
                      images={images}
                      altFallback={room.name}
                      sizes="(max-width: 1024px) 100vw, 50vw"
                      className="absolute inset-0"
                    />
                  ) : (
                    <ImagePlaceholder label="Room image coming soon" />
                  )}
                </div>
                <div className="flex flex-1 flex-col p-6 sm:p-8">
                  <div className="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 className="font-serif text-2xl text-stone-900">{room.name}</h3>
                    {hasDiscount ? (
                      <span className="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-900">
                        <span className="mr-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                          {discountPct}% OFF
                        </span>
                        <span className="mr-2 text-red-700 line-through">
                          {toPrice(room.originalPrice) != null
                            ? `INR ${priceInr(originalNum!)}`
                            : room.priceLabel ?? `INR ${priceInr(originalNum!)}`}
                        </span>
                        <span>INR {priceInr(discountedNum!)} / night</span>
                      </span>
                    ) : toPrice(room.originalPrice) != null ? (
                      <span className="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-900">
                        INR {priceInr(toPrice(room.originalPrice)!)} / night
                      </span>
                    ) : room.priceLabel ? (
                      <span className="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-900">
                        {room.priceLabel}
                      </span>
                    ) : null}
                  </div>
                  {room.capacity ? (
                    <p className="mt-2 text-sm text-stone-500">
                      Sleeps up to {room.capacity} guests
                    </p>
                  ) : null}
                  {room.shortDescription ? (
                    <p className="mt-4 min-h-12 text-stone-600">{room.shortDescription}</p>
                  ) : null}
                  {room.description ? (
                    <div className="mt-4">
                      <RichText content={room.description} />
                    </div>
                  ) : null}
                  {images.length > 1 ? (
                    <p className="mt-auto pt-6 text-sm text-stone-500">{images.length} images</p>
                  ) : null}
                </div>
              </article>
            );
          })}
        </div>
      </div>
    </section>
  );
}
