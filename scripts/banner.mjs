import { readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const packageJson = JSON.parse(await readFile(path.join(root, 'package.json'), 'utf8'));
const target = process.argv[2];

if (!target) {
    console.error('Usage: node scripts/banner.mjs <file>');
    process.exit(1);
}

const year = new Date().getFullYear();
const banner = `/*!
 * ${packageJson.name} v${packageJson.version}
 * Homepage: ${packageJson.homepage}
 * Copyright 2014-${year} ${packageJson.author}
 * Licensed under ${packageJson.license}
*/\n`;

const file = path.resolve(process.cwd(), target);
let css = await readFile(file, 'utf8');

// Avoid stacking duplicate banners when the build is run repeatedly.
if (css.startsWith('/*!\n')) {
    css = css.replace(/^\/\*![\s\S]*?\*\/\n/, '');
}

await writeFile(file, banner + css);
console.log(`Banner added to ${path.relative(root, file)}`);
