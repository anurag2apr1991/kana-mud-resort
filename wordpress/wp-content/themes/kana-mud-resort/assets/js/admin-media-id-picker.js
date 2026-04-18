/**
 * WordPress admin: choose multiple images from Media Library; store comma-separated attachment IDs.
 * Expects window.kmrMediaIdPickers = [ { input, preview, button, urls, i18n }, ... ]
 */
(function ($) {
  "use strict";

  function parseIds(val) {
    if (!val || typeof val !== "string") return [];
    return val
      .split(/[\s,]+/)
      .map(function (x) {
        return parseInt(x, 10);
      })
      .filter(function (n) {
        return n > 0;
      });
  }

  function uniqueIds(ids) {
    var seen = {};
    return ids.filter(function (id) {
      if (seen[id]) return false;
      seen[id] = true;
      return true;
    });
  }

  function withWpMedia(callback) {
    if (window.wp && wp.media) {
      callback(true);
      return;
    }
    var attempts = 0;
    var timer = window.setInterval(function () {
      attempts += 1;
      if (window.wp && wp.media) {
        window.clearInterval(timer);
        callback(true);
      } else if (attempts > 240) {
        window.clearInterval(timer);
        callback(false);
      }
    }, 25);
  }

  function renderPreview($wrap, ids, urlsMap, removeLabel) {
    urlsMap = urlsMap || {};
    $wrap.empty();
    if (!ids.length) return;
    ids.forEach(function (id) {
      var $item = $("<span/>", {
        class: "kmr-media-ids__item",
        "data-id": String(id),
      });
      var $img = $("<img/>", {
        alt: "",
        width: 80,
        height: 80,
        css: { objectFit: "cover", display: "block", borderRadius: "4px" },
      });
      $item.append($img);
      var url = urlsMap[id] || urlsMap[String(id)] || "";
      if (url) {
        $img.attr("src", url);
      } else {
        $img.attr(
          "src",
          "data:image/svg+xml," +
            encodeURIComponent(
              '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect fill="#ddd" width="80" height="80"/><text x="40" y="44" text-anchor="middle" fill="#666" font-size="12">' +
                id +
                "</text></svg>"
            )
        );
        if (window.wp && wp.media && wp.media.attachment) {
          var att = wp.media.attachment(id);
          att.fetch().done(function () {
            var u =
              att.get("sizes") && att.get("sizes").thumbnail
                ? att.get("sizes").thumbnail.url
                : att.get("url");
            if (u) $img.attr("src", u);
          });
        }
      }
      var $remove = $("<button/>", {
        type: "button",
        class: "button-link kmr-media-ids__remove",
        "aria-label": removeLabel || "Remove",
        text: "×",
        css: {
          position: "absolute",
          top: "2px",
          right: "2px",
          lineHeight: "1",
          padding: "0 4px",
        },
      });
      $item.css({ position: "relative", display: "inline-block" });
      $item.append($remove);
      $wrap.append($item);
    });
  }

  function bindPicker(cfg) {
    var $input = $(cfg.input);
    var $preview = $(cfg.preview);
    var $btn = $(cfg.button);
    if (!$input.length || !$preview.length || !$btn.length) {
      return;
    }
    var urlsMap = cfg.urls && typeof cfg.urls === "object" ? cfg.urls : {};
    var i18n = cfg.i18n || {};

    function openMediaFrame() {
      var ids = parseIds($input.val());

      var frame = wp.media({
        title: i18n.title || "Select images",
        button: { text: i18n.button || "Use selected images" },
        library: { type: "image" },
        multiple: true,
      });

      frame.on("open", function () {
        var selection = frame.state().get("selection");
        selection.reset();
        ids.forEach(function (id) {
          var att = wp.media.attachment(id);
          att.fetch();
          selection.add(att);
        });
      });

      frame.on("select", function () {
        var attachments = frame.state().get("selection").toJSON();
        var newIds = uniqueIds(
          attachments.map(function (a) {
            return a.id;
          })
        );
        $input.val(newIds.join(","));
        attachments.forEach(function (a) {
          if (a.sizes && a.sizes.thumbnail) {
            urlsMap[a.id] = a.sizes.thumbnail.url;
          } else if (a.url) {
            urlsMap[a.id] = a.url;
          }
        });
        renderPreview($preview, newIds, urlsMap, i18n.remove);
      });

      frame.open();
    }

    function onChooseClick(e) {
      e.preventDefault();
      withWpMedia(function (ok) {
        if (!ok) {
          window.alert(i18n.mediaMissing || "Media library is not ready.");
          return;
        }
        openMediaFrame();
      });
    }

    function removeItem(e) {
      e.preventDefault();
      e.stopPropagation();
      var $item = $(e.target).closest(".kmr-media-ids__item");
      var id = parseInt($item.data("id"), 10);
      if (!id) return;
      var ids = parseIds($input.val()).filter(function (x) {
        return x !== id;
      });
      $input.val(ids.join(","));
      if (urlsMap[id]) delete urlsMap[id];
      renderPreview($preview, ids, urlsMap, i18n.remove);
    }

    $btn.on("click", onChooseClick);
    $preview.on("click", ".kmr-media-ids__remove", removeItem);

    var initial = parseIds($input.val());
    renderPreview($preview, initial, urlsMap, i18n.remove);
  }

  $(function () {
    var raw = window.kmrMediaIdPickers;
    if (!raw) return;
    var list = Array.isArray(raw) ? raw : Object.keys(raw).map(function (k) {
      return raw[k];
    });
    if (!list.length) return;
    list.forEach(bindPicker);
  });
})(jQuery);
