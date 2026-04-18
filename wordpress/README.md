# Kana Mud Resort — WordPress

This folder contains a **full WordPress theme** for the resort landing site (converted from the Next.js + Strapi stack in `../frontend` and `../cms`).

- **Theme path:** `wp-content/themes/kana-mud-resort/`
- **Requirements:** WordPress 6.x, PHP 8.0+, MySQL or MariaDB

---

## Quick start (Docker)

From this directory:

```bash
cd wordpress
docker-compose up -d
```

Open **http://127.0.0.1:8888** (host port `8888` → container `80`). If the port is in use, edit `docker-compose.yml` and change the mapping (e.g. `8890:80`).

Default database credentials (see `docker-compose.yml`):

| Setting | Value |
|--------|--------|
| Database | `wordpress` |
| User | `wordpress` |
| Password | `wordpress` |

Stop containers:

```bash
docker-compose down
```

Remove the database volume as well (fresh DB next time):

```bash
docker-compose down -v
```

**Colima:** If Docker reports a missing socket, start the runtime first: `colima start`.

---

## Install on existing hosting

1. Copy `wp-content/themes/kana-mud-resort` into your WordPress installation’s `wp-content/themes/` directory.
2. In **wp-admin → Appearance → Themes**, activate **Kana Mud Resort**.
3. **Settings → Reading:** either keep “Your latest posts” or set a static front page — the theme’s `index.php` / `front-page.php` render the same one-page layout.

---

## Configure the site

### Appearance → Resort Home

Central settings for content that is not stored as posts:

- **Hero:** eyebrow, title, subtitle, primary CTA, target section id, **comma-separated Media Library attachment IDs** for the background slideshow.
- **Booking:** primary/secondary button labels and URLs, footer note.
- **Contact:** email, phone, address, hours, map (Google Maps URL or full embed iframe HTML).
- **Site:** brand name, optional **menu links JSON** (array of `{ "label", "href" }`), room sort order (default / price low–high / high–low).

### Custom post types (left admin menu)

| Menu | Purpose |
|------|--------|
| **Rooms** | Title, excerpt (short line), editor (long description), featured image + optional extra gallery image IDs in meta. Pricing fields in **Room details** meta box. |
| **Gallery photos** | Featured image + optional caption. |
| **Nearby places** | Title, featured image, distance label, description. |
| **Amenities** | Title, icon key (e.g. `leaf`, `mountain`), description. |
| **Offers** | Title, featured image, description, badge, valid-until date. |
| **Testimonials** | Title = guest name; meta: quote, author title, rating 0–5; optional featured image as avatar. |

Use **Page Attributes → Order** to control display order where supported.

---

## Demo images (bundled in the theme)

The theme ships JPEGs under `wp-content/themes/kana-mud-resort/assets/images/demo/`.
If **Resort Home → hero background IDs** is empty, the hero uses those files as a slideshow.
If there are no **Gallery photos** or **Rooms** yet, the theme shows the same assets so the layout matches the Next.js experience before you upload real media.

Administrators see short amber notices on the front when demos are active. To turn off fallbacks (empty sections until content exists), add to a small custom plugin or `functions.php` snippet:

```php
add_filter( 'kmr_use_demo_assets', '__return_false' );
```

---

## Develop / rebuild CSS

The theme uses Tailwind. After changing `src/input.css` or Tailwind usage in PHP:

```bash
cd wp-content/themes/kana-mud-resort
npm install
npm run build:css
```

Output: `assets/css/main.css`.

---

## Related repo paths

| Path | Role |
|------|------|
| `../frontend/` | Original Next.js front end |
| `../cms/` | Original Strapi CMS |
| `docker-compose.yml` | Local MySQL + WordPress containers |

Content is **not** auto-migrated from Strapi; re-enter or script a one-off import if you need parity.
