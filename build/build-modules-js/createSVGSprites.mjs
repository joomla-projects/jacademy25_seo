import { existsSync, readdirSync, readFileSync, writeFileSync } from "node:fs";

if (!existsSync('node_modules/@fortawesome/fontawesome-free/svgs')) throw Error(`Font Awesome not installed, run "npm install @fontawesome"`);

const symbols = [];
const files = readdirSync('node_modules/@fortawesome/fontawesome-free/svgs', { withFileTypes: true, recursive: true });

if (!files.length) throw Error('Cannot find the icons package');

for (const svg of files) {
  if (svg.isDirectory()) continue;
  const content = readFileSync(`${svg.parentPath}/${svg.name}`, {encoding: 'utf8'});
  const cleanSvgCode = /<svg\b[^>]*?(?:viewBox=\"(\b[^"]*)\")?><!--!\s\b[^>]*?>([\s\S]*?)<\/svg>/gm.exec(content);

  if (cleanSvgCode.length !== 3) throw Error('The regex for the icons failed');

  const name = svg.name.replace(/\.svg/, '');
  const viewBox = cleanSvgCode[1];
  const code = cleanSvgCode[2];

  symbols.push(`'j--${name}' => '<symbol id="j--${name}" viewbox="${viewBox}">${code}</symbol>'`);
}

// Create the PHP file
writeFileSync(
	'libraries/svg_icons.php',
	`<?php
// phpcs:disable PSR1.Files.SideEffects
\\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

return [
  ${symbols.join(',\r  ')}
];
`,
);
