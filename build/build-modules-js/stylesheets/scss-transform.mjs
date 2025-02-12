import Fs from 'node:fs/promises';
import { dirname, sep } from 'node:path';

import FsExtra from 'fs-extra';
import LightningCSS from 'lightningcss';
import Sass from 'sass-embedded';

export const compile = async (file) => {
  const cssFile = file
    .replace(`${sep}scss${sep}`, `${sep}css${sep}`)
    .replace('.scss', '.css')
    .replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const silenceThese =
    /media_source\/templates/.test(file) ||
    /media_source\\templates/.test(file) ||
    /installation\/template/.test(file) ||
    /installation\\template/.test(file) ||
    /media_source\/plg_installer_webinstaller/.test(file) ||
    /media_source\\plg_installer_webinstaller/.test(file) ||
    /vendor\/fontawesome-free/.test(file) ||
    /vendor\\fontawesome-free/.test(file) ||
    /media_source\/system\/scss\/joomla-fontawesome.scss/.test(file) ||
    /media_source\\system\\scss\\joomla-fontawesome.scss/.test(file) ||
    /media_source\/com_media\/scss\/media-manager.scss/.test(file) ||
    /media_source\\com_media\\scss\\media-manager.scss/.test(file) ||
    /media_source\/plg_system_guidedtours\/scss\/guidedtours.scss/.test(file) ||
    /media_source\\plg_system_guidedtours\\scss\\guidedtours.scss/.test(file);
  const options = silenceThese ? { silenceDeprecations: ['mixed-decls', 'color-functions', 'import', 'global-builtin']} : {};
  let compiled;

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
