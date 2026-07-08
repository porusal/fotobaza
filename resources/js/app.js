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
  const themeToggles = Array.from(document.querySelectorAll("[data-theme-toggle]"));
  if (!themeToggles.length) {
    return;
  }

  const storageKey = "foto636-theme";
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  const applyTheme = (theme) => {
    document.body.classList.toggle("dark-mode", theme === "dark");
    themeToggles.forEach((themeToggle) => {
      const label = themeToggle.querySelector("[data-theme-label]");
      const lightLabel = themeToggle.dataset.themeLabelLight || "День";
      const darkLabel = themeToggle.dataset.themeLabelDark || "Ночь";

      themeToggle.setAttribute("aria-pressed", String(theme === "dark"));
      if (label) {
        label.textContent = theme === "dark" ? darkLabel : lightLabel;
      }
    });
  };

  let savedTheme = null;
  try {
    savedTheme = window.localStorage.getItem(storageKey);
  } catch {
    savedTheme = null;
  }

  const nextTheme = savedTheme ?? (prefersDark ? "dark" : "light");
  applyTheme(nextTheme);

  themeToggles.forEach((themeToggle) => {
    themeToggle.addEventListener("click", () => {
      const theme = document.body.classList.contains("dark-mode") ? "light" : "dark";
      try {
        window.localStorage.setItem(storageKey, theme);
      } catch {
        // Local storage can be disabled in hardened browser profiles.
      }
      applyTheme(theme);
    });
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

function alignLanguageSwitcherMenu(switcher) {
  const menu = switcher.querySelector(".language-switcher__menu");

  if (!menu) {
    return;
  }

  menu.style.setProperty("--language-menu-shift-x", "0px");

  const viewportMargin = 12;
  const rect = menu.getBoundingClientRect();
  let shift = 0;

  if (rect.left < viewportMargin) {
    shift = viewportMargin - rect.left;
  } else if (rect.right > window.innerWidth - viewportMargin) {
    shift = window.innerWidth - viewportMargin - rect.right;
  }

  menu.style.setProperty("--language-menu-shift-x", `${Math.round(shift)}px`);
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

    switcher.addEventListener("toggle", () => {
      if (switcher.open) {
        window.requestAnimationFrame(() => alignLanguageSwitcherMenu(switcher));
      }
    });

    window.addEventListener(
      "resize",
      () => {
        if (switcher.open) {
          alignLanguageSwitcherMenu(switcher);
        }
      },
      { passive: true },
    );
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

function updatePhotoFileName(control) {
  const output = control.querySelector("[data-photo-file-name]");

  if (!output) {
    return;
  }

  const input = control.querySelector("[data-photo-file-input]");
  const emptyText = output.dataset.emptyText || "Файл не выбран";

  if (!input?.files?.length) {
    output.textContent = emptyText;
    return;
  }

  output.textContent = input.files[0].name;
}

function initPhotoFileInputs(root = document) {
  root.querySelectorAll("[data-photo-file-control]:not([data-photo-file-ready])").forEach((control) => {
    const input = control.querySelector("[data-photo-file-input]");

    input?.addEventListener("change", () => updatePhotoFileName(control));
    updatePhotoFileName(control);
    control.setAttribute("data-photo-file-ready", "true");
  });
}

const PHOTO_UPLOAD_TARGET_BYTES = 1.6 * 1024 * 1024;
const PHOTO_UPLOAD_MAX_SIDE = 2200;

function isPhotoFile(file) {
  return file?.type?.startsWith("image/") || /\.(jpe?g|png|webp)$/i.test(file?.name || "");
}

function canvasToBlob(canvas, quality) {
  return new Promise((resolve) => {
    canvas.toBlob(resolve, "image/jpeg", quality);
  });
}

function loadImageFile(file) {
  return new Promise((resolve, reject) => {
    const image = new Image();
    const url = URL.createObjectURL(file);

    image.onload = () => {
      URL.revokeObjectURL(url);
      resolve(image);
    };

    image.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error("Image load failed"));
    };

    image.src = url;
  });
}

async function compressPhotoFile(file) {
  if (!isPhotoFile(file) || file.size <= PHOTO_UPLOAD_TARGET_BYTES) {
    return file;
  }

  const image = await loadImageFile(file);
  const scale = Math.min(1, PHOTO_UPLOAD_MAX_SIDE / Math.max(image.naturalWidth, image.naturalHeight));
  const width = Math.max(1, Math.round(image.naturalWidth * scale));
  const height = Math.max(1, Math.round(image.naturalHeight * scale));
  const canvas = document.createElement("canvas");
  const context = canvas.getContext("2d");

  if (!context) {
    return file;
  }

  canvas.width = width;
  canvas.height = height;
  context.drawImage(image, 0, 0, width, height);

  let quality = 0.82;
  let blob = await canvasToBlob(canvas, quality);

  while (blob && blob.size > PHOTO_UPLOAD_TARGET_BYTES && quality > 0.52) {
    quality -= 0.08;
    blob = await canvasToBlob(canvas, quality);
  }

  if (!blob || blob.size >= file.size) {
    return file;
  }

  const fileName = file.name.replace(/\.[^.]+$/, "") || "photo";
  return new File([blob], `${fileName}.jpg`, {
    type: "image/jpeg",
    lastModified: file.lastModified,
  });
}

async function preparePhotoUploadForm(form) {
  const inputs = Array.from(form.querySelectorAll("[data-photo-file-input]"))
    .filter((input) => input.files?.length);

  if (!inputs.length || typeof DataTransfer === "undefined") {
    return;
  }

  for (const input of inputs) {
    const file = input.files[0];
    const compressed = await compressPhotoFile(file);

    if (compressed === file) {
      continue;
    }

    const transfer = new DataTransfer();
    transfer.items.add(compressed);
    input.files = transfer.files;

    const control = input.closest("[data-photo-file-control]");
    if (control) {
      updatePhotoFileName(control);
    }
  }
}

function initPhotoUploadForms() {
  document.querySelectorAll("#photo-create-form, #photo-edit-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (form.dataset.photoUploadPrepared === "true") {
        return;
      }

      if (form.dataset.photoUploadPrepared === "preparing") {
        event.preventDefault();
        return;
      }

      event.preventDefault();
      form.dataset.photoUploadPrepared = "preparing";

      preparePhotoUploadForm(form)
        .catch(() => {})
        .finally(() => {
          form.dataset.photoUploadPrepared = "true";
          if (typeof form.requestSubmit === "function") {
            form.requestSubmit();
          } else {
            form.submit();
          }
        });
    });
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

    row.querySelectorAll("[data-photo-file-control]").forEach(updatePhotoFileName);
  };

  const addRow = () => {
    const rowIndex = nextIndex++;
    const html = template.innerHTML
      .split("__INDEX__").join(String(rowIndex))
      .split("__NUMBER__").join(String(rowIndex + 1));
    list.insertAdjacentHTML("beforeend", html.trim());

    const newRow = list.lastElementChild;
    if (newRow) {
      window.foto636InitSelect2(newRow);
      window.foto636InitPhotoFileInputs(newRow);
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

function initAdminMobileMenu() {
  const sidebar = document.querySelector("#admin-sidebar");
  const toggles = Array.from(document.querySelectorAll("[data-admin-menu-toggle]"));
  const closers = Array.from(document.querySelectorAll("[data-admin-menu-close]"));
  const backdrop = document.querySelector(".admin-menu-backdrop");

  if (!sidebar || !toggles.length) {
    return;
  }

  const setOpen = (isOpen) => {
    document.body.classList.toggle("admin-menu-open", isOpen);
    sidebar.classList.toggle("is-open", isOpen);
    toggles.forEach((toggle) => toggle.setAttribute("aria-expanded", String(isOpen)));
    if (backdrop) {
      backdrop.hidden = !isOpen;
    }
  };

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", () => setOpen(!sidebar.classList.contains("is-open")));
  });

  closers.forEach((closer) => {
    closer.addEventListener("click", () => setOpen(false));
  });

  sidebar.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => setOpen(false));
  });

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      setOpen(false);
    }
  });
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

window.foto636InitPhotoFileInputs = (root = document) => {
  initPhotoFileInputs(root);
};

initGoogleTranslateElement();
initThemeToggle();
initLanguageSwitcher();
initAdminMobileMenu();
initGalleryTreeToggle();
initLightGallery();
window.foto636InitSelect2();
window.foto636InitQuill();
window.foto636InitPhotoFileInputs();
initPhotoUploadRows();
initPhotoUploadForms();
initAdminPhotoGridSwitch();
