import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "lightgallery/css/lightgallery.css";
import "lightgallery/css/lg-autoplay.css";
import "lightgallery/css/lg-fullscreen.css";
import "lightgallery/css/lg-rotate.css";
import "lightgallery/css/lg-share.css";
import "lightgallery/css/lg-thumbnail.css";
import "lightgallery/css/lg-zoom.css";
import "quill/dist/quill.snow.css";
import $ from "jquery";
import Quill from "quill";
import lightGallery from "lightgallery";
import lgAutoplay from "lightgallery/plugins/autoplay";
import lgFullscreen from "lightgallery/plugins/fullscreen";
import lgRotate from "lightgallery/plugins/rotate";
import lgShare from "lightgallery/plugins/share";
import lgThumbnail from "lightgallery/plugins/thumbnail";
import lgZoom from "lightgallery/plugins/zoom";
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
  const lightLabel = themeToggle.dataset.themeLabelLight || "День";
  const darkLabel = themeToggle.dataset.themeLabelDark || "Ночь";

  const applyTheme = (theme) => {
    document.body.classList.toggle("dark-mode", theme === "dark");
    themeToggle.setAttribute("aria-pressed", String(theme === "dark"));
    if (label) {
      label.textContent = theme === "dark" ? darkLabel : lightLabel;
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

function readCookie(name) {
  const prefix = `${name}=`;
  const cookies = document.cookie ? document.cookie.split("; ") : [];
  const cookie = cookies.find((item) => item.startsWith(prefix));

  if (!cookie) {
    return "";
  }

  try {
    return decodeURIComponent(cookie.slice(prefix.length));
  } catch {
    return cookie.slice(prefix.length);
  }
}

function cookieDomainCandidates() {
  const host = window.location.hostname;
  if (!host || host === "localhost" || /^[\d.]+$/.test(host) || host.includes(":")) {
    return [];
  }

  const parts = host.split(".").filter(Boolean);
  const domains = new Set([host, `.${host}`]);

  if (parts.length > 2) {
    domains.add(`.${parts.slice(-2).join(".")}`);
  }

  return Array.from(domains);
}

function writeCookie(name, value, maxAgeSeconds) {
  document.cookie = `${name}=${value}; path=/; max-age=${maxAgeSeconds}; SameSite=Lax`;

  cookieDomainCandidates().forEach((domain) => {
    document.cookie = `${name}=${value}; path=/; domain=${domain}; max-age=${maxAgeSeconds}; SameSite=Lax`;
  });
}

function clearCookie(name) {
  document.cookie = `${name}=; path=/; max-age=0; SameSite=Lax`;

  cookieDomainCandidates().forEach((domain) => {
    document.cookie = `${name}=; path=/; domain=${domain}; max-age=0; SameSite=Lax`;
  });
}

function googleCookieLanguage(sourceLanguage) {
  const value = readCookie("googtrans");
  const parts = value.replace(/^\/+|\/+$/g, "").split("/");

  if (parts.length >= 2 && parts[0] === sourceLanguage) {
    return parts[1];
  }

  return "";
}

function savedLanguage(sourceLanguage, serverLanguage = "") {
  const cookieLanguage = readCookie("site_locale");
  const googleLanguage = googleCookieLanguage(sourceLanguage);

  try {
    return serverLanguage || cookieLanguage || googleLanguage || window.localStorage.getItem("foto636-language") || sourceLanguage;
  } catch {
    return serverLanguage || cookieLanguage || googleLanguage || sourceLanguage;
  }
}

function rememberLanguage(language) {
  try {
    window.localStorage.setItem("foto636-language", language);
  } catch {
    // Local storage can be disabled in private or hardened browser modes.
  }
}

function applyGoogleTranslateLanguage(sourceLanguage, targetLanguage) {
  const oneYear = 60 * 60 * 24 * 365;

  if (!targetLanguage || targetLanguage === sourceLanguage) {
    clearCookie("googtrans");
    writeCookie("site_locale", sourceLanguage, oneYear);
    rememberLanguage(sourceLanguage);
    return;
  }

  writeCookie("googtrans", `/${sourceLanguage}/${targetLanguage}`, oneYear);
  writeCookie("site_locale", targetLanguage, oneYear);
  rememberLanguage(targetLanguage);
}

function triggerGoogleTranslateLanguage(sourceLanguage, targetLanguage, attempt = 0) {
  if (!targetLanguage || targetLanguage === sourceLanguage) {
    return;
  }

  const combo = document.querySelector(".goog-te-combo");
  if (!combo) {
    if (attempt < 30) {
      window.setTimeout(() => triggerGoogleTranslateLanguage(sourceLanguage, targetLanguage, attempt + 1), 150);
    }
    return;
  }

  if (combo.value !== targetLanguage) {
    combo.value = targetLanguage;
    combo.dispatchEvent(new Event("change", { bubbles: true }));
  }
}

function syncLanguageSwitcherSummary(switcher, activeLink) {
  if (!activeLink) {
    return;
  }

  const label = switcher.querySelector("[data-language-current-label]");
  const flag = switcher.querySelector("[data-language-current-flag]");
  const nextLabel = activeLink.dataset.languageLabel;
  const nextFlag = activeLink.dataset.languageFlagSrc;

  if (label && nextLabel) {
    label.textContent = nextLabel;
  }

  if (flag && nextFlag) {
    flag.setAttribute("src", nextFlag);
  }
}

function syncLanguageSwitcherState(switcher, language) {
  const sourceLanguage = switcher.dataset.sourceLanguage || "ru";
  const links = Array.from(switcher.querySelectorAll("[data-language-link]"));
  const activeLanguage = language || sourceLanguage;
  let activeLink = links.find((link) => link.dataset.language === activeLanguage);

  if (!activeLink) {
    activeLink = links.find((link) => link.dataset.language === sourceLanguage) || links[0];
  }

  links.forEach((link) => {
    const isActive = link === activeLink;

    link.classList.toggle("is-active", isActive);
    if (isActive) {
      link.setAttribute("aria-current", "true");
    } else {
      link.removeAttribute("aria-current");
    }
  });

  syncLanguageSwitcherSummary(switcher, activeLink);
}

function initLanguageSwitcher() {
  document.querySelectorAll("[data-language-switcher]").forEach((switcher) => {
    const sourceLanguage = switcher.dataset.sourceLanguage || "ru";
    const currentLanguage = savedLanguage(sourceLanguage, switcher.dataset.currentLanguage || "");
    const links = Array.from(switcher.querySelectorAll("[data-language-link]"));

    syncLanguageSwitcherState(switcher, currentLanguage);

    links.forEach((link) => {
      const language = link.dataset.language || sourceLanguage;
      link.addEventListener("click", (event) => {
        event.preventDefault();
        syncLanguageSwitcherState(switcher, language);
        applyGoogleTranslateLanguage(sourceLanguage, language);
        switcher.removeAttribute("open");
        window.location.reload();
      });
    });
  });
}

function initGoogleTranslateElement() {
  window.googleTranslateElementInit = () => {
    const element = document.getElementById("google_translate_element");

    if (!element || !window.google?.translate?.TranslateElement) {
      return;
    }

    const config = {
      pageLanguage: element.dataset.sourceLanguage || "ru",
      autoDisplay: false,
    };

    if (element.dataset.includedLanguages) {
      config.includedLanguages = element.dataset.includedLanguages;
    }

    new window.google.translate.TranslateElement(config, "google_translate_element");
    triggerGoogleTranslateLanguage(config.pageLanguage, savedLanguage(config.pageLanguage, element.dataset.currentLanguage || ""));
  };
}

function initGalleryTreeToggle() {
  document.addEventListener("click", (event) => {
    const toggle = event.target.closest("[data-gallery-tree-toggle]");
    if (!toggle) {
      return;
    }

    const item = toggle.closest("[data-gallery-tree-item]");
    if (!item) {
      return;
    }

    const children = item.querySelector("[data-gallery-tree-children]");
    if (!children) {
      return;
    }

    const isHidden = children.hasAttribute("hidden");
    if (isHidden) {
      children.removeAttribute("hidden");
      toggle.setAttribute("aria-expanded", "true");
      toggle.title = toggle.dataset.titleExpanded || "Свернуть ветку";
      return;
    }

    children.setAttribute("hidden", "");
    toggle.setAttribute("aria-expanded", "false");
    toggle.title = toggle.dataset.titleCollapsed || "Развернуть ветку";
  });
}

function initLightGallery() {
  document.querySelectorAll("[data-lightgallery]:not([data-lightgallery-ready])").forEach((gallery) => {
    lightGallery(gallery, {
      plugins: [lgThumbnail, lgZoom, lgFullscreen, lgAutoplay, lgRotate, lgShare],
      selector: ".lightgallery-item",
      hash: false,
      loop: true,
      mode: "lg-slide",
      appendSubHtmlTo: ".lg-item",
      slideDelay: 400,
      allowMediaOverlap: false,
      counter: true,
      download: true,
      thumbnail: true,
      animateThumb: true,
      alignThumbnails: "middle",
      currentPagerPosition: "middle",
      thumbWidth: 96,
      thumbHeight: "78px",
      thumbMargin: 6,
      toggleThumb: false,
      zoom: true,
      actualSize: true,
      showZoomInOutIcons: true,
      fullScreen: true,
      autoplay: true,
      autoplayControls: true,
      slideShowAutoplay: false,
      rotate: true,
      rotateLeft: true,
      rotateRight: true,
      flipHorizontal: true,
      flipVertical: true,
      share: true,
      speed: 420,
      licenseKey: "0000-0000-000-0000",
      mobileSettings: {
        controls: true,
        showCloseIcon: true,
        download: true,
        thumbnail: true,
      },
    });

    gallery.setAttribute("data-lightgallery-ready", "true");
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

function initAdminPhotoGridSwitch() {
  document.querySelectorAll("[data-admin-photo-grid-switch]").forEach((switcher) => {
    const panel = switcher.closest("[data-admin-photo-grid-panel]") || document;
    const previews = Array.from(panel.querySelectorAll("[data-admin-photo-grid-preview]"));
    const buttons = Array.from(switcher.querySelectorAll("[data-grid-mode-button]"));

    if (!previews.length || !buttons.length) {
      return;
    }

    const setMode = (mode) => {
      previews.forEach((preview) => {
        preview.dataset.gridPreviewMode = mode;
      });

      buttons.forEach((button) => {
        const isActive = button.dataset.gridModeButton === mode;
        button.classList.toggle("is-active", isActive);
        button.setAttribute("aria-pressed", isActive ? "true" : "false");
      });
    };

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        setMode(button.dataset.gridModeButton || "desktop");
      });
    });

    const requestedMode = previews[0].dataset.gridPreviewMode || "auto";
    const automaticMode = window.matchMedia("(max-width: 575.98px)").matches
      ? "mobile"
      : window.matchMedia("(max-width: 991.98px)").matches
        ? "tablet"
        : "desktop";

    setMode(requestedMode === "auto" ? automaticMode : requestedMode);
  });
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

initGoogleTranslateElement();
initThemeToggle();
initLanguageSwitcher();
initGalleryTreeToggle();
initLightGallery();
window.foto636InitSelect2();
window.foto636InitQuill();
initPhotoUploadRows();
initAdminPhotoGridSwitch();
