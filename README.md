# Resort site (Next.js + Strapi)

Single-page resort website with Strapi as the headless CMS. Content (hero, rooms, gallery, amenities, testimonials, offers, contact, booking links) is edited in the Strapi admin.

## Prerequisites

- Node.js 20+ (22 recommended with current ESLint toolchain)
- npm

## Local setup

### 1. Strapi (`cms/`)

```bash
cd cms
cp .env.example .env   # if `.env` is missing; Strapi may have created one
npm run develop
```

- Admin: [http://localhost:1337/admin](http://localhost:1337/admin) — create the first admin user on first run.
- The app bootstraps **public read** permissions for all resort APIs so the Next.js site can fetch data without an API token.
- On first start (no sample **Deluxe forest room** and no **Monsoon long stay** offer), Strapi **seeds demo content**: rooms, gallery, amenities, testimonials, offer, contact, booking links, hero text, and Unsplash images downloaded into the Media Library. To run seed again after wiping data, delete `.tmp/data.db` and recreate the admin user, or remove both markers above from the Content Manager.

### 2. Next.js (`frontend/`)

```bash
cd frontend
cp .env.example .env.local
npm run dev
```

- Site: [http://localhost:3000](http://localhost:3000)

### 3. Run both (repo root)

```bash
npm install
npm run dev
```

## CMS content

| Admin section    | Purpose |
|-----------------|---------|
| Hero            | Eyebrow, title, subtitle, CTA, hero background image |
| Room            | Copy, price label, capacity, featured image, gallery |
| Gallery photo   | Image + caption (ordered by `sortOrder`) |
| Amenity         | Title, description, icon key |
| Testimonial     | Quote, author, rating, avatar |
| Offer           | Title, description, badge, dates, CTA, cover image |
| Contact details | Email, phone, address, hours, map embed |
| Booking links   | Primary/secondary URLs and labels |

Upload images in the **Media Library**. Strapi generates optimized sizes when responsive formats are enabled (configured in `cms/config/plugins.ts`).

## Production notes

- Set `NEXT_PUBLIC_STRAPI_URL` and `NEXT_PUBLIC_SITE_URL` to your public URLs.
- Add your Strapi host to `frontend/next.config.ts` under `images.remotePatterns` if not using localhost.
- Use HTTPS in production and restrict CORS in `cms/config/middlewares.ts` to your frontend origin.
