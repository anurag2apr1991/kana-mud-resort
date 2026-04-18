/**
 * Carousels, lightbox, nearby pager, reload scroll-to-top.
 */
(function () {
  "use strict";

  function parseJsonSafe(raw) {
    if (!raw) return [];
    try {
      const v = JSON.parse(raw);
      return Array.isArray(v) ? v : [];
    } catch {
      return [];
    }
  }

  function initCarousels() {
    document.querySelectorAll("[data-kmr-carousel]").forEach(function (root) {
      const raw = root.getAttribute("data-images");
      const urls = parseJsonSafe(raw).filter(function (u) {
        return typeof u === "string" && u.length > 0;
      });
      if (!urls.length) return;

      const intervalMs = parseInt(root.getAttribute("data-kmr-interval") || "6000", 10);
      const lightboxOn = root.getAttribute("data-kmr-lightbox") === "1";

      root.classList.add("relative", "h-full", "w-full");
      root.innerHTML = "";

      urls.forEach(function (url, idx) {
        const img = document.createElement("img");
        img.src = url;
        img.alt = "";
        img.className =
          "absolute inset-0 h-full w-full object-cover transition-opacity duration-500 " +
          (idx === 0 ? "opacity-100 z-[1]" : "opacity-0 z-0");
        img.decoding = "async";
        if (idx === 0) img.loading = "eager";
        else img.loading = "lazy";
        root.appendChild(img);
      });

      const imgs = root.querySelectorAll("img");
      let index = 0;
      let timer = null;

      function show(i) {
        index = ((i % imgs.length) + imgs.length) % imgs.length;
        imgs.forEach(function (im, j) {
          if (j === index) {
            im.classList.remove("opacity-0", "z-0");
            im.classList.add("opacity-100", "z-[1]");
          } else {
            im.classList.remove("opacity-100", "z-[1]");
            im.classList.add("opacity-0", "z-0");
          }
        });
        if (dots) {
          dots.querySelectorAll("button").forEach(function (b, j) {
            b.classList.toggle("bg-white", j === index);
            b.classList.toggle("bg-white/50", j !== index);
          });
        }
      }

      function next() {
        show(index + 1);
      }

      function prev() {
        show(index - 1);
      }

      function startTimer() {
        stopTimer();
        if (imgs.length <= 1) return;
        timer = window.setInterval(next, intervalMs);
      }

      function stopTimer() {
        if (timer) window.clearInterval(timer);
        timer = null;
      }

      const isHero =
        root.getAttribute("data-kmr-hero") === "1" &&
        document.getElementById("hero");
      const uiParent = isHero ? document.getElementById("hero") : root;

      let dots = null;
      if (imgs.length > 1) {
        const prevBtn = document.createElement("button");
        prevBtn.type = "button";
        prevBtn.setAttribute("aria-label", "Previous image");
        prevBtn.className =
          "absolute left-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/45 px-3 py-2 text-sm font-semibold text-white hover:bg-black/60";
        if (isHero) prevBtn.style.zIndex = "8";
        prevBtn.textContent = "‹";
        prevBtn.addEventListener("click", function (e) {
          e.stopPropagation();
          prev();
          startTimer();
        });
        uiParent.appendChild(prevBtn);

        const nextBtn = document.createElement("button");
        nextBtn.type = "button";
        nextBtn.setAttribute("aria-label", "Next image");
        nextBtn.className =
          "absolute right-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/45 px-3 py-2 text-sm font-semibold text-white hover:bg-black/60";
        if (isHero) nextBtn.style.zIndex = "8";
        nextBtn.textContent = "›";
        nextBtn.addEventListener("click", function (e) {
          e.stopPropagation();
          next();
          startTimer();
        });
        uiParent.appendChild(nextBtn);

        dots = document.createElement("div");
        dots.className =
          "absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 gap-2";
        if (isHero) dots.style.zIndex = "8";
        urls.forEach(function (_, j) {
          const b = document.createElement("button");
          b.type = "button";
          b.setAttribute("aria-label", "Go to image " + (j + 1));
          b.className =
            "h-2.5 w-2.5 rounded-full " + (j === 0 ? "bg-white" : "bg-white/50");
          b.addEventListener("click", function (e) {
            e.stopPropagation();
            show(j);
            startTimer();
          });
          dots.appendChild(b);
        });
        uiParent.appendChild(dots);
      }

      if (lightboxOn && imgs.length) {
        const overlay = document.createElement("button");
        overlay.type = "button";
        overlay.className =
          "absolute inset-0 z-10 cursor-zoom-in bg-transparent";
        overlay.setAttribute("aria-label", "View images larger");
        overlay.addEventListener("click", function () {
          openLightbox(
            urls.map(function (u, i) {
              return { src: u, alt: "Image " + (i + 1) };
            }),
            index
          );
        });
        root.appendChild(overlay);
      }

      startTimer();
    });
  }

  var lightboxEl = null;
  var lightboxItems = [];
  var lightboxIndex = 0;

  function ensureLightbox() {
    if (lightboxEl) return lightboxEl;
    lightboxEl = document.createElement("div");
    lightboxEl.className =
      "kmr-lightbox fixed inset-0 z-[100] hidden items-center justify-center p-3 sm:p-6";
    lightboxEl.setAttribute("role", "dialog");
    lightboxEl.setAttribute("aria-modal", "true");
    lightboxEl.innerHTML =
      '<button type="button" class="kmr-lb-back absolute inset-0 bg-black/90" aria-label="Close gallery"></button>' +
      '<div class="relative z-10 flex h-[min(90vh,920px)] w-full max-w-6xl flex-col">' +
      '<div class="mb-3 flex shrink-0 justify-end gap-2">' +
      '<button type="button" class="kmr-lb-close rounded-full bg-white/15 px-4 py-2 text-sm font-medium text-white backdrop-blur hover:bg-white/25">Close</button>' +
      "</div>" +
      '<div class="relative min-h-0 flex-1">' +
      '<button type="button" class="kmr-lb-prev absolute left-0 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/15 px-3 py-3 text-2xl text-white backdrop-blur hover:bg-white/25 sm:-left-2" aria-label="Previous image">‹</button>' +
      '<button type="button" class="kmr-lb-next absolute right-0 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/15 px-3 py-3 text-2xl text-white backdrop-blur hover:bg-white/25 sm:-right-2" aria-label="Next image">›</button>' +
      '<div class="kmr-lb-frame relative h-[min(80vh,800px)] w-full">' +
      '<img class="kmr-lb-img h-full w-full object-contain" alt="" />' +
      "</div></div>" +
      '<p class="kmr-lb-count mt-3 shrink-0 text-center text-sm text-white/80"></p>' +
      "</div>";
    document.body.appendChild(lightboxEl);

    function close() {
      lightboxEl.classList.add("hidden");
      lightboxEl.classList.remove("flex");
      document.body.style.overflow = "";
      document.removeEventListener("keydown", onKey);
    }

    function onKey(e) {
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") move(-1);
      if (e.key === "ArrowRight") move(1);
    }

    function move(delta) {
      if (lightboxItems.length <= 1) return;
      lightboxIndex =
        (lightboxIndex + delta + lightboxItems.length) % lightboxItems.length;
      renderLb();
    }

    function renderLb() {
      var img = lightboxEl.querySelector(".kmr-lb-img");
      var cur = lightboxItems[lightboxIndex];
      if (cur && img) {
        img.src = cur.src;
        img.alt = cur.alt || "";
      }
      var c = lightboxEl.querySelector(".kmr-lb-count");
      if (c) {
        c.textContent =
          lightboxItems.length > 1
            ? lightboxIndex + 1 + " / " + lightboxItems.length
            : "";
      }
    }

    lightboxEl.querySelector(".kmr-lb-back").addEventListener("click", close);
    lightboxEl.querySelector(".kmr-lb-close").addEventListener("click", close);
    lightboxEl.querySelector(".kmr-lb-prev").addEventListener("click", function () {
      move(-1);
    });
    lightboxEl.querySelector(".kmr-lb-next").addEventListener("click", function () {
      move(1);
    });

    lightboxEl._kmrClose = close;
    lightboxEl._kmrRender = renderLb;
    lightboxEl._kmrOnKey = onKey;

    return lightboxEl;
  }

  function openLightbox(items, startIndex) {
    lightboxItems = items || [];
    lightboxIndex = startIndex || 0;
    if (!lightboxItems.length) return;
    var el = ensureLightbox();
    el.classList.remove("hidden");
    el.classList.add("flex");
    document.body.style.overflow = "hidden";
    el._kmrRender();
    document.addEventListener("keydown", el._kmrOnKey);
  }

  function initGalleryLightbox() {
    document.querySelectorAll("[data-kmr-gallery]").forEach(function (grid) {
      var raw = grid.getAttribute("data-kmr-gallery");
      var items = parseJsonSafe(raw);
      if (!items.length) return;

      grid.querySelectorAll("[data-kmr-gallery-open]").forEach(function (btn) {
        btn.addEventListener("click", function () {
          var idx = parseInt(btn.getAttribute("data-kmr-gallery-open") || "0", 10);
          var mapped = items.map(function (it) {
            return {
              src: it.full || it.src,
              alt: it.alt || it.caption || "Gallery image",
            };
          });
          openLightbox(mapped, idx);
        });
      });
    });
  }

  function initGallerySlider() {
    document.querySelectorAll("[data-kmr-gallery-slider]").forEach(function (root) {
      var track = root.querySelector(".kmr-gallery-slider-track");
      var prev = root.querySelector(".kmr-gallery-slider-prev");
      var next = root.querySelector(".kmr-gallery-slider-next");
      var dots = root.querySelectorAll(".kmr-gallery-slider-dot");
      var pages = parseInt(root.getAttribute("data-kmr-gallery-pages") || "1", 10);
      if (!track || pages <= 1) return;

      var page = 0;

      function setDots() {
        dots.forEach(function (dot, j) {
          var on = j === page;
          dot.setAttribute("aria-selected", on ? "true" : "false");
          dot.classList.toggle("bg-emerald-700", on);
          dot.classList.toggle("bg-stone-300", !on);
        });
      }

      function go(p) {
        page = Math.max(0, Math.min(pages - 1, p));
        var pct = (page * 100) / pages;
        track.style.transform = "translateX(-" + pct + "%)";
        if (prev) prev.disabled = page === 0;
        if (next) next.disabled = page === pages - 1;
        setDots();
      }

      if (prev)
        prev.addEventListener("click", function () {
          go(page - 1);
        });
      if (next)
        next.addEventListener("click", function () {
          go(page + 1);
        });

      dots.forEach(function (dot) {
        dot.addEventListener("click", function () {
          var pi = parseInt(dot.getAttribute("data-kmr-gallery-page") || "0", 10);
          go(pi);
        });
      });

      go(0);
    });
  }

  function initNearby() {
    document.querySelectorAll(".kmr-nearby").forEach(function (wrap) {
      var per = parseInt(wrap.getAttribute("data-kmr-per-page") || "3", 10);
      var cards = Array.prototype.slice.call(
        wrap.querySelectorAll(".kmr-nearby-card")
      );
      if (cards.length <= per) return;

      var page = 0;
      var totalPages = Math.ceil(cards.length / per);
      var curEl = wrap.querySelector(".kmr-nearby-current");
      var prevBtn = wrap.querySelector(".kmr-nearby-prev");
      var nextBtn = wrap.querySelector(".kmr-nearby-next");

      function render() {
        var start = page * per;
        cards.forEach(function (card, i) {
          if (i >= start && i < start + per) card.classList.remove("hidden");
          else card.classList.add("hidden");
        });
        if (curEl) curEl.textContent = String(page + 1);
        if (prevBtn) prevBtn.disabled = page === 0;
        if (nextBtn) nextBtn.disabled = page >= totalPages - 1;
      }

      if (prevBtn)
        prevBtn.addEventListener("click", function () {
          page = Math.max(0, page - 1);
          render();
        });
      if (nextBtn)
        nextBtn.addEventListener("click", function () {
          page = Math.min(totalPages - 1, page + 1);
          render();
        });

      render();
    });
  }

  function initReloadScroll() {
    try {
      var nav = performance.getEntriesByType("navigation")[0];
      if (!nav || nav.type !== "reload") return;
      var path = window.location.pathname + window.location.search;
      if (window.location.hash) {
        window.history.replaceState(null, "", path);
      }
      window.scrollTo(0, 0);
    } catch {
      /* ignore */
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    initReloadScroll();
    initCarousels();
    initGalleryLightbox();
    initGallerySlider();
    initNearby();
  });
})();
