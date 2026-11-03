import { cpSync, mkdirSync, readdirSync } from 'node:fs';

// Publish assets only. PHP sources must never be copied to static hosting.
mkdirSync('.vercel-static', { recursive: true });
for (const entry of readdirSync('public', { withFileTypes: true })) {
  if (entry.name.startsWith('.') || entry.name === 'uploads' || entry.name.endsWith('.php')) continue;
  cpSync(`public/${entry.name}`, `.vercel-static/${entry.name}`, {
    recursive: true,
    filter: (source) => !/\.(?:php\d*|phtml|phar)$/i.test(source),
  });
}
