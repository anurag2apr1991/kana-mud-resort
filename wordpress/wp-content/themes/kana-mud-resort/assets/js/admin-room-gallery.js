/**
 * Room carousel: select images from Media Library (stores comma-separated IDs).
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

  function renderPreview($wrap, ids) {
    $wrap.empty();
    if (!ids.length) return;
    ids.forEach(function (id) {
      var $item = $("<span/>", {
        class: "kmr-room-gallery__item",
        "data-id": String(id),
      });
      var $img = $("<img/>", {
        alt: "",
        width: 80,
        height: 80,
        css: { objectFit: "cover", display: "block", borderRadius: "4px" },
      });
      $item.append($img);
      var urlsMap = typeof kmrRoomGallery.urls === "object" ? kmrRoomGallery.urls : {};
      var url =
        urlsMap[id] || urlsMap[String(id)] || "";
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
            var u = att.get("sizes") && att.get("sizes").thumbnail
              ? att.get("sizes").thumbnail.url
              : att.get("url");
            if (u) $img.attr("src", u);
          });
        }
      }
      var $remove = $("<button/>", {
        type: "button",
        class: "button-link kmr-room-gallery__remove",
        "aria-label": kmrRoomGallery.i18n.remove,
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

  function openFrame(e) {
    e.preventDefault();
    if (!window.wp || !wp.media) {
      window.alert(
        typeof kmrRoomGallery !== "undefined" && kmrRoomGallery.i18n && kmrRoomGallery.i18n.mediaMissing
          ? kmrRoomGallery.i18n.mediaMissing
          : "Media library is not available. Wait a moment and try again, or refresh the page."
      );
      return;
    }

    var $input = $("#kmr_gallery_ids");
    var $preview = $("#kmr-room-gallery-preview");
    var ids = parseIds($input.val());

    var frame = wp.media({
      title: kmrRoomGallery.i18n.title,
      button: { text: kmrRoomGallery.i18n.button },
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
      if (typeof kmrRoomGallery.urls !== "object") kmrRoomGallery.urls = {};
      attachments.forEach(function (a) {
        if (a.sizes && a.sizes.thumbnail) {
          kmrRoomGallery.urls[a.id] = a.sizes.thumbnail.url;
        } else if (a.url) {
          kmrRoomGallery.urls[a.id] = a.url;
        }
      });
      renderPreview($preview, newIds);
    });

    frame.open();
  }

  function removeItem(e) {
    e.preventDefault();
    e.stopPropagation();
    var $btn = $(e.target).closest(".kmr-room-gallery__remove");
    var $item = $btn.closest(".kmr-room-gallery__item");
    var id = parseInt($item.data("id"), 10);
    if (!id) return;
    var $input = $("#kmr_gallery_ids");
    var ids = parseIds($input.val()).filter(function (x) {
      return x !== id;
    });
    $input.val(ids.join(","));
    if (kmrRoomGallery.urls && kmrRoomGallery.urls[id]) {
      delete kmrRoomGallery.urls[id];
    }
    renderPreview($("#kmr-room-gallery-preview"), ids);
  }

  $(function () {
    var $root = $("#kmr-room-gallery-root");
    if (!$root.length) return;

    $("#kmr-room-gallery-add").on("click", openFrame);
    $("#kmr-room-gallery-preview").on("click", ".kmr-room-gallery__remove", removeItem);

    var initial = parseIds($("#kmr_gallery_ids").val());
    renderPreview($("#kmr-room-gallery-preview"), initial);
  });
})(jQuery);
