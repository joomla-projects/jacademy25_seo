import Fs from 'node:fs/promises';
import { dirname, sep } from 'node:path';

import FsExtra from 'fs-extra';
import LightningCSS from 'lightningcss';
import Sass from 'sass-embedded';

const silenceDeprecationList = [
  `media_source${sep}templates`,
  `installation${sep}template`,
  `media_source${sep}plg_installer_webinstaller`,
  `vendor${sep}fontawesome-free`,
  `media_source${sep}system${sep}scss${sep}joomla-fontawesome.scss`,
  `media_source${sep}com_media`,
  `media_source${sep}plg_system_guidedtours${sep}scss${sep}guidedtours.scss`,
];
const shouldSilenceDeprecation = (file) => silenceDeprecationList.some((path) => new RegExp(String.raw`${path}`, 'i').test(file));
const getOutputFile = (file) => file.replace(`${sep}scss${sep}`, `${sep}css${sep}`).replace('.scss', '.css').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);

export const compile = async (file) => {
  let compiled;
  const cssFile = getOutputFile(file);
  const options = shouldSilenceDeprecation(file) ? { silenceDeprecations: ['mixed-decls', 'color-functions', 'import', 'global-builtin'] } : {};

  try {
    compiled = Sass.compile(file, options);
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(error.formatted);
    process.exitCode = 1;
  }

  // Auto prefixing
  const { code } = LightningCSS.transform({
    code: Buffer.from(compiled.css.toString()),
    minify: false,
  });

  // Ensure the folder exists or create it
  await FsExtra.mkdirs(dirname(cssFile), {});
  await Fs.writeFile(
    cssFile,
    `@charset "UTF-8";
${code}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  const cssMin = LightningCSS.transform({
    code: Buffer.from(code),
    minify: true,
    exclude: LightningCSS.Features.VendorPrefixes,
  });

  // Ensure the folder exists or create it
  FsExtra.mkdirs(dirname(cssFile.replace('.css', '.min.css')), {});
  await Fs.writeFile(
    cssFile.replace('.css', '.min.css'),
    `@charset "UTF-8";${cssMin.code}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  // eslint-disable-next-line no-console
  console.log(`✅ SCSS File compiled: ${cssFile}`);
};
