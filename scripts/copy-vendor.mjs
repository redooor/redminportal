import { access, copyFile, mkdir, readdir, readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const modules = path.join(root, 'node_modules');
const publicDir = path.join(root, 'src', 'public');

async function exists(file) {
    try {
        await access(file);
        return true;
    } catch {
        return false;
    }
}

async function copyFileTo(source, destination) {
    if (!(await exists(source))) {
        throw new Error(`Missing vendor source: ${path.relative(root, source)}`);
    }

    await mkdir(path.dirname(destination), { recursive: true });
    await copyFile(source, destination);
    console.log(`copied ${path.relative(root, source)} -> ${path.relative(root, destination)}`);
}

// Vendored js/css is copied without its sourceMappingURL comment: the maps (and the sources they point to) are not shipped.
async function copyTextWithoutSourceMap(source, destination) {
    if (!(await exists(source))) {
        throw new Error(`Missing vendor source: ${path.relative(root, source)}`);
    }

    const text = await readFile(source, 'utf8');
    const stripped = text.replace(/\n?(?:\/\*#\s*sourceMappingURL=[^*]*\*\/|\/\/[#@]\s*sourceMappingURL=.*)\s*$/m, '\n');
    await mkdir(path.dirname(destination), { recursive: true });
    await writeFile(destination, stripped);
    console.log(`copied ${path.relative(root, source)} -> ${path.relative(root, destination)}`);
}

async function copyDirectory(sourceDir, destinationDir) {
    if (!(await exists(sourceDir))) {
        throw new Error(`Missing vendor source directory: ${path.relative(root, sourceDir)}`);
    }

    const entries = await readdir(sourceDir, { withFileTypes: true });

    for (const entry of entries) {
        const source = path.join(sourceDir, entry.name);
        const destination = path.join(destinationDir, entry.name);

        if (entry.isDirectory()) {
            await copyDirectory(source, destination);
        } else if (entry.isFile()) {
            await copyFileTo(source, destination);
        }
    }
}

const vendorFiles = [
    ['bootstrap/dist/css/bootstrap.min.css', 'css/bootstrap.min.css'],
    ['bootstrap/dist/js/bootstrap.bundle.min.js', 'js/bootstrap.bundle.min.js'],
    ['jquery/dist/jquery.min.js', 'js/jquery/jquery.min.js'],
    ['jquery-ui/dist/jquery-ui.min.js', 'js/jquery-ui/jquery-ui.min.js'],
    ['@popperjs/core/dist/umd/popper.min.js', 'js/popper/popper.min.js'],
    ['@eonasdan/tempus-dominus/dist/js/tempus-dominus.min.js', 'js/tempus-dominus/tempus-dominus.min.js'],
    ['@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css', 'css/tempus-dominus/tempus-dominus.min.css'],
    ['jodit/es2021/jodit.fat.min.js', 'js/jodit/jodit.min.js'],
    ['jodit/es2021/jodit.fat.min.css', 'css/jodit/jodit.min.css'],
    ['redmaterials/dist/js/redmaterials.min.js', 'js/redmaterials.min.js'],
    ['redmaterials/dist/css/redmaterials.min.css', 'css/redmaterials.min.css'],
    ['@fortawesome/fontawesome-free/css/all.min.css', 'css/fontawesome.min.css'],
    ['typeahead.js/dist/typeahead.bundle.min.js', 'js/typeahead.bundle.min.js'],
    ['plupload/js/plupload.full.min.js', 'js/plupload.full.min.js'],
];

const vendorDirectories = [
    ['jquery-ui/dist/themes/blitzer', 'css/jquery-ui/themes/blitzer'],
    ['@fortawesome/fontawesome-free/webfonts', 'webfonts'],
];

for (const [source, destination] of vendorFiles) {
    await copyTextWithoutSourceMap(path.join(modules, source), path.join(publicDir, destination));
}

// Licence notices of the redistributed third-party code travel with the published assets.
const licenseFiles = [
    ['bootstrap/LICENSE', 'bootstrap.txt'],
    ['jquery/LICENSE.txt', 'jquery.txt'],
    ['jquery-ui/LICENSE.txt', 'jquery-ui.txt'],
    ['@popperjs/core/LICENSE.md', 'popper.txt'],
    ['@fortawesome/fontawesome-free/LICENSE.txt', 'fontawesome-free.txt'],
    ['typeahead.js/LICENSE', 'typeahead.js.txt'],
    ['plupload/license.txt', 'plupload.txt'],
    ['@eonasdan/tempus-dominus/LICENSE', 'tempus-dominus.txt'],
    ['jodit/LICENSE.txt', 'jodit.txt'],
    ['redmaterials/LICENSE.md', 'redmaterials.txt'],
];

for (const [source, destination] of licenseFiles) {
    await copyFileTo(path.join(modules, source), path.join(publicDir, 'licenses', destination));
}

for (const [source, destination] of vendorDirectories) {
    await copyDirectory(path.join(modules, source), path.join(publicDir, destination));
}

// Font Awesome's stylesheet references ../webfonts/; make sure that resolves.
const fontAwesomeCss = await readFile(path.join(publicDir, 'css/fontawesome.min.css'), 'utf8');
const fontUrls = [...fontAwesomeCss.matchAll(/url\((['"]?)([^)'"]+)\1\)/g)]
    .map((match) => match[2])
    .filter((url) => url.startsWith('../webfonts/'));

for (const url of fontUrls) {
    const fontFile = path.resolve(path.join(publicDir, 'css'), url);
    if (!(await exists(fontFile))) {
        throw new Error(`Font Awesome reference does not resolve: ${url}`);
    }
}

console.log(`verified ${fontUrls.length} Font Awesome webfont reference(s) from css/ to webfonts/`);
