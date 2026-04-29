/**
 * Frontend address autocomplete powered by Geoapify.
 */
(function () {
  const cfg = window.GuisFusGeoapifyAutocomplete;
  if (!cfg || !cfg.apiKey) return;

  const i18n = cfg.i18n || {};
  const state = {
    instances: new Set(),
    globalsBound: false,
    rafScheduled: false,
    started: false,
  };

  function debounce(fn, wait) {
    let timer;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  function text(key, fallback) {
    const value = i18n[key];
    return typeof value === "string" && value.trim() ? value : fallback;
  }

  function fire(el) {
    if (!el) return;
    el.dispatchEvent(new Event("input", { bubbles: true }));
    el.dispatchEvent(new Event("change", { bubbles: true }));
  }

  function splitBaseAndSuffix(raw) {
    const value = (raw || "").trim();
    if (!value) return { base: "", suffix: "" };

    const commaIndex = value.indexOf(",");
    if (commaIndex >= 0) {
      const base = value.slice(0, commaIndex).trim();
      const suffix = value.slice(commaIndex).trim();
      if (base && suffix) return { base, suffix };
    }

    const tokenRe =
      /\b(floor|flat|unit|suite|apartment|apt|door|office|local|piso|planta|puerta|pta|escalera|esc|bloque|blq|portal|oficina|of|despacho|local|tienda|nave|bajo|bj|bjo|entresuelo|ent|atico|ático|principal|pral)\b/i;
    const tokenMatch = tokenRe.exec(value);
    if (tokenMatch && tokenMatch.index > 0) {
      const base = value.slice(0, tokenMatch.index).trim();
      const suffix = " " + value.slice(tokenMatch.index).trim();
      if (base && suffix.trim()) return { base, suffix };
    }

    const ordinalMatch = /(?:^|\s)(\d+\s*[ºª]\s*\w*)$/i.exec(value);
    if (ordinalMatch) {
      const base = value.slice(0, ordinalMatch.index).trim();
      const suffix = " " + value.slice(ordinalMatch.index).trim();
      if (base && suffix.trim()) return { base, suffix };
    }

    const doorMatch = /\s(\d+\s*[A-Za-z])\s*$/.exec(value);
    if (doorMatch) {
      const before = value.slice(0, doorMatch.index).trim();
      if (/\b\d{1,5}\b/.test(before)) {
        return { base: before, suffix: " " + doorMatch[1].replace(/\s+/g, "") };
      }
    }

    return { base: value, suffix: "" };
  }

  function buildAddressLine(place) {
    const street = (place.street || "").trim();
    const houseNumber = (place.housenumber || place.house_number || "").trim();

    if (street && houseNumber) return street + " " + houseNumber;
    return (place.address_line1 || place.formatted || street || "").trim();
  }

  function getCity(place) {
    return place.city || place.town || place.village || place.hamlet || place.suburb || place.municipality || "";
  }

  function getState(place) {
    return place.state || place.state_code || place.state_district || place.county || place.region || "";
  }

  function isElementVisible(el) {
    if (!el || !el.isConnected) return false;
    const rect = el.getBoundingClientRect();
    if (rect.width <= 0 || rect.height <= 0) return false;

    const height = window.innerHeight || document.documentElement.clientHeight;
    const width = window.innerWidth || document.documentElement.clientWidth;
    return rect.bottom > 0 && rect.right > 0 && rect.top < height && rect.left < width;
  }

  function querySelector(selector) {
    if (!selector) return null;
    try {
      return document.querySelector(selector);
    } catch (error) {
      return null;
    }
  }

  function collectTargets() {
    const targets = [];
    const forms = Array.isArray(cfg.forms) ? cfg.forms : [];

    forms.forEach((ids) => {
      if (!ids || !ids.address) return;
      targets.push({
        mode: "ids",
        address: ids.address,
        city: ids.city || "",
        state: ids.state || "",
        zip: ids.zip || "",
      });
    });

    document.querySelectorAll('[data-geoapify="address"]').forEach((addressEl) => {
      targets.push({
        mode: "data",
        addressEl,
        citySel: addressEl.getAttribute("data-geoapify-city") || "",
        stateSel: addressEl.getAttribute("data-geoapify-state") || "",
        zipSel: addressEl.getAttribute("data-geoapify-zip") || "",
      });
    });

    return targets;
  }

  function bindGlobalListeners() {
    if (state.globalsBound) return;
    state.globalsBound = true;

    document.addEventListener("click", (event) => {
      state.instances.forEach((instance) => {
        if (!instance.isOpen()) return;
        if (event.target === instance.addressEl || instance.dropdown.contains(event.target)) return;
        instance.hide();
      });
    });

    let rafId = 0;
    const reposition = () => {
      if (rafId) return;
      rafId = requestAnimationFrame(() => {
        rafId = 0;
        state.instances.forEach((instance) => {
          if (!instance.isOpen()) return;
          if (!isElementVisible(instance.addressEl) || instance.addressEl.disabled || instance.addressEl.readOnly) {
            instance.hide();
            return;
          }
          instance.positionDropdown();
        });
      });
    };

    window.addEventListener("resize", reposition, { passive: true });
    window.addEventListener("scroll", reposition, { capture: true, passive: true });
    window.addEventListener("touchmove", reposition, { capture: true, passive: true });

    if (window.visualViewport) {
      window.visualViewport.addEventListener("resize", reposition, { passive: true });
      window.visualViewport.addEventListener("scroll", reposition, { passive: true });
    }
  }

  function initTarget(target) {
    let addressEl = null;
    let getCityEl = () => null;
    let getStateEl = () => null;
    let getZipEl = () => null;

    if (target.mode === "ids") {
      addressEl = target.address ? document.getElementById(target.address) : null;
      if (!addressEl) return false;
      getCityEl = () => (target.city ? document.getElementById(target.city) : null);
      getStateEl = () => (target.state ? document.getElementById(target.state) : null);
      getZipEl = () => (target.zip ? document.getElementById(target.zip) : null);
    } else {
      addressEl = target.addressEl;
      if (!addressEl || !addressEl.isConnected) return false;
      getCityEl = () => (target.citySel ? querySelector(target.citySel) : null);
      getStateEl = () => (target.stateSel ? querySelector(target.stateSel) : null);
      getZipEl = () => (target.zipSel ? querySelector(target.zipSel) : null);
    }

    if (addressEl.dataset.guisfusGeoapifyBound === "1") return true;
    addressEl.dataset.guisfusGeoapifyBound = "1";

    bindGlobalListeners();

    const dropdownId = "guisfus-geoapify-listbox-" + Math.random().toString(36).slice(2);
    const dropdown = document.createElement("div");
    dropdown.className = "gaa-dropdown";
    dropdown.id = dropdownId;
    dropdown.setAttribute("role", "listbox");
    dropdown.style.position = "absolute";
    dropdown.style.display = "none";
    dropdown.style.zIndex = "99999";
    document.body.appendChild(dropdown);

    const statusEl = document.createElement("div");
    statusEl.className = "gaa-status";
    statusEl.setAttribute("aria-live", "polite");
    statusEl.setAttribute("aria-atomic", "true");
    document.body.appendChild(statusEl);

    addressEl.setAttribute("aria-autocomplete", "list");
    addressEl.setAttribute("aria-expanded", "false");
    addressEl.setAttribute("aria-controls", dropdownId);

    let results = [];
    let activeIndex = -1;
    let selecting = false;
    let lastSuffix = "";
    let controller = null;
    let requestId = 0;

    function announce(message) {
      statusEl.textContent = message || "";
    }

    function positionDropdown() {
      if (!addressEl.isConnected || !isElementVisible(addressEl)) {
        hide();
        return;
      }

      const rect = addressEl.getBoundingClientRect();
      dropdown.style.left = rect.left + window.scrollX + "px";
      dropdown.style.top = rect.bottom + window.scrollY + "px";
      dropdown.style.width = rect.width + "px";
    }

    function hide(clearStatus = true) {
      dropdown.style.display = "none";
      dropdown.textContent = "";
      results = [];
      activeIndex = -1;
      if (clearStatus) announce("");
      addressEl.setAttribute("aria-expanded", "false");
      addressEl.removeAttribute("aria-activedescendant");
    }

    function isOpen() {
      return dropdown.style.display === "block";
    }

    function show() {
      if (!dropdown.children.length) return;
      positionDropdown();
      dropdown.style.display = "block";
      addressEl.setAttribute("aria-expanded", "true");
    }

    function setActive(index) {
      activeIndex = index;
      Array.from(dropdown.children).forEach((child, childIndex) => {
        const isActive = childIndex === activeIndex;
        child.classList.toggle("active", isActive);
        child.setAttribute("aria-selected", isActive ? "true" : "false");
      });

      if (activeIndex >= 0 && dropdown.children[activeIndex]) {
        addressEl.setAttribute("aria-activedescendant", dropdown.children[activeIndex].id);
      } else {
        addressEl.removeAttribute("aria-activedescendant");
      }
    }

    function fill(place, suffixToApply) {
      selecting = true;

      const cityEl = getCityEl();
      const stateEl = getStateEl();
      const zipEl = getZipEl();
      const base = buildAddressLine(place);
      const suffix = (suffixToApply || "").trim();

      addressEl.value = suffix ? base + (suffix.startsWith(",") ? "" : " ") + suffix : base;
      if (cityEl) cityEl.value = getCity(place);
      if (stateEl) stateEl.value = getState(place);
      if (zipEl) zipEl.value = place.postcode || "";

      addressEl.dispatchEvent(new Event("change", { bubbles: true }));
      fire(cityEl);
      fire(stateEl);
      fire(zipEl);
      hide();

      setTimeout(() => {
        selecting = false;
      }, 250);
    }

    function render(list) {
      dropdown.textContent = "";
      results = Array.isArray(list) ? list : [];
      setActive(-1);

      if (!results.length) {
        announce(text("noResults", "No addresses found."));
        hide(false);
        return;
      }

      announce(String(results.length) + " " + text("available", "results available."));

      results.forEach((place, index) => {
        const baseLabel = place.formatted || place.address_line1 || "";
        if (!baseLabel) return;

        const label = lastSuffix ? baseLabel + (lastSuffix.trim().startsWith(",") ? "" : " ") + lastSuffix.trim() : baseLabel;
        const item = document.createElement("div");
        item.className = "gaa-item";
        item.id = dropdownId + "-option-" + index;
        item.setAttribute("role", "option");
        item.setAttribute("aria-selected", "false");
        item.textContent = label;

        item.addEventListener("mouseenter", () => setActive(index));
        item.addEventListener("mousedown", (event) => {
          event.preventDefault();
          fill(place, lastSuffix);
        });

        dropdown.appendChild(item);
      });

      show();
    }

    const fetchData = debounce(async () => {
      if (selecting) return;

      const raw = addressEl.value || "";
      const split = splitBaseAndSuffix(raw);
      const query = split.base.trim();
      lastSuffix = split.suffix || "";

      if (query.length < (Number(cfg.minChars) || 3)) {
        hide();
        return;
      }

      if (controller) controller.abort();
      controller = new AbortController();
      requestId += 1;
      const currentRequest = requestId;

      const params = new URLSearchParams({
        text: query,
        lang: cfg.lang || "en",
        limit: String(Number(cfg.limit) || 6),
        format: "json",
        apiKey: cfg.apiKey,
      });

      if (cfg.countryCode) {
        params.set("filter", "countrycode:" + cfg.countryCode);
      }

      try {
        announce(text("loading", "Searching addresses..."));
        const response = await fetch("https://api.geoapify.com/v1/geocode/autocomplete?" + params.toString(), {
          signal: controller.signal,
        });

        if (!response.ok) throw new Error("Geoapify request failed");

        const data = await response.json();
        if (currentRequest !== requestId) return;
        render(data.results || []);
      } catch (error) {
        if (error.name === "AbortError") return;
        hide();
      }
    }, 300);

    addressEl.addEventListener("input", fetchData);
    addressEl.addEventListener("keydown", (event) => {
      if (!isOpen()) return;

      if (event.key === "ArrowDown") {
        event.preventDefault();
        setActive(Math.min(activeIndex + 1, results.length - 1));
      } else if (event.key === "ArrowUp") {
        event.preventDefault();
        setActive(Math.max(activeIndex - 1, 0));
      } else if (event.key === "Enter") {
        if (activeIndex >= 0 && results[activeIndex]) {
          event.preventDefault();
          fill(results[activeIndex], lastSuffix);
        }
      } else if (event.key === "Escape") {
        hide();
      }
    });

    addressEl.addEventListener("focus", positionDropdown);
    addressEl.addEventListener("click", positionDropdown);
    addressEl.addEventListener("blur", () => {
      setTimeout(() => {
        if (document.activeElement === addressEl || selecting) return;
        hide();
      }, 150);
    });

    const instance = { addressEl, dropdown, positionDropdown, hide, isOpen };
    state.instances.add(instance);

    const cleanupObserver = new MutationObserver(() => {
      if (addressEl.isConnected) return;
      dropdown.remove();
      statusEl.remove();
      if (controller) controller.abort();
      state.instances.delete(instance);
      cleanupObserver.disconnect();
    });
    cleanupObserver.observe(document.body, { childList: true, subtree: true });

    return true;
  }

  function initAll() {
    let any = false;
    collectTargets().forEach((target) => {
      if (initTarget(target)) any = true;
    });
    return any;
  }

  function safeInitAll() {
    try {
      return initAll();
    } catch (error) {
      return false;
    }
  }

  function start() {
    if (state.started) return;
    state.started = true;
    safeInitAll();

    const observer = new MutationObserver(() => {
      if (state.rafScheduled) return;
      state.rafScheduled = true;
      requestAnimationFrame(() => {
        state.rafScheduled = false;
        safeInitAll();
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    window.addEventListener("pageshow", safeInitAll);
    document.addEventListener("focusin", safeInitAll, { passive: true });
    document.addEventListener("click", safeInitAll, { passive: true });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start, { once: true });
  } else {
    start();
  }
})();
