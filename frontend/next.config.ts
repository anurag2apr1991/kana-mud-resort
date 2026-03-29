import type { NextConfig } from "next";

/** Allow `next/image` for Strapi uploads when env points at a non-default host/port. */
function strapiUploadRemotePatterns(): NonNullable<
  NonNullable<NextConfig["images"]>["remotePatterns"]
> {
  const defaults = [
    { protocol: "http" as const, hostname: "localhost", port: "1337", pathname: "/uploads/**" },
    { protocol: "http" as const, hostname: "127.0.0.1", port: "1337", pathname: "/uploads/**" },
  ];
  const base = process.env.NEXT_PUBLIC_STRAPI_URL;
  if (!base) return defaults;
  try {
    const u = new URL(base);
    const entry = {
      protocol: (u.protocol === "https:" ? "https" : "http") as "http" | "https",
      hostname: u.hostname,
      pathname: "/uploads/**" as const,
      ...(u.port ? { port: u.port } : {}),
    };
    return [entry, ...defaults];
  } catch {
    return defaults;
  }
}

const nextConfig: NextConfig = {
  images: {
    remotePatterns: strapiUploadRemotePatterns(),
  },
};

export default nextConfig;
