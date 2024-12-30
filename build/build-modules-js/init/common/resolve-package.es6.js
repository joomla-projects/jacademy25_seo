const { existsSync, readdirSync } = require('fs-extra');

/**
 * Find full path for package file.
 * Replacement for require.resolve(), as it is broken for packages with "exports" property.
 *
 * @param {string} relativePath Relative path to the file to resolve, in format packageName/file-name.js
 * @returns {string|boolean}
 */
module.exports.resolvePackageFile = (relativePath) => {
  for (const path of module.paths) {
    const fullPath = `${path}/${relativePath}`;
    if (existsSync(fullPath)) {
      return fullPath;
    }
  }

  return false;
};

/**
 * Find a list of modules under given scope,
 * eg: @foobar will look for all submodules @foobar/foo, @foobar/bar
 *
 * @param scope
 * @returns {[]}
 */
module.exports.getPackagesUnderScope = (scope) => {
  const cmModules = new Set();

  // Get the scope roots
  const roots = [];
  for (const path of module.paths) {
    const fullPath = `${path}/${scope}`;
    if (existsSync(fullPath)) {
      roots.push(fullPath);
    }
  };

  // List of modules
  for (const rootPath of roots) {
    readdirSync(rootPath).forEach((subModule) => cmModules.add(`${scope}/${subModule}`));
  };

  return [...cmModules];
};
