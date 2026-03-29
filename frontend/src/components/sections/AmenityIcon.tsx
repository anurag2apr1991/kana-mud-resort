const ICONS: Record<string, string> = {
  leaf: "🌿",
  pool: "🏔️", // legacy iconKey → no pool on property
  mountain: "🏔️",
  wifi: "📶",
  food: "🍽️",
  spa: "🧘",
  car: "🚗",
  coffee: "☕",
  bed: "🛏️",
  tree: "🌳",
  star: "✨",
  default: "✦",
};

export function AmenityIcon({ iconKey }: { iconKey?: string | null }) {
  const k = (iconKey ?? "default").toLowerCase();
  const glyph = ICONS[k] ?? ICONS.default;
  // Mountain emoji often renders smaller than food/tree/wifi in system fonts — nudge size to match.
  const isMountain = k === "mountain" || k === "pool";
  return (
    <span
      className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10"
      aria-hidden
    >
      <span
        className={`inline-flex size-8 items-center justify-center leading-none [font-feature-settings:normal] ${
          isMountain ? "text-[26px]" : "text-[22px]"
        }`}
      >
        {glyph}
      </span>
    </span>
  );
}
