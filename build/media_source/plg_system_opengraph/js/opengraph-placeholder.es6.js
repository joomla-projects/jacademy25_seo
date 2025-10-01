/**
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       __DEPLOY_VERSION__
 */

const initOpengraphPlaceholder = () => {
  const maps = Joomla.getOptions("plgOgMappings", {});

  const selectorFor = (token) => {
    if (token.startsWith("field.")) {
      const n = CSS.escape(token.slice(6));
      return `[name="jform[com_fields][${n}]"]`;
    }

    if (token.startsWith("image_")) {
      return `[name="jform[images][${CSS.escape(token)}]"]`;
    }

    return `[name="jform[${CSS.escape(token)}]"]`;
  };

  const t = Joomla.Text._;
  const mapCoreNames = {
    title: t("JGLOBAL_TITLE"),
    alias: t("JFIELD_ALIAS_LABEL"),
    metadesc: t("JFIELD_META_DESCRIPTION_LABEL"),
    metakey: t("JFIELD_META_KEYWORDS_LABEL"),
    articletext: t("COM_CONTENT_FIELD_ARTICLETEXT_LABEL"),
    image_intro: t("COM_CONTENT_FIELD_INTRO_LABEL"),
    image_intro_alt:
      t("COM_CONTENT_FIELD_INTRO_LABEL") +
      " - " +
      t("COM_CONTENT_FIELD_IMAGE_ALT_LABEL"),
    image_fulltext: t("COM_CONTENT_FIELD_FULLTEXT_LABEL"),
    image_fulltext_alt:
      t("COM_CONTENT_FIELD_FULLTEXT_LABEL") +
      " - " +
      t("COM_CONTENT_FIELD_IMAGE_ALT_LABEL"),
    created_by_alias: t("COM_CONTENT_FIELD_CREATED_BY_LABEL"),
  };

  const getSourceName = (token) => {
    if (token.startsWith("field.")) {
      const fieldKey = token.slice(6).replace(/_/g, " ");
      return `Custom Field: ${fieldKey}`;
    }
    return mapCoreNames[token] || token;
  };

  const sanitizeText = (input, maxLen = 60) => {
    if (typeof input !== "string" || !input.trim()) {
      return "";
    }

    // Strip HTML tags
    const noTags = input.replace(/<[^>]*>/g, " ");

    // Decode entities
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = noTags;
    const decoded = tempDiv.textContent || tempDiv.innerText || "";

    // Normalize whitespace
    const cleaned = decoded.replace(/\s+/g, " ").trim();

    // Safe truncate
    if (cleaned.length <= maxLen) {
      return cleaned;
    }

    const cut = cleaned.lastIndexOf(" ", maxLen - 1);
    const safeCut = cut > maxLen * 0.6 ? cut : maxLen - 1;

    return cleaned.slice(0, safeCut).replace(/[.,;:\-\s]+$/, "") + "…";
  };

  const maxLen = {
    og_title: Number(maps.maxTitleLen) || 60,
    og_description: Number(maps.maxDescLen) || 160,
    og_image_alt: Number(maps.maxAltLen) || 125,
  };

  Object.entries(maps).forEach(([ogKey, token]) => {
    const ogInput = document.getElementById(`jform_attribs_${ogKey}`);
    const srcInput = document.querySelector(selectorFor(token));

    if (!ogInput || !srcInput) {
      return;
    }

    const originalPh = ogInput.placeholder;
    const inherited = Joomla.Text._("PLG_SYSTEM_OPENGRAPH_INHERITED");

    const paint = () => {
      if (ogInput.value.trim()) {
        return;
      } // user override

      let v = srcInput.value.trim();
      if (ogKey !== "og_image") {
        v = sanitizeText(v, maxLen[ogKey]);
      }

      const sourceLabel = getSourceName(token);
      ogInput.placeholder = v
        ? `${v} — ${inherited} ${sourceLabel}`
        : originalPh;
    };

    paint(); // initial render
    srcInput.addEventListener("input", paint);

    ogInput.addEventListener("input", () => {
      ogInput.placeholder = originalPh; // detach override
      srcInput.removeEventListener("input", paint);
    });
  });
};

((document) => {
  document.addEventListener("DOMContentLoaded", initOpengraphPlaceholder);
})(document);
