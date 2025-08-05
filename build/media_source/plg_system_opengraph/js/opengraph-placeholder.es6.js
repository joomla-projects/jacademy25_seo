document.addEventListener("DOMContentLoaded", () => {
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

  function getSourceName(token) {
    if (token.startsWith("field.")) {
      const fieldKey = token.slice(6).replace(/_/g, " ");
      return `Custom Field: ${fieldKey}`;
    }

    const mapCoreNames = {
      title: "Title",
      alias: "Alias",
      metadesc: "Meta Description",
      metakey: "Meta Keywords",
      articletext: "Article Text",
      image_intro: "Intro Image",
      image_intro_alt: "Intro Image Alt",
      image_fulltext: "Fulltext Image",
      image_fulltext_alt: "Fulltext Image Alt",
      created_by_alias: "Author Alias",
    };

    return mapCoreNames[token] || token;
  }

  function sanitizeText(input, maxLen = 60) {
    if (typeof input !== "string" || !input.trim()) return "";

    // Remove HTML tags manually (just in case innerText fails)
    const noTags = input.replace(/<[^>]*>/g, " ");

    // Decode HTML entities using a temporary div
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = noTags;
    const decoded = tempDiv.textContent || tempDiv.innerText || "";

    // Normalize whitespace
    const cleaned = decoded.replace(/\s+/g, " ").trim();

    // Truncate with word boundary safety
    if (cleaned.length <= maxLen) return cleaned;

    const cut = cleaned.lastIndexOf(" ", maxLen - 1);
    const safeCut = cut > maxLen * 0.6 ? cut : maxLen - 1;
    return cleaned.slice(0, safeCut).replace(/[.,;:\-\s]+$/, "") + "…";
  }

  const maxLen = {
    og_title: Number(maps.maxTitleLen) || 60,
    og_description: Number(maps.maxDescLen) || 160,
    og_image_alt: Number(maps.maxAltLen) || 125,
  };

  Object.entries(maps).forEach(([ogKey, token]) => {
    const ogInput = document.getElementById(`jform_attribs_${ogKey}`);
    const srcInput = document.querySelector(selectorFor(token));

    if (!ogInput || !srcInput) return;

    const originalPh = ogInput.placeholder;
    const inherited = Joomla.Text._("PLG_SYSTEM_OPENGRAPH_INHERITED");
    const paint = () => {
      if (ogInput.value.trim()) return; // user override
      let v = srcInput.value.trim();

      if (ogKey !== "og_image") {
        v = sanitizeText(v, maxLen[ogKey]);
      }
      const sourceLabel = getSourceName(token);
      ogInput.placeholder = v
        ? `${v} — ${inherited} from ${sourceLabel}`
        : originalPh;
    };

    paint(); // initial
    srcInput.addEventListener("input", paint);

    ogInput.addEventListener("input", () => {
      ogInput.placeholder = originalPh; // detach
      srcInput.removeEventListener("input", paint);
    });
  });
});
