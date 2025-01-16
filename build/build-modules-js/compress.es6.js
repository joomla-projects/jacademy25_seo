const { readdir } = require('fs').promises;
const { extname } = require('path');
const { compressFile } = require('./utils/compressFile.es6.js');
const { Timer } = require('./utils/timer.es6.js');

/**
 * Get files recursively
 *
 * @param {string} path The path
 */
async function getFiles(path) {
  // Get files within the current directory
  return (await readdir(path, { withFileTypes: true, recursive: true }))
    .filter((file) => (!file.isDirectory() && ['.js', '.css'].includes(extname(file.name))))
    .map((file) => `${file.path}/${file.name}`);
}

/**
 * Method that will pre compress (gzip) all .css/.js files
 * in the templates and in the media folder
 */
module.exports.compressFiles = async (enableBrotli = false) => {
  const bench = new Timer('Gzip');
  const paths = [
    `${process.cwd()}/media`,
    `${process.cwd()}/installation/template`,
    `${process.cwd()}/templates`,
    `${process.cwd()}/administrator/templates`,
  ];

  const compressTasks = [];
  const files = await Promise.all(paths.map((path) => getFiles(`${path}`)));
  [].concat(...files).map((file) => compressTasks.push(compressFile(file, enableBrotli)));

  await Promise.all(compressTasks);
  // eslint-disable-next-line no-console
  console.log('✅ Done 👍');
  bench.stop();
};
