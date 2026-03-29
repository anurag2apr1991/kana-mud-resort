import type { Core } from '@strapi/strapi';
import fs from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';

type UploadFileInput = {
  filepath: string;
  originalFilename: string;
  mimetype: string;
  size: number;
};

async function downloadToTemp(url: string, baseName: string): Promise<string> {
  const res = await fetch(url, { signal: AbortSignal.timeout(45_000) });
  if (!res.ok) {
    throw new Error(`Download failed ${res.status}: ${url}`);
  }
  const buf = Buffer.from(await res.arrayBuffer());
  const ext = path.extname(new URL(url).pathname) || '.jpg';
  const safeExt = ext.length > 5 ? '.jpg' : ext;
  const tmp = path.join(os.tmpdir(), `strapi-seed-${baseName}-${Date.now()}${safeExt}`);
  await fs.writeFile(tmp, buf);
  return tmp;
}

async function uploadImage(
  strapi: Core.Strapi,
  tmpPath: string,
  originalFilename: string,
  alt: string
): Promise<number> {
  const stat = await fs.stat(tmpPath);
  const file: UploadFileInput = {
    filepath: tmpPath,
    originalFilename,
    mimetype: 'image/jpeg',
    size: stat.size,
  };

  const uploadService = strapi.plugin('upload').service('upload');
  const uploaded = await uploadService.upload({
    data: {
      fileInfo: {
        alternativeText: alt,
        name: originalFilename,
      },
    },
    files: file,
  });

  const first = Array.isArray(uploaded) ? uploaded[0] : uploaded;
  const id = first?.id;
  if (typeof id !== 'number') {
    throw new Error('Upload did not return file id');
  }
  return id;
}

async function upsertSingleType(
  strapi: Core.Strapi,
  uid:
    | 'api::contact.contact'
    | 'api::booking.booking'
    | 'api::hero.hero'
    | 'api::site-setting.site-setting',
  data: Record<string, unknown>
): Promise<void> {
  const existing = await strapi.documents(uid).findFirst({});
  const docId = (existing as { documentId?: string } | null)?.documentId;
  if (docId) {
    await strapi.documents(uid).update({ documentId: docId, data });
  } else {
    await strapi.documents(uid).create({ data });
  }
}

async function ensureByField(
  strapi: Core.Strapi,
  uid: 'api::room.room' | 'api::offer.offer' | 'api::nearby-place.nearby-place',
  field: string,
  value: string,
  data: Record<string, unknown>
): Promise<void> {
  const existing = await strapi.db.query(uid).findOne({ where: { [field]: value } });
  if (existing) return;
  await strapi.documents(uid).create({ data });
}

async function tryUpload(
  strapi: Core.Strapi,
  url: string,
  filename: string,
  alt: string
): Promise<number | null> {
  let tmp: string | null = null;
  try {
    tmp = await downloadToTemp(url, filename.replace(/\W/g, ''));
    return await uploadImage(strapi, tmp, filename, alt);
  } catch (e) {
    strapi.log.warn(`[seed] Skipping image ${filename}: ${(e as Error).message}`);
    return null;
  } finally {
    if (tmp) {
      await fs.unlink(tmp).catch(() => undefined);
    }
  }
}

/** Sample data promised a pool; property has none—rewrite legacy row on every boot if present. */
async function replaceLegacyInfinityPoolAmenity(strapi: Core.Strapi): Promise<void> {
  const legacy = await strapi.db.query('api::amenity.amenity').findOne({
    where: { title: 'Infinity pool' },
  });
  if (!legacy) return;
  const documentId = (legacy as { documentId?: string }).documentId;
  if (!documentId) return;
  await strapi.documents('api::amenity.amenity').update({
    documentId,
    data: {
      title: 'Open-air decks & views',
      description: 'Sit-outs facing the forest—tea, silence, and long skies.',
      iconKey: 'mountain',
    },
  });
  strapi.log.info('[seed] Replaced legacy “Infinity pool” amenity (no pool on property).');
}

