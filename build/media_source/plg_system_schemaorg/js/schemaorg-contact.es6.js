/**
 * schemaorg-contact.js
 * Minimal: populate placeholders from injected initialContact by baseName.
 */
((Joomla, document) => {
  "use strict";

  class SchemaOrgContactHandler {
    constructor(options) {
      this.options = Object.assign(
        {
          fieldMappings: {
            name: "name",
            email: "email_to",
            url: "webpage",
            streetAddress: ["address", "street"],
            addressLocality: ["suburb", "state", "address"],
            postalCode: "postcode",
            addressRegion: "state",
            addressCountry: "country",
            telephone: "telephone",
            mobile: "mobile",
            fax: "fax",
            misc: "misc",
            position: "position",
          },
        },
        options
      );

      // nested fields that must go under [address]
      this.nestedFields = {
        streetAddress: "address",
        postalCode: "address",
        addressLocality: "address",
        addressRegion: "address",
        addressCountry: "address",
      };

      // read injected contact from Joomla options or global
      const opt =
        (Joomla &&
          Joomla.getOptions &&
          Joomla.getOptions("plg_system_schemaorg")) ||
        window.plg_system_schemaorg ||
        {};
      this.initialContact = opt.initialContact || null;

      this._initOnce = false;
      this.init();
    }

    init() {
      if (this._initOnce) return;
      this._initOnce = true;

      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => this.bindEvents());
      } else {
        this.bindEvents();
      }
    }

    bindEvents() {
      // Scope to the current form
      const form = document.querySelector("form");
      if (!form) return;

      // Contact title inputs in the form
      const contactNameFields = form.querySelectorAll(
        "input.js-input-title[name*='[contact]']"
      );

      contactNameFields.forEach((field) => {
        if (field.value && field.value.trim() && this.initialContact) {
          this.populatePlaceholders(field, this.initialContact);
        }
      });
    }

    populatePlaceholders(contactField, contactData) {
      if (!contactField || !contactData) return;

      // Normalize simple contact object and trim strings
      const contact = Object.assign({}, contactData);
      Object.keys(contact).forEach((k) => {
        if (typeof contact[k] === "string") contact[k] = contact[k].trim();
      });

      // find the role container (use nearby grouping)
      const roleContainer = contactField.closest(
        ".subform-repeatable-group, fieldset, form"
      );
      if (!roleContainer) return;

      // Parse baseName from contact field name
      const contactName = contactField.name || "";
      const baseMatch = contactName.match(/^(.*)\[contact\]$/);
      if (!baseMatch) {
        console.debug("schemaorg: contact baseName not found for", contactName);
        return;
      }
      const baseName = baseMatch[1]; // e.g. jform[schema][Book][illustrator]

      // For each mapping, compute target name and set placeholder
      Object.entries(this.options.fieldMappings).forEach(
        ([schemaField, contactKey]) => {
          let value = "";

          if (Array.isArray(contactKey)) {
            for (const k of contactKey) {
              if (contact[k]) {
                value = contact[k];
                break;
              }
            }
          } else {
            value = contact[contactKey] || "";
          }

          if (!value) return;

          const parentKey = this.nestedFields[schemaField] || null;
          const targetName = parentKey
            ? `${baseName}[${parentKey}][${schemaField}]`
            : `${baseName}[${schemaField}]`;

          // find the input within the same roleContainer
          const selector = `input[name='${targetName}'], textarea[name='${targetName}'], select[name='${targetName}']`;
          const targetField =
            roleContainer.querySelector(selector) ||
            document.querySelector(selector);

          if (targetField) {
            targetField.placeholder = value + " — Inherited from contact";
            targetField.classList.add("has-contact-placeholder");
          } else {
            // debug if not found
            console.debug(
              "schemaorg: could not find target field for",
              targetName
            );
          }
        }
      );

      // emit event
      document.dispatchEvent(
        new CustomEvent("schemaorg:contact:loaded", {
          detail: {
            contactId: contact.id,
            contactData: contact,
            container: roleContainer,
          },
        })
      );
    }
  }

  // Initialize once
  if (typeof Joomla !== "undefined") {
    if (!Joomla.SchemaOrgContactHandler) {
      Joomla.SchemaOrgContactHandler = new SchemaOrgContactHandler();
    }
  }
})(window.Joomla, document);
