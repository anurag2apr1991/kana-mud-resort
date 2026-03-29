import fs from 'node:fs';
import path from 'node:path';
import type { Core } from '@strapi/strapi';

const IMAGE_EXT = new Set(['.jpg', '.jpeg', '.png', '.webp', '.gif']);

const MARKER_REL = '.tmp/local-images-imported';

function listImages(dir: string): string[] {
  if (!fs.existsSync(dir)) {
    return [];
  }
  return fs
    .readdirSync(dir)
    .filter((f) => IMAGE_EXT.has(path.extname(f).toLowerCase()))
    .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }))
    .map((f) => path.join(dir, f));
}

function mimeFor(filePath: string): string {
  const ext = path.extname(filePath).toLowerCase();
  if (ext === '.png') return 'image/png';
  if (ext === '.webp') return 'image/webp';
  if (ext === '.gif') return 'image/gif';
  return 'image/jpeg';
}

async function uploadFile(strapi: Core.Strapi, absPath: string): Promise<number> {
  const name = path.basename(absPath);
  const stat = await fs.promises.stat(absPath);
  const upload = strapi.plugin('upload').service('upload');
  const uploaded = await upload.upload({
    data: {
      fileInfo: {
        name,
        alternativeText: name.replace(/\.[^.]+$/, ''),
      },
    },
    files: {
      filepath: absPath,
      originalFilename: name,
      mimetype: mimeFor(absPath),
      size: stat.size,
    },
  });
  const file = Array.isArray(uploaded) ? uploaded[0] : uploaded;
  if (!file?.id) {
    throw new Error(`Upload failed for ${name}`);
  }
  return file.id;
}

async function upsertHeroBackground(
  strapi: Core.Strapi,
  backgroundFileId: number,
  backgroundGalleryIds: number[]
) {
  const existing = await strapi.documents('api::hero.hero').findFirst({});
  const docId = (existing as { documentId?: string } | null)?.documentId;
  if (docId) {
    await strapi.documents('api::hero.hero').update({
      documentId: docId,
      data: { backgroundImage: backgroundFileId, backgroundGallery: backgroundGalleryIds },
    });
  } else {
    await strapi.documents('api::hero.hero').create({
      data: {
        eyebrow: 'Mussoorie · Himalayan foothills',
        title: 'Mud cottages, starlit decks, and room to exhale.',
        subtitle: 'Update this text in Content Manager → Hero.',
        ctaLabel: 'See rooms',
        ctaTargetSection: 'rooms',
        backgroundImage: backgroundFileId,
        backgroundGallery: backgroundGalleryIds,
      },
    });
  }
}

async function updateRoomMedia(
  strapi: Core.Strapi,
  slug: string,
  featuredId: number,
  galleryIds: number[]
) {
  const row = await strapi.db.query('api::room.room').findOne({ where: { slug } });
  if (!row?.documentId) {
    strapi.log.warn(
      `[import] Room "${slug}" not found — create it (or run sample seed) before importing images.`
    );
    return;
  }
  await strapi.documents('api::room.room').update({
    documentId: row.documentId,
    data: {
      featuredImage: featuredId,
      ...(galleryIds.length ? { gallery: galleryIds } : {}),
    },
  });
}

async function replaceGalleryFromCafeteria(
  strapi: Core.Strapi,
  files: string[],
  ids: number[]
) {
  const existing = await strapi.db.query('api::photo.photo').findMany({ limit: 500 });
  for (const p of existing) {
    if (p.documentId) {
      await strapi.documents('api::photo.photo').delete({ documentId: p.documentId });
    }
  }
  for (let i = 0; i < ids.length; i += 1) {
    const caption = path.basename(files[i], path.extname(files[i])).replace(/_/g, ' ');
    await strapi.documents('api::photo.photo').create({
      data: {
        image: ids[i],
        caption,
        sortOrder: i + 1,
      },
    });
  }
}

/**
 * Reads `../Images` from the CMS folder: surroundings → Hero, Room → two room types, Cafeteria → gallery.
 * Set IMPORT_LOCAL_IMAGES=1 once; remove `.tmp/local-images-imported` to run again.
 */