/** Rename Starlink → Wifi; remove Spa therapies amenity (sample data drift). */
async function normalizeWifiAndRemoveSpaAmenity(strapi: Core.Strapi): Promise<void> {
  const allAmenities = await strapi.db.query('api::amenity.amenity').findMany({ limit: 100 });
  const starlinkRows = allAmenities.filter((r: { title?: string }) =>
    (r.title ?? '').toLowerCase().includes('starlink')
  );
  for (const row of starlinkRows) {
    const documentId = (row as { documentId?: string }).documentId;
    if (!documentId) continue;
    await strapi.documents('api::amenity.amenity').update({
      documentId,
      data: {
        title: 'Wifi',
        description: 'Connectivity when you need it.',
        iconKey: 'wifi',
      },
    });
    strapi.log.info('[seed] Renamed Starlink Wi-Fi amenity to Wifi.');
  }

  const spa = await strapi.db.query('api::amenity.amenity').findOne({
    where: { title: 'Spa therapies' },
  });
  if (spa) {
    const documentId = (spa as { documentId?: string }).documentId;
    if (documentId) {
      await strapi.documents('api::amenity.amenity').delete({ documentId });
      strapi.log.info('[seed] Removed “Spa therapies” amenity.');
    }
  }

  const wellness = await strapi.db.query('api::offer.offer').findOne({
    where: { title: 'Weekend wellness reset' },
  });
  const wellnessDesc = (wellness as { description?: string | null } | null)?.description ?? '';
  if (wellness && wellnessDesc.includes('Spa therapy')) {
    const documentId = (wellness as { documentId?: string }).documentId;
    if (documentId) {
      await strapi.documents('api::offer.offer').update({
        documentId,
        data: {
          description: 'Yoga session and wholesome meal plan for 2 nights.',
        },
      });
      strapi.log.info('[seed] Updated “Weekend wellness reset” offer (removed spa wording).');
    }
  }
}

/** Legacy sample copy referenced Kanha; migrate entries to Mussoorie-area places. */
async function migrateNearbyPlacesToMussoorie(strapi: Core.Strapi): Promise<void> {
  const rows = await strapi.db.query('api::nearby-place.nearby-place').findMany({ limit: 50 });
  const kanhaRows = rows.filter((r: { name?: string }) =>
    ['Kanha National Park Gate', 'Kanha National Park (area)'].includes(r.name ?? '')
  );
  if (kanhaRows.length >= 1) {
    const documentId = (kanhaRows[0] as { documentId?: string }).documentId;
    if (documentId) {
      await strapi.documents('api::nearby-place.nearby-place').update({
        documentId,
        data: {
          name: 'Mussoorie Mall Road',
          distanceLabel: 'approx. 8 km from property',
          description: 'The ridge, cafés, and shops—an easy half-day from the retreat.',
          sortOrder: 1,
        },
      });
      strapi.log.info('[seed] Migrated Kanha sample nearby → Mussoorie Mall Road.');
    }
  }
  for (let i = 1; i < kanhaRows.length; i++) {
    const dupId = (kanhaRows[i] as { documentId?: string }).documentId;
    if (dupId) {
      await strapi.documents('api::nearby-place.nearby-place').delete({ documentId: dupId });
      strapi.log.info('[seed] Removed duplicate Kanha nearby row.');
    }
  }

  const bamni = await strapi.db.query('api::nearby-place.nearby-place').findOne({
    where: { name: 'Bamni Dadar (Sunset Point)' },
  });
  const bamniId = (bamni as { documentId?: string } | null)?.documentId;
  if (bamniId) {
    await strapi.documents('api::nearby-place.nearby-place').update({
      documentId: bamniId,
      data: {
        name: 'Lal Tibba & Landour',
        distanceLabel: 'approx. 12 km from property',
        description:
          'Views over the Doon Valley, winding lanes, and quiet café corners.',
        sortOrder: 2,
      },
    });
    strapi.log.info('[seed] Migrated Bamni Dadar nearby → Lal Tibba & Landour.');
  }

  const tribal = await strapi.db.query('api::nearby-place.nearby-place').findOne({
    where: { name: 'Tribal Craft Market' },
  });
  const tribalId = (tribal as { documentId?: string } | null)?.documentId;
  if (tribalId) {
    await strapi.documents('api::nearby-place.nearby-place').update({
      documentId: tribalId,
      data: {
        name: 'Kempty Falls circuit',
        distanceLabel: 'approx. 18 km from property',
        description:
          'Cascade walks and roadside stops—plan half a day with local drivers.',
        sortOrder: 3,
      },
    });
    strapi.log.info('[seed] Migrated Tribal Craft Market → Kempty Falls circuit.');
  }
}

