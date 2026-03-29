import type { Core } from '@strapi/strapi';
import { errors } from '@strapi/utils';
import { importLocalImagesFromDisk } from './bootstrap/import-local-images';
import { seedSampleResortData } from './bootstrap/seed-sample-data';

const { ValidationError } = errors;

function roomPriceInt(value: unknown): number | null {
  if (value === undefined || value === null) return null;
  if (typeof value === 'number' && Number.isFinite(value)) return value;
  if (typeof value === 'string') {
    const n = parseInt(value.replace(/,/g, ''), 10);
    return Number.isFinite(n) ? n : null;
  }
  return null;
}

/** Public read actions for all API content types (for website read-only access). */
function getPublicReadActions(strapi: Core.Strapi): string[] {
  const apiUids = Object.keys(strapi.contentTypes).filter((uid) => uid.startsWith('api::'));
  const actions = new Set<string>();
  for (const uid of apiUids) {
    actions.add(`${uid}.find`);
    actions.add(`${uid}.findOne`);
  }
  return Array.from(actions);
}

export default {
  register(/* { strapi }: { strapi: Core.Strapi } */) {},

  async bootstrap({ strapi }: { strapi: Core.Strapi }) {
    strapi.db.lifecycles.subscribe({
      models: ['api::room.room'],
      async beforeCreate(event: { params: { data?: Record<string, unknown> } }) {
        const data = event.params.data;
        if (!data) return;
        const o = roomPriceInt(data.originalPrice);
        const d = roomPriceInt(data.discountedPrice);
        if (o != null && d != null && d > o) {
          throw new ValidationError('Discounted price cannot be greater than original price.');
        }
      },
      async beforeUpdate(event: { params: { data?: Record<string, unknown>; where?: Record<string, unknown> } }) {
        const { data, where } = event.params;
        if (!data || !where) return;
        const existing = await strapi.db.query('api::room.room').findOne({ where });
        if (!existing) return;
        const o =
          data.originalPrice !== undefined
            ? roomPriceInt(data.originalPrice)
            : roomPriceInt((existing as { originalPrice?: unknown }).originalPrice);
        const d =
          data.discountedPrice !== undefined
            ? roomPriceInt(data.discountedPrice)
            : roomPriceInt((existing as { discountedPrice?: unknown }).discountedPrice);
        if (o != null && d != null && d > o) {
          throw new ValidationError('Discounted price cannot be greater than original price.');
        }
      },
    });

    const publicRole = await strapi.db.query('plugin::users-permissions.role').findOne({
      where: { type: 'public' },
    });

    if (!publicRole) {
      return;
    }

    for (const action of getPublicReadActions(strapi)) {
      const existing = await strapi.db.query('plugin::users-permissions.permission').findOne({
        where: { action, role: publicRole.id },
      });

      if (!existing) {
        await strapi.db.query('plugin::users-permissions.permission').create({
          data: { action, role: publicRole.id },
        });
      }
    }

    try {
      await seedSampleResortData(strapi);
    } catch (err) {
      strapi.log.error(`[seed] Sample data failed (site still runs): ${err}`);
    }

    try {
      await importLocalImagesFromDisk(strapi);
    } catch (err) {
      strapi.log.error(`[import] Local images failed (site still runs): ${err}`);
    }
  },
};
