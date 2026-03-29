import { existsSync, rmSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const root = join(dirname(fileURLToPath(import.meta.url)), "..");

/** Build / cache dirs only — does not remove node_modules or cms/.tmp (database). */
const targets = [
  "frontend/.next",
  "frontend/out",
  "frontend/tsconfig.tsbuildinfo",
  "cms/dist",
  "cms/build",
  "cms/.strapi",
];

let removed = 0;
for (const rel of targets) {
  const abs = join(root, rel);
  if (existsSync(abs)) {
    rmSync(abs, { recursive: true, force: true });
    console.log(`clean: removed ${rel}`);
    removed += 1;
  }
}

if (removed === 0) {
  console.log("clean: nothing to remove (already clean)");
} else {
  console.log(`clean: removed ${removed} path(s)`);
}