async function migrateJungleSafariOffer(strapi: Core.Strapi): Promise<void> {
  const row = await strapi.db.query('api::offer.offer').findOne({
    where: { title: 'Jungle safari package' },
  });
  const documentId = (row as { documentId?: string } | null)?.documentId;
  if (!documentId) return;
  await strapi.documents('api::offer.offer').update({
    documentId,
    data: {
      title: 'Hills weekend package',
      description:
        'Two nights with breakfast, a guided nature walk, and time on the ridge.',
    },
  });
  strapi.log.info('[seed] Renamed “Jungle safari package” → Hills weekend package.');
}

async function migrateSiteBrandToKanaMud(strapi: Core.Strapi): Promise<void> {
  const row = await strapi.documents('api::site-setting.site-setting').findFirst({});
  const docId = (row as { documentId?: string } | null)?.documentId;
  if (!docId) return;
  const brandName = ((row as { brandName?: string | null }).brandName ?? '').trim();
  const legacy = new Set(['Forest & Mud', 'Forest & Mud Resort']);
  if (!legacy.has(brandName)) return;
  await strapi.documents('api::site-setting.site-setting').update({
    documentId: docId,
    data: { brandName: 'Kana Mud Resort' },
  });
  strapi.log.info('[seed] Site brand updated to Kana Mud Resort.');
}

/** Demo seed used hello@forestandmud.example; normalize for Kana Mud branding. */
async function migrateContactEmailFromForestDomain(strapi: Core.Strapi): Promise<void> {
  const contact = await strapi.documents('api::contact.contact').findFirst({});
  const docId = (contact as { documentId?: string } | null)?.documentId;
  if (!docId) return;
  const email = ((contact as { email?: string | null }).email ?? '').trim().toLowerCase();
  if (email !== 'hello@forestandmud.example') return;
  await strapi.documents('api::contact.contact').update({
    documentId: docId,
    data: { email: 'hello@kanamud.example' },
  });
  strapi.log.info('[seed] Contact email updated from legacy forestandmud.example.');
}

/** Prefer “WhatsApp concierge” for the secondary booking link when it points at WhatsApp. */
async function migrateBookingSecondaryLabelToConcierge(strapi: Core.Strapi): Promise<void> {
  const row = await strapi.documents('api::booking.booking').findFirst({});
  const docId = (row as { documentId?: string } | null)?.documentId;
  if (!docId) return;
  const secondaryUrl = ((row as { secondaryUrl?: string | null }).secondaryUrl ?? '')
    .trim()
    .toLowerCase();
  if (!secondaryUrl.includes('wa.me') && !secondaryUrl.includes('whatsapp')) return;
  const label = ((row as { secondaryLabel?: string | null }).secondaryLabel ?? '').trim();
  const legacy = new Set(['WhatsApp', 'Message', '']);
  if (!legacy.has(label)) return;
  await strapi.documents('api::booking.booking').update({
    documentId: docId,
    data: { secondaryLabel: 'WhatsApp concierge' },
  });
  strapi.log.info('[seed] Booking secondary label set to WhatsApp concierge.');
}

