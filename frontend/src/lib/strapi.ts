import qs from "qs";

const STRAPI =
  process.env.NEXT_PUBLIC_STRAPI_URL ?? "http://localhost:1337";

export function getStrapiURL(path = "") {
  return `${STRAPI}${path}`;
}

/** Trims strings; coerces finite numbers. Avoids `.trim()` on non-strings (throws at runtime). */
export function safeTrimString(value: unknown): string {
  if (typeof value === "string") return value.trim();
  if (typeof value === "number" && Number.isFinite(value)) return String(value);
  return "";
}

export type StrapiMedia = {
  id: number;
  documentId?: string;
  url: string;
  alternativeText?: string | null;
  width?: number;
  height?: number;
  formats?: Record<
    string,
    { url: string; width?: number; height?: number; name?: string }
  >;
};

export type StrapiCollectionResponse<T> = {
  data: T[];
  meta?: { pagination?: Record<string, number> };
};

export type StrapiSingleResponse<T> = {
  data: T | null;
};

export type BlockNode = {
  type: string;
  children?: BlockChild[];
  level?: number;
  format?: string;
};

export type BlockChild = {
  type: string;
  text?: string;
  bold?: boolean;
  italic?: boolean;
  underline?: boolean;
  strikethrough?: boolean;
  code?: boolean;
  children?: BlockChild[];
};

export function buildCollectionQuery(extra: Record<string, unknown> = {}) {
  return qs.stringify(
    {
      populate: "*",
      sort: ["sortOrder:asc"],
      ...extra,
    },
    { encodeValuesOnly: true }
  );
}

export function mediaSrc(
  media: StrapiMedia | null | undefined,
  prefer: "large" | "medium" | "small" | "thumbnail" = "large"
): string | null {
  if (!media?.url) return null;
  const fmt = media.formats?.[prefer] ?? media.formats?.medium ?? media.formats?.small;
  const path = fmt?.url ?? media.url;
  if (path.startsWith("http")) return path;
  return getStrapiURL(path);
}

export async function fetchStrapi<T>(path: string): Promise<T | null> {
  try {
    const res = await fetch(getStrapiURL(path), {
      next: { revalidate: 60 },
    });
    if (!res.ok) return null;
    return (await res.json()) as T;
  } catch {
    return null;
  }
}

export async function fetchStrapiSingle<T>(path: string): Promise<T | null> {
  try {
    const res = await fetch(getStrapiURL(path), {
      next: { revalidate: 60 },
    });
    if (res.status === 404) return null;
    if (!res.ok) return null;
    const json = (await res.json()) as { data: T | null };
    return json.data ?? null;
  } catch {
    return null;
  }
}

export async function fetchStrapiList<T>(path: string): Promise<T[]> {
  const json = await fetchStrapi<StrapiCollectionResponse<T>>(path);
  return json?.data ?? [];
}

/** Normalize Strapi v5 media (direct object or legacy `{ data }`). */
export function unwrapMedia<T extends { id?: number; url?: string }>(
  input:
    | T
    | { data: T | T[] | null }
    | T[]
    | null
    | undefined
): T | T[] | null {
  if (input == null) return null;
  if (Array.isArray(input)) return input;
  if (typeof input === "object" && "data" in input) {
    const d = (input as { data: T | T[] | null }).data;
    if (d == null) return null;
    return d;
  }
  return input as T;
}
