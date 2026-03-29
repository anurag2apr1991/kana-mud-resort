/** Strapi single types used on the home page */
export type BookingCms = {
  primaryLabel?: string | null;
  primaryUrl?: string | null;
  secondaryLabel?: string | null;
  secondaryUrl?: string | null;
  footerNote?: string | null;
};

export type SiteSettingCms = {
  brandName?: string | null;
  menuLinks?: Array<{ label?: string | null; href?: string | null }> | null;
  roomSortOrder?: "default" | "price-low-high" | "price-high-low" | null;
};