export async function importLocalImagesFromDisk(strapi: Core.Strapi): Promise<void> {
  const cmsRoot = strapi.dirs?.app?.root ?? process.cwd();
  const markerPath = path.join(cmsRoot, MARKER_REL);
  const force = process.env.IMPORT_LOCAL_IMAGES_FORCE === '1';

  if (process.env.IMPORT_LOCAL_IMAGES !== '1') {
    return;
  }
  if (fs.existsSync(markerPath) && !force) {
    strapi.log.info('[import] Skipping local images (already imported). Delete .tmp/local-images-imported or set IMPORT_LOCAL_IMAGES_FORCE=1.');
    return;
  }

  const projectRoot = path.resolve(cmsRoot, '..');
  const imagesRoot = process.env.LOCAL_IMAGES_DIR
    ? path.resolve(process.env.LOCAL_IMAGES_DIR)
    : path.join(projectRoot, 'Images');

  const surroundingsDir = path.join(imagesRoot, 'surroundings');
  const roomDir = path.join(imagesRoot, 'Room');
  const cafeteriaDir = path.join(imagesRoot, 'Cafeteria');

  const surroundings = listImages(surroundingsDir);
  const roomFiles = listImages(roomDir);
  const cafeteriaFiles = listImages(cafeteriaDir);

  if (!surroundings.length) {
    strapi.log.warn(`[import] No images in ${surroundingsDir} — skipping.`);
    return;
  }
  if (roomFiles.length < 2) {
    strapi.log.warn(`[import] Need at least 2 images in ${roomDir} — skipping rooms.`);
    return;
  }
  if (!cafeteriaFiles.length) {
    strapi.log.warn(`[import] No images in ${cafeteriaDir} — skipping gallery.`);
    return;
  }

  const mid = Math.ceil(roomFiles.length / 2);
  const room1Files = roomFiles.slice(0, mid);
  const room2Files = roomFiles.slice(mid);
  const r1Featured = room1Files[0];
  const r1Gallery = room1Files.slice(1);
  const r2Featured = room2Files[0];
  const r2Gallery = room2Files.slice(1);

  strapi.log.info(`[import] Images root: ${imagesRoot}`);
  strapi.log.info(`[import] Hero ← ${path.basename(surroundings[0])}`);
  strapi.log.info(
    `[import] Room 1 (deluxe-forest-room) ← ${room1Files.length} file(s) from Room/`
  );
  strapi.log.info(
    `[import] Room 2 (mud-cottage-suite) ← ${room2Files.length} file(s) from Room/`
  );
  strapi.log.info(`[import] Gallery ← ${cafeteriaFiles.length} file(s) from Cafeteria/`);

  const surroundingsIds: number[] = [];
  for (const f of surroundings) {
    surroundingsIds.push(await uploadFile(strapi, f));
  }
  const heroId = surroundingsIds[0];
  await upsertHeroBackground(strapi, heroId, surroundingsIds);

  const r1FeatId = await uploadFile(strapi, r1Featured);
  const r1GalIds: number[] = [];
  for (const f of r1Gallery) {
    r1GalIds.push(await uploadFile(strapi, f));
  }
  await updateRoomMedia(strapi, 'deluxe-forest-room', r1FeatId, r1GalIds);

  const r2FeatId = await uploadFile(strapi, r2Featured);
  const r2GalIds: number[] = [];
  for (const f of r2Gallery) {
    r2GalIds.push(await uploadFile(strapi, f));
  }
  await updateRoomMedia(strapi, 'mud-cottage-suite', r2FeatId, r2GalIds);

  const cafIds: number[] = [];
  for (const f of cafeteriaFiles) {
    cafIds.push(await uploadFile(strapi, f));
  }
  await replaceGalleryFromCafeteria(strapi, cafeteriaFiles, cafIds);

  fs.mkdirSync(path.dirname(markerPath), { recursive: true });
  fs.writeFileSync(markerPath, new Date().toISOString(), 'utf8');
  strapi.log.info('[import] Local images applied. Media Library + Hero / Rooms / Gallery updated.');
}