async function migrateHeroContactToMussoorie(strapi: Core.Strapi): Promise<void> {
  const hero = await strapi.documents('api::hero.hero').findFirst({});
  const heroId = (hero as { documentId?: string } | null)?.documentId;
  if (heroId) {
    const eyebrow = (hero as { eyebrow?: string | null }).eyebrow ?? '';
    if (eyebrow.includes('Kanha')) {
      await strapi.documents('api::hero.hero').update({
        documentId: heroId,
        data: { eyebrow: 'Mussoorie · Himalayan foothills' },
      });
      strapi.log.info('[seed] Hero eyebrow updated to Mussoorie.');
    }
  }
  const contact = await strapi.documents('api::contact.contact').findFirst({});
  const contactId = (contact as { documentId?: string } | null)?.documentId;
  if (contactId) {
    const addr = (contact as { address?: string | null }).address ?? '';
    if (addr.includes('Kanha') || addr.includes('Madhya Pradesh')) {
      await strapi.documents('api::contact.contact').update({
        documentId: contactId,
        data: {
          address: 'Forest lane, near Mussoorie,\nUttarakhand, India — 248179',
          mapEmbedUrl:
            '<iframe title="Map" width="100%" height="320" style="border:0" loading="lazy" allowfullscreen src="https://www.openstreetmap.org/export/embed.html?bbox=78.02%2C30.43%2C78.08%2C30.49&amp;layer=mapnik"></iframe>',
        },
      });
      strapi.log.info('[seed] Contact address/map updated for Mussoorie.');
    }
  }
}

/**
 * Inserts demo content once (detected by the sample room slug).
 * Safe on every bootstrap; skips if that room already exists.
 */
