import { GalleryPhotoGrid, type GalleryPhoto } from "./GalleryPhotoGrid";

export type Photo = GalleryPhoto;

export function GallerySection({ photos }: { photos: Photo[] }) {
  if (!photos.length) {
    return (
      <section id="gallery" className="scroll-mt-28 bg-white py-20 sm:py-28">
        <div className="mx-auto max-w-6xl px-4 sm:px-6">
          <h2 className="font-serif text-3xl text-stone-900 sm:text-4xl">Gallery</h2>
          <p className="mt-4 text-stone-600">
            New photos of the property will appear here soon.
          </p>
        </div>
      </section>
    );
  }

  return (
    <section id="gallery" className="scroll-mt-28 bg-white py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800">
          Moments
        </p>
        <h2 className="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl">
          Around the property
        </h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-stone-600">
          Mud walls, forest light, courtyards, and paths you will want to remember — a quiet look at
          the retreat before you arrive.
        </p>
        <GalleryPhotoGrid photos={photos} />
      </div>
    </section>
  );
}
