import { cp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const output = path.join(root, 'dist');
const manifestPath = path.join(root, 'public/build/manifest.json');
const templatePath = path.join(root, 'resources/views/welcome.blade.php');

const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
const css = manifest['resources/css/app.css'];
const javascript = manifest['resources/js/app.js'];

if (!css?.file || !javascript?.file) {
    throw new Error('Vite manifest is missing the ClipDarija CSS or JavaScript entry.');
}

const styles = [css.file, ...(javascript.css ?? [])]
    .filter((file, index, files) => files.indexOf(file) === index)
    .map((file) => `    <link rel="stylesheet" href="./build/${file}">`)
    .join('\n');

const assets = `${styles}\n    <script type="module" src="./build/${javascript.file}"></script>`;
let html = await readFile(templatePath, 'utf8');
html = html.replace(/\s*@vite\([^\n]+\)/, `\n${assets}`);

if (html.includes('@vite(')) {
    throw new Error('The Blade asset directive was not replaced.');
}

await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
await cp(path.join(root, 'public/build'), path.join(output, 'build'), { recursive: true });
await writeFile(path.join(output, 'index.html'), html);
await writeFile(path.join(output, '.nojekyll'), '');

console.log('Static ClipDarija site generated in dist/.');
