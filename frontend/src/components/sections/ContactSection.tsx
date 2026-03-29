export type Contact = {
  email?: string | null;
  phone?: string | null;
  address?: string | null;
  mapEmbedUrl?: string | null;
  hours?: string | null;
};

function normalizeMapUrl(input?: string | null): string | null {
  if (!input) return null;
  const value = input.trim();
  if (!value || value.includes("<")) return null;
  if (!value.startsWith("http")) return value;
  try {
    const url = new URL(value);
    if (url.hostname.includes("google.") && url.pathname.includes("/maps")) {
      const q = url.searchParams.get("q");
      if (q) return `https://www.google.com/maps?q=${encodeURIComponent(q)}&output=embed`;
      return `${value}${value.includes("?") ? "&" : "?"}output=embed`;
    }
    return value;
  } catch {
    return value;
  }
}

export function ContactSection({ contact }: { contact: Contact | null }) {
  const hasMap =
    contact?.mapEmbedUrl &&
    contact.mapEmbedUrl.trim().length > 0 &&
    contact.mapEmbedUrl.includes("<");
  const mapSrc = normalizeMapUrl(contact?.mapEmbedUrl);

  return (
    <section id="contact" className="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <p className="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800">
          Visit
        </p>
        <h2 className="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl">
          Contact & location
        </h2>
        <p className="mt-4 max-w-2xl text-stone-600">
          Reach us by phone or email, find directions below, and plan your arrival with confidence.
        </p>
        <div className="mt-12 grid gap-10 lg:grid-cols-2 lg:items-stretch">
          <div className="space-y-6 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-stone-200/80">
            {contact?.address ? (
              <div>
                <h3 className="text-sm font-semibold uppercase tracking-wide text-stone-500">
                  Address
                </h3>
                <p className="mt-2 whitespace-pre-line text-stone-800">{contact.address}</p>
              </div>
            ) : (
              <p className="text-stone-500">Address details will appear here soon.</p>
            )}
            {contact?.phone ? (
              <div>
                <h3 className="text-sm font-semibold uppercase tracking-wide text-stone-500">
                  Phone
                </h3>
                <a
                  href={`tel:${contact.phone.replace(/\s/g, "")}`}
                  className="mt-2 inline-block text-lg font-medium text-emerald-800 hover:underline"
                >
                  {contact.phone}
                </a>
              </div>
            ) : null}
            {contact?.email ? (
              <div>
                <h3 className="text-sm font-semibold uppercase tracking-wide text-stone-500">
                  Email
                </h3>
                <a
                  href={`mailto:${contact.email}`}
                  className="mt-2 inline-block font-medium text-emerald-800 hover:underline"
                >
                  {contact.email}
                </a>
              </div>
            ) : null}
            {contact?.hours ? (
              <div>
                <h3 className="text-sm font-semibold uppercase tracking-wide text-stone-500">
                  Hours
                </h3>
                <p className="mt-2 whitespace-pre-line text-stone-700">{contact.hours}</p>
              </div>
            ) : null}
          </div>
          <div className="relative isolate min-h-[280px] w-full overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200/80 max-lg:aspect-[4/3] lg:h-full lg:min-h-[22rem]">
            {hasMap ? (
              <div
                className="absolute inset-0 [&_iframe]:!h-full [&_iframe]:!w-full [&_iframe]:max-h-none [&_iframe]:border-0"
                dangerouslySetInnerHTML={{ __html: contact!.mapEmbedUrl! }}
              />
            ) : mapSrc ? (
              <iframe
                title="Map"
                src={mapSrc}
                className="absolute inset-0 h-full w-full border-0"
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
              />
            ) : (
              <div className="flex h-full min-h-[12rem] items-center justify-center p-8 text-center text-stone-500">
                Map preview is not available yet. Use the address on the left for directions.
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
