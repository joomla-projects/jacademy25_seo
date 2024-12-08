/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  // This method is used to decode HTML entities
  const decodeHtml = (html) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = Joomla.sanitizeHtml(html);
    return textarea.value;
  };

  const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

  const compare = (original, changed) => {
    const display = changed.nextElementSibling;
    const diff = window.Diff.diffWords(original.innerHTML, changed.innerHTML);
    const fragment = document.createDocumentFragment();

    diff.forEach((part) => {
      let color = '';

      if (part.added) {
        color = isDarkMode ? '#4cfa4c' : '#a6f3a6'; // Green shades for dark mode and light mode
      }

      if (part.removed) {
        color = isDarkMode ? '#e85c5c' : '#f8cbcb'; // Red shades for dark mode and light mode
      }

      // @todo use the tag MARK here not SPAN
      const span = document.createElement('span');
      span.style.backgroundColor = color;
      span.style.borderRadius = '.2rem';
      span.appendChild(document.createTextNode(decodeHtml(part.value)));
      fragment.appendChild(span);
    });

    display.appendChild(fragment);
  };

  const onBoot = () => {
    document.querySelectorAll('.original').forEach((fragment) => compare(fragment, fragment.nextElementSibling));

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})();
