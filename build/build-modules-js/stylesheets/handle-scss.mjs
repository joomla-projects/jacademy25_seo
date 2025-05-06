import { writeFile } from 'node:fs/promises';
import { dirname, sep } from 'node:path';

import rtlcss from 'rtlcss';
import { ensureDir } from 'fs-extra';
import { transform as transformCss, Features } from 'lightningcss';
import * as Sass from 'sass-embedded';

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

export const handleScssFile = async (file) => {
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

  let contents = transformCss({
    code: Buffer.from(compiled.css.toString()),
    minify: false,
  }).code;

  if (cssFile.endsWith('-rtl.css')) {
    contents = rtlcss.process(contents);
  }

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile), {});
  await writeFile(
    cssFile,
    `@charset "UTF-8";
${contents}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  const cssMin = transformCss({
    code: Buffer.from(contents),
    minify: true,
    exclude: Features.VendorPrefixes,
  });

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile.replace('.css', '.min.css')), {});
  await writeFile(cssFile.replace('.css', '.min.css'), `@charset "UTF-8";${cssMin.code}`, { encoding: 'utf8', mode: 0o644 });

  // eslint-disable-next-line no-console
  console.log(`✅ SCSS File compiled: ${cssFile}`);
};