export async function seedSampleResortData(strapi: Core.Strapi): Promise<void> {
  await replaceLegacyInfinityPoolAmenity(strapi);
  await normalizeWifiAndRemoveSpaAmenity(strapi);
  await migrateNearbyPlacesToMussoorie(strapi);
  await migrateJungleSafariOffer(strapi);
  await migrateSiteBrandToKanaMud(strapi);
  await migrateContactEmailFromForestDomain(strapi);
  await migrateBookingSecondaryLabelToConcierge(strapi);
  await migrateHeroContactToMussoorie(strapi);

  const hasSampleRoom = await strapi.db.query('api::room.room').findOne({
    where: { slug: 'deluxe-forest-room' },
  });
  const hasSampleOffer = await strapi.db.query('api::offer.offer').findOne({
    where: { title: 'Monsoon long stay' },
  });

  if (hasSampleRoom || hasSampleOffer) {
    await upsertSingleType(strapi, 'api::site-setting.site-setting', {
      brandName: 'Kana Mud Resort',
      roomSortOrder: 'default',
      menuLinks: [
        { label: 'Rooms', href: '#rooms' },
        { label: 'Gallery', href: '#gallery' },
        { label: 'Nearby', href: '#nearby' },
        { label: 'Amenities', href: '#amenities' },
        { label: 'Offers', href: '#offers' },
        { label: 'Stories', href: '#testimonials' },
        { label: 'Contact', href: '#contact' },
      ],
    });

    await ensureByField(strapi, 'api::room.room', 'slug', 'family-forest-villa', {
      name: 'Family forest villa',
      slug: 'family-forest-villa',
      shortDescription: 'Two connected rooms with a private sit-out and forest-facing lawn.',
      priceLabel: 'From ₹16,500 / night',
      capacity: 5,
      sortOrder: 3,
    });
    await ensureByField(strapi, 'api::room.room', 'slug', 'river-view-hut', {
      name: 'River view hut',
      slug: 'river-view-hut',
      shortDescription: 'Compact romantic hut with a deck for sunrise tea and birdwatching.',
      priceLabel: 'From ₹7,200 / night',
      capacity: 2,
      sortOrder: 4,
    });

    await ensureByField(strapi, 'api::offer.offer', 'title', 'Hills weekend package', {
      title: 'Hills weekend package',
      description:
        'Two nights with breakfast, a guided nature walk, and time on the ridge.',
      badge: 'Package',
      validUntil: '2026-12-20',
      ctaLabel: 'Plan this package',
      ctaUrl: '#contact',
      sortOrder: 2,
    });
    await ensureByField(strapi, 'api::offer.offer', 'title', 'Weekend wellness reset', {
      title: 'Weekend wellness reset',
      description: 'Yoga session and wholesome meal plan for 2 nights.',
      badge: 'Wellness',
      validUntil: '2026-11-15',
      ctaLabel: 'Enquire now',
      ctaUrl: '#contact',
      sortOrder: 3,
    });

    await ensureByField(strapi, 'api::nearby-place.nearby-place', 'name', 'Mussoorie Mall Road', {
      name: 'Mussoorie Mall Road',
      distanceLabel: 'approx. 8 km from property',
      description: 'The ridge, cafés, and shops—an easy half-day from the retreat.',
      sortOrder: 1,
    });
    await ensureByField(strapi, 'api::nearby-place.nearby-place', 'name', 'Lal Tibba & Landour', {
      name: 'Lal Tibba & Landour',
      distanceLabel: 'approx. 12 km from property',
      description:
        'Views over the Doon Valley, winding lanes, and quiet café corners.',
      sortOrder: 2,
    });
    await ensureByField(strapi, 'api::nearby-place.nearby-place', 'name', 'Kempty Falls circuit', {
      name: 'Kempty Falls circuit',
      distanceLabel: 'approx. 18 km from property',
      description:
        'Cascade walks and roadside stops—plan half a day with local drivers.',
      sortOrder: 3,
    });
    return;
  }

  strapi.log.info('[seed] Loading sample resort data…');

  const [
    heroBg,
    room1Feat,
    room2Feat,
    gal1,
    gal2,
    gal3,
    gal4,
    offerCover,
    av1,
    av2,
  ] = await Promise.all([
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1920&q=80',
      'sample-hero.jpg',
      'Mountain ridges at dawn — Himalayan-style retreat mood'
    ),
    tryUpload(
      strapi,
      'https://picsum.photos/seed/mussoorie-deluxe/1400/900',
      'sample-room-deluxe.jpg',
      'Deluxe room interior with king bed'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1400&q=80',
      'sample-room-cottage.jpg',
      'Cottage suite with warm lighting'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200&q=80',
      'sample-gallery-1.jpg',
      'Resort exterior'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
      'sample-gallery-2.jpg',
      'Forest valley view from the deck'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=1200&q=80',
      'sample-gallery-3.jpg',
      'Dining terrace'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=80',
      'sample-gallery-4.jpg',
      'Spa and wellness'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200&q=80',
      'sample-offer.jpg',
      'Special stay package'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80',
      'sample-guest-1.jpg',
      'Guest portrait'
    ),
    tryUpload(
      strapi,
      'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80',
      'sample-guest-2.jpg',
      'Guest portrait'
    ),
  ]);

  const room1Gallery =
    gal1 != null && gal2 != null ? [gal1, gal2] : gal1 != null ? [gal1] : [];
  const room2Gallery =
    gal3 != null && gal4 != null ? [gal3, gal4] : gal3 != null ? [gal3] : [];

  await ensureByField(strapi, 'api::room.room', 'slug', 'deluxe-forest-room', {
    name: 'Deluxe forest room',
    slug: 'deluxe-forest-room',
    shortDescription:
      'King bed, rain shower, and a private balcony facing the trees—perfect for two.',
    priceLabel: 'From ₹8,500 / night',
    capacity: 2,
    sortOrder: 1,
    ...(room1Feat != null ? { featuredImage: room1Feat } : {}),
    ...(room1Gallery.length ? { gallery: room1Gallery } : {}),
  });

  await ensureByField(strapi, 'api::room.room', 'slug', 'mud-cottage-suite', {
    name: 'Mud cottage suite',
    slug: 'mud-cottage-suite',
    shortDescription: 'Earthy lime-plaster walls, a deep soaking tub, and space for the family.',
    priceLabel: 'From ₹14,200 / night',
    capacity: 4,
    sortOrder: 2,
    ...(room2Feat != null ? { featuredImage: room2Feat } : {}),
    ...(room2Gallery.length ? { gallery: room2Gallery } : {}),
  });

  await ensureByField(strapi, 'api::room.room', 'slug', 'family-forest-villa', {
    name: 'Family forest villa',
    slug: 'family-forest-villa',
    shortDescription: 'Two connected rooms with a private sit-out and forest-facing lawn.',
    priceLabel: 'From ₹16,500 / night',
    capacity: 5,
    sortOrder: 3,
    ...(gal1 != null ? { featuredImage: gal1 } : {}),
    ...(gal2 != null ? { gallery: [gal2] } : {}),
  });

  await ensureByField(strapi, 'api::room.room', 'slug', 'river-view-hut', {
    name: 'River view hut',
    slug: 'river-view-hut',
    shortDescription: 'Compact romantic hut with a deck for sunrise tea and birdwatching.',
    priceLabel: 'From ₹7,200 / night',
    capacity: 2,
    sortOrder: 4,
    ...(gal3 != null ? { featuredImage: gal3 } : {}),
    ...(gal4 != null ? { gallery: [gal4] } : {}),
  });

  const gallerySeeds: Array<{ sortOrder: number; caption: string; id: number | null }> = [
    { sortOrder: 1, caption: 'Arrival courtyard', id: gal1 },
    { sortOrder: 2, caption: 'Deck views at golden hour', id: gal2 },
    { sortOrder: 3, caption: 'Open-air dining', id: gal3 },
    { sortOrder: 4, caption: 'Wellness & spa', id: gal4 },
  ];

  for (const g of gallerySeeds) {
    if (g.id == null) continue;
    await strapi.documents('api::photo.photo').create({
      data: {
        image: g.id,
        caption: g.caption,
        sortOrder: g.sortOrder,
      },
    });
  }

  const amenities = [
    {
      title: 'Open-air decks & views',
      description: 'Sit-outs facing the forest—tea, silence, and long skies.',
      iconKey: 'mountain',
      sortOrder: 1,
    },
    { title: 'Farm-to-table dining', description: 'Seasonal menus and local ingredients.', iconKey: 'food', sortOrder: 2 },
    { title: 'Nature walks', description: 'Guided morning trails and birding.', iconKey: 'tree', sortOrder: 3 },
    { title: 'Wifi', description: 'Connectivity when you need it.', iconKey: 'wifi', sortOrder: 4 },
    { title: 'Airport transfers', description: 'Private pickup can be arranged.', iconKey: 'car', sortOrder: 5 },
  ];

  for (const a of amenities) {
    await strapi.documents('api::amenity.amenity').create({ data: a });
  }

  await strapi.documents('api::testimonial.testimonial').create({
    data: {
      quote:
        'We cancelled our city plans and stayed an extra night. The mud cottage smelled like rain and the staff remembered every small preference.',
      authorName: 'Meera K.',
      authorTitle: 'Mumbai',
      rating: 5,
      sortOrder: 1,
      ...(av1 != null ? { avatar: av1 } : {}),
    },
  });

  await strapi.documents('api::testimonial.testimonial').create({
    data: {
      quote:
        'Kids loved the trails; we loved the silence after 9pm. Booking links in the header actually worked—rare for boutique stays.',
      authorName: 'James & Lin',
      authorTitle: 'Singapore',
      rating: 5,
      sortOrder: 2,
      ...(av2 != null ? { avatar: av2 } : {}),
    },
  });

  await ensureByField(strapi, 'api::offer.offer', 'title', 'Monsoon long stay', {
    title: 'Monsoon long stay',
    description: 'Stay 4 nights, pay for 3—includes one family-style dinner on the house.',
    badge: 'Limited',
    validUntil: '2026-09-30',
    ctaLabel: 'Check availability',
    ctaUrl: 'https://example.com/offers/monsoon',
    sortOrder: 1,
    ...(offerCover != null ? { coverImage: offerCover } : {}),
  });

  await ensureByField(strapi, 'api::offer.offer', 'title', 'Hills weekend package', {
    title: 'Hills weekend package',
    description:
      'Two nights with breakfast, a guided nature walk, and time on the ridge.',
    badge: 'Package',
    validUntil: '2026-12-20',
    ctaLabel: 'Plan this package',
    ctaUrl: '#contact',
    sortOrder: 2,
    ...(gal1 != null ? { coverImage: gal1 } : {}),
  });

  await ensureByField(strapi, 'api::offer.offer', 'title', 'Weekend wellness reset', {
    title: 'Weekend wellness reset',
    description: 'Yoga session and wholesome meal plan for 2 nights.',
    badge: 'Wellness',
    validUntil: '2026-11-15',
    ctaLabel: 'Enquire now',
    ctaUrl: '#contact',
    sortOrder: 3,
    ...(gal2 != null ? { coverImage: gal2 } : {}),
  });

  await ensureByField(strapi, 'api::nearby-place.nearby-place', 'name', 'Mussoorie Mall Road', {
    name: 'Mussoorie Mall Road',
    distanceLabel: 'approx. 8 km from property',
    description: 'The ridge, cafés, and shops—an easy half-day from the retreat.',
    sortOrder: 1,
    ...(gal3 != null ? { image: gal3 } : {}),
  });
  await ensureByField(strapi, 'api::nearby-place.nearby-place', 'name', 'Lal Tibba & Landour', {
    name: 'Lal Tibba & Landour',
    distanceLabel: 'approx. 12 km from property',
    description:
      'Views over the Doon Valley, winding lanes, and quiet café corners.',
    sortOrder: 2,
    ...(gal4 != null ? { image: gal4 } : {}),
  });
  await ensureByField(strapi, 'api::nearby-place.nearby-place', 'name', 'Kempty Falls circuit', {
    name: 'Kempty Falls circuit',
    distanceLabel: 'approx. 18 km from property',
    description:
      'Cascade walks and roadside stops—plan half a day with local drivers.',
    sortOrder: 3,
    ...(offerCover != null ? { image: offerCover } : {}),
  });

  await upsertSingleType(strapi, 'api::contact.contact', {
    email: 'hello@kanamud.example',
    phone: '+91 98765 43210',
    address: 'Forest lane, near Mussoorie,\nUttarakhand, India — 248179',
    hours: 'Reception: 7:00 – 22:00 daily\nRestaurant: 7:30 – 10:30, 13:00 – 15:30, 19:30 – 22:00',
    mapEmbedUrl:
      '<iframe title="Map" width="100%" height="320" style="border:0" loading="lazy" allowfullscreen src="https://www.openstreetmap.org/export/embed.html?bbox=78.02%2C30.43%2C78.08%2C30.49&amp;layer=mapnik"></iframe>',
  });

  await upsertSingleType(strapi, 'api::booking.booking', {
    primaryLabel: 'Book a stay',
    primaryUrl: 'https://example.com/book',
    secondaryLabel: 'WhatsApp concierge',
    secondaryUrl: 'https://wa.me/919876543210',
    footerNote: 'Sample booking URLs—replace with your real engine or channel manager in Strapi.',
  });

  await upsertSingleType(strapi, 'api::hero.hero', {
    eyebrow: 'Mussoorie · Himalayan foothills',
    title: 'Mud cottages, starlit decks, and room to exhale.',
    subtitle:
      'Slow mornings, ridge light, and deodar quiet—edit this line in your CMS when you are ready.',
    ctaLabel: 'See rooms',
    ctaTargetSection: 'rooms',
    ...(heroBg != null ? { backgroundImage: heroBg } : {}),
  });

  await upsertSingleType(strapi, 'api::site-setting.site-setting', {
    brandName: 'Kana Mud Resort',
    roomSortOrder: 'default',
    menuLinks: [
      { label: 'Rooms', href: '#rooms' },
      { label: 'Gallery', href: '#gallery' },
      { label: 'Nearby', href: '#nearby' },
      { label: 'Amenities', href: '#amenities' },
      { label: 'Offers', href: '#offers' },
      { label: 'Stories', href: '#testimonials' },
      { label: 'Contact', href: '#contact' },
    ],
  });

  strapi.log.info('[seed] Sample resort data created. Edit or delete entries in the admin.');
}
