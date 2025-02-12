import { writeFile } from 'node:fs/promises';
import { dirname, sep } from 'node:path';

import rtlcss from 'rtlcss';
import { ensureDir } from 'fs-extra';
import { transform as transformCss, Features } from 'lightningcss';
import * as Sass from 'sass-embedded';

export const handleScssFile = async (file) => {
  const cssFile = file
    .replace(`${sep}scss${sep}`, `${sep}css${sep}`)
    .replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`)
    .replace('.scss', '.css');
  const silenceThese = /media_source\/templates/.test(file)
    || /media_source\\templates/.test(file)
    || /installation\/template/.test(file)
    || /installation\\template/.test(file)
    || /media_source\/plg_installer_webinstaller/.test(file)
    || /media_source\\plg_installer_webinstaller/.test(file)
    || /vendor\/fontawesome-free/.test(file)
    || /vendor\\fontawesome-free/.test(file)
    || /media_source\/system\/scss\/joomla-fontawesome.scss/.test(file)
    || /media_source\\system\\scss\\joomla-fontawesome.scss/.test(file)
    || /media_source\/com_media\/scss\/media-manager.scss/.test(file)
    || /media_source\\com_media\\scss\\media-manager.scss/.test(file)
    || /media_source\/plg_system_guidedtours\/scss\/guidedtours.scss/.test(file)
    || /media_source\\plg_system_guidedtours\\scss\\guidedtours.scss/.test(file);
  const options = silenceThese ? { silenceDeprecations: ['mixed-decls', 'color-functions', 'import', 'global-builtin'] } : {};
  let compiled;

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
