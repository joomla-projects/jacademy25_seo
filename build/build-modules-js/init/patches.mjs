import { join } from 'node:path';
import { readFile, writeFile } from 'node:fs/promises';

const RootPath = process.cwd();

/**
 * Main method that will patch files...
 *
 * @param options The options from setting.json
 *
 * @returns {Promise}
 */
export const patchPackages = async (options) => {
  const mediaVendorPath = join(RootPath, 'media/vendor');

  // Append initialising code to the end of the Short-and-Sweet javascript
  const dest = join(mediaVendorPath, 'short-and-sweet');
  const shortandsweetPath = `${dest}/${options.settings.vendors['short-and-sweet'].js['dist/short-and-sweet.min.js']}`;
  let ShortandsweetJs = await readFile(shortandsweetPath, { encoding: 'utf8' });
  ShortandsweetJs = ShortandsweetJs.concat(`
shortAndSweet('textarea.charcount,input.charcount', {counterClassName: 'small text-muted'});
/** Repeatable */
document.addEventListener("joomla:updated", (event) => [].slice.call(event.target.querySelectorAll('textarea.charcount,input.charcount')).map((el) => shortAndSweet(el, {counterClassName: 'small text-muted'})));
`);
  await writeFile(shortandsweetPath, ShortandsweetJs, { encoding: 'utf8', mode: 0o644 });

  // Include the v5 shim for Font Awesome
  const faPath = join(mediaVendorPath, 'fontawesome-free/scss/fontawesome.scss');
  const newScss = (await readFile(faPath, { encoding: 'utf8' })).concat(`
@import 'shims';
`);
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
};
