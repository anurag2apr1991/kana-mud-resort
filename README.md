# Kana Mud Resort (WordPress)

This repository contains the **WordPress theme** and a **Docker Compose** file for local development.

See **[wordpress/README.md](wordpress/README.md)** for installation, the **Resort Home** settings, custom post types, and rebuilding theme CSS.

### Quick local run

```bash
cd wordpress
docker-compose up -d
```

Then open `http://127.0.0.1:8888` (see `wordpress/README.md` for details).

### Clean (like `mvn clean`)

Removes Tailwind cache under the theme; does not delete `node_modules`.

```bash
npm run clean
```
