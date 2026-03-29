import qs from "qs";
import { AmenitiesSection } from "@/components/sections/AmenitiesSection";
import {
  ContactSection,
  type Contact,
} from "@/components/sections/ContactSection";
import { GallerySection } from "@/components/sections/GallerySection";
import { HeroSection, type Hero } from "@/components/sections/HeroSection";
import {
  NearbyPlacesSection,
  type NearbyPlace,
} from "@/components/sections/NearbyPlacesSection";
import { OffersSection } from "@/components/sections/OffersSection";
import { RoomsSection } from "@/components/sections/RoomsSection";
import { SiteFooter } from "@/components/sections/SiteFooter";
import { SiteHeader } from "@/components/sections/SiteHeader";
import { TestimonialsSection } from "@/components/sections/TestimonialsSection";
import type { BookingCms, SiteSettingCms } from "@/lib/cms-types";
import {
  buildCollectionQuery,
  fetchStrapiList,
  fetchStrapiSingle,
  getStrapiURL,
  type StrapiMedia,
} from "@/lib/strapi";
import type { Amenity } from "@/components/sections/AmenitiesSection";
import type { Offer } from "@/components/sections/OffersSection";
import type { Photo } from "@/components/sections/GallerySection";
import type { Room } from "@/components/sections/RoomsSection";
import type { Testimonial } from "@/components/sections/TestimonialsSection";
import type { Metadata } from "next";

export const revalidate = 60;

const populateAll = qs.stringify({ populate: "*" }, { encodeValuesOnly: true });

export async function generateMetadata(): Promise<Metadata> {
  const hero = await fetchStrapiSingle<Hero>(`/api/hero?${populateAll}`);
  const title = hero?.title
    ? `${hero.title} | Kana Mud Resort`
    : "Kana Mud Resort — Himalayan-style retreat near Mussoorie";
  const description =
    hero?.subtitle ??
    "A quiet Himalayan-style forest retreat near Mussoorie—rooms, gallery, offers, and how to reach us.";
  const bg = hero?.backgroundImage as StrapiMedia | undefined;
  const ogImage = bg?.url
    ? bg.url.startsWith("http")
      ? bg.url
      : getStrapiURL(bg.url)
    : undefined;

  return {
    title,
    description,
    metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000"),
    openGraph: {
      title,
      description,
      type: "website",
      locale: "en_IN",
      ...(ogImage ? { images: [{ url: ogImage }] } : {}),
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
    },
    robots: { index: true, follow: true },
  };
}

export default async function HomePage() {
  const [
    hero,
    rooms,
    photos,
    nearbyPlaces,
    amenities,
    testimonials,
    contact,
    offers,
    booking,
    siteSetting,
  ] = await Promise.all([
    fetchStrapiSingle<Hero>(`/api/hero?${populateAll}`),
    fetchStrapiList<Room>(`/api/rooms?${buildCollectionQuery()}`),
    fetchStrapiList<Photo>(`/api/photos?${buildCollectionQuery()}`),
    fetchStrapiList<NearbyPlace>(`/api/nearby?${buildCollectionQuery()}`),
    fetchStrapiList<Amenity>(`/api/amenities?${buildCollectionQuery()}`),
    fetchStrapiList<Testimonial>(`/api/testimonials?${buildCollectionQuery()}`),
    fetchStrapiSingle<Contact>(`/api/contact?${populateAll}`),
    fetchStrapiList<Offer>(`/api/offers?${buildCollectionQuery()}`),
    fetchStrapiSingle<BookingCms>(`/api/booking?${populateAll}`),
    fetchStrapiSingle<SiteSettingCms>(`/api/site-setting?${populateAll}`),
  ]);

  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "LodgingBusiness",
    name: hero?.title ?? "Kana Mud Resort",
    description: hero?.subtitle ?? undefined,
    ...(contact && typeof contact === "object"
      ? {
          address: contact.address
            ? { "@type": "PostalAddress", streetAddress: contact.address }
            : undefined,
          email: contact.email ?? undefined,
          telephone: contact.phone ?? undefined,
        }
      : {}),
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      <SiteHeader booking={booking} siteSetting={siteSetting} />
      <main>
        <HeroSection hero={hero} />
        <RoomsSection rooms={rooms} roomSortOrder={siteSetting?.roomSortOrder} />
        <GallerySection photos={photos} />
        <NearbyPlacesSection places={nearbyPlaces} />
        <AmenitiesSection amenities={amenities} />
        <OffersSection offers={offers} />
        <TestimonialsSection testimonials={testimonials} />
        <ContactSection contact={contact} />
      </main>
      <SiteFooter booking={booking} />
    </>
  );
}
