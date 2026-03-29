/**
 * Strapi JSON fields can be: array, single object, or double-encoded string.
 * Keys are often mistyped (title vs label, url vs href). Never throw.
 */
export type NavLink = { label: string; href: string };

function pickLabel(o: Record<string, unknown>): string {
  const direct = [o.label, o.title, o.name, o.text];
  for (const c of direct) {
    if (typeof c === "string" && c.trim()) return c.trim();
    if (typeof c === "number" && Number.isFinite(c)) return String(c);
  }
  for (const [k, v] of Object.entries(o)) {
    if (k === "href" || k === "url" || k === "link" || k === "path" || k === "hash")
      continue;
    if (typeof v === "string" && v.trim()) return v.trim();
  }
  return "";
}

function pickHref(o: Record<string, unknown>): string {
  const direct = [o.href, o.url, o.link, o.hash, o.path];
  for (const c of direct) {
    if (typeof c === "string" && c.trim()) return c.trim();
  }
  return "";
}

export function normalizeMenuLinks(raw: unknown): NavLink[] {
  if (raw == null) return [];
  let parsed: unknown = raw;
  if (typeof raw === "string") {
    try {
      parsed = JSON.parse(raw) as unknown;
    } catch {
      return [];
    }
  }
  let list: unknown[];
  if (Array.isArray(parsed)) {
    list = parsed;
  } else if (typeof parsed === "object" && parsed !== null) {
    list = [parsed];
  } else {
    return [];
  }
  const out: NavLink[] = [];
  for (const item of list) {
    if (!item || typeof item !== "object") continue;
    const o = item as Record<string, unknown>;
    const label = pickLabel(o);
    const href = pickHref(o);
    if (label && href) out.push({ label, href });
  }
  return out;
}

export function safeSiteBrandName(raw: unknown, fallback = "Kana Mud Resort"): string {
  if (typeof raw === "string") {
    const t = raw.trim();
    return t.length ? t : fallback;
  }
  if (typeof raw === "number" && Number.isFinite(raw)) return String(raw);
  return fallback;
}
