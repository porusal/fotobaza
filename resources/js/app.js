import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "lightgallery/css/lightgallery.css";
import "quill/dist/quill.snow.css";
import $ from "jquery";
import Quill from "quill";
import lightGallery from "lightgallery";
import "select2/dist/css/select2.min.css";
import "../css/app.css";

window.$ = window.jQuery = $;

function initThemeToggle() {
  const themeToggle = document.querySelector("[data-theme-toggle]");
  if (!themeToggle) {
    return;
  }

  const storageKey = "foto636-theme";
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const label = themeToggle.querySelector("[data-theme-label]");

  const applyTheme = (theme) => {
    document.body.classList.toggle("dark-mode", theme === "dark");
    themeToggle.setAttribute("aria-pressed", String(theme === "dark"));
    if (label) {
      label.textContent = theme === "dark" ? "Ночь" : "День";
    }
  };

  let savedTheme = null;
  try {
    savedTheme = window.localStorage.getItem(storageKey);
  } catch {
    savedTheme = null;
  }

  const nextTheme = savedTheme ?? (prefersDark ? "dark" : "light");
  applyTheme(nextTheme);

  themeToggle.addEventListener("click", () => {
    const theme = document.body.classList.contains("dark-mode") ? "light" : "dark";
    try {
      window.localStorage.setItem(storageKey, theme);
    } catch {
      // Local storage can be disabled in hardened browser profiles.
    }
    applyTheme(theme);
  });
}

function initLightGallery() {
  document.querySelectorAll("[data-lightgallery]").forEach((gallery) => {
    lightGallery(gallery, {
      selector: ".lightgallery-item",
      download: false,
      speed: 320,
      licenseKey: "0000-0000-000-0000",
      mobileSettings: {
        controls: true,
        showCloseIcon: true,
        download: false,
      },
    });
  });
}

async function initSelect2(root = document) {
  const selectTargets = root.querySelectorAll("[data-select2]:not([data-select2-ready])");
  if (!selectTargets.length) {
    return;
  }

  try {
    await import("select2/dist/js/select2.full.min.js");
  } catch {
    return;
  }

  selectTargets.forEach((element) => {
    $(element).select2({
      width: "100%",
    });
    element.setAttribute("data-select2-ready", "true");
  });
}

function initQuillEditors(root = document) {
  const editorFields = root.querySelectorAll("[data-quill-field]:not([data-quill-ready])");

  editorFields.forEach((field) => {
    const editorEl = field.querySelector("[data-quill-editor]");
    const inputEl = field.querySelector("[data-quill-input]");

    if (!editorEl || !inputEl) {
      return;
    }

    const quill = new Quill(editorEl, {
      theme: "snow",
      placeholder: editorEl.dataset.placeholder || "",
      modules: {
        toolbar: [
          [{ header: [1, 2, 3, false] }],
          ["bold", "italic", "underline", "strike"],
          [{ list: "ordered" }, { list: "bullet" }],
          ["blockquote", "link"],
          ["clean"],
        ],
      },
    });

    if (inputEl.value.trim()) {
      quill.clipboard.dangerouslyPasteHTML(inputEl.value);
    }

    const syncValue = () => {
      const html = quill.root.innerHTML;
      inputEl.value = html === "<p><br></p>" ? "" : html;
    };

    quill.on("text-change", syncValue);
    syncValue();
    field.setAttribute("data-quill-ready", "true");
  });
}

function initPhotoUploadRows() {
  const list = document.querySelector("[data-photo-upload-list]");
  const template = document.querySelector("[data-photo-upload-template]");
  const addButton = document.querySelector("[data-photo-add-row]");

  if (!list || !template || !addButton) {
    return;
  }

  let nextIndex = 0;
  list.querySelectorAll("[data-photo-upload-row]").forEach((row) => {
    const fileInput = row.querySelector('input[type="file"]');
    const match = fileInput?.name.match(/items\[(.+?)\]\[file\]/);
    if (!match) {
      return;
    }

    const index = Number(match[1]);
    if (!Number.isNaN(index)) {
      nextIndex = Math.max(nextIndex, index + 1);
    }
  });

  const clearRow = (row) => {
    row.querySelectorAll("input").forEach((input) => {
      if (input.type === "file") {
        input.value = "";
      } else if (input.type === "checkbox" || input.type === "radio") {
        input.checked = false;
      } else {
        input.value = "";
      }
    });

    row.querySelectorAll("[data-select2]").forEach((select) => {
      $(select).val(null).trigger("change");
    });
  };

  const addRow = () => {
    const html = template.innerHTML.split("__INDEX__").join(String(nextIndex++));
    list.insertAdjacentHTML("beforeend", html.trim());

    const newRow = list.lastElementChild;
    if (newRow) {
      window.foto636InitSelect2(newRow);
    }
  };

  addButton.addEventListener("click", addRow);

  list.addEventListener("click", (event) => {
    const removeButton = event.target.closest("[data-photo-remove-row]");
    if (!removeButton) {
      return;
    }

    const row = removeButton.closest("[data-photo-upload-row]");
    if (!row) {
      return;
    }

    const rows = list.querySelectorAll("[data-photo-upload-row]");
    if (rows.length > 1) {
      row.remove();
      return;
    }

    clearRow(row);
  });

  window.foto636InitSelect2(list);
}

function initGoogleTranslate() {
  window.googleTranslateElementInit = function googleTranslateElementInit() {
    const target = document.getElementById("google_translate_element");
    if (!target || !window.google?.translate?.TranslateElement) {
      return;
    }

    new window.google.translate.TranslateElement(
      {
        pageLanguage: "ru",
        includedLanguages: "ru,en,de,fr,it,es,pl",
        layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false,
      },
      "google_translate_element",
    );
  };
}

window.foto636InitSelect2 = (root = document) => {
  void initSelect2(root).catch(() => {});
};

window.foto636InitQuill = (root = document) => {
  try {
    initQuillEditors(root);
  } catch {
    // Keep the page usable if Quill fails to mount in a constrained browser.
  }
};

initThemeToggle();
initLightGallery();
initGoogleTranslate();
window.foto636InitSelect2();
window.foto636InitQuill();
initPhotoUploadRows();
