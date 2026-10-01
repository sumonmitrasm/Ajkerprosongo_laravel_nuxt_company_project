(function () {
    "use strict";

    var videoPlayers = [
        "https://www.youtube.com/embed/",
        "https://www.youtube-nocookie.com/embed/",
        "https://player.vimeo.com/video/"
    ];

    // Sanitize editor HTML before importing, submitting or exporting it.
    window.cleanEditorHtml = function (html) {
        var content = DOMPurify.sanitize(html || "", {
            USE_PROFILES: { html: true },
            ADD_TAGS: ["iframe"],
            ADD_ATTR: ["allowfullscreen", "frameborder"],
            RETURN_DOM: true
        });

        // Keep video embeds only from the supported, trusted video players.
        content.querySelectorAll("iframe").forEach(function (frame) {
            var source = (frame.getAttribute("src") || "").toLowerCase();
            var allowed = videoPlayers.some(function (player) {
                return source.startsWith(player);
            });
            if (!allowed) {
                frame.remove();
            }
        });

        return content.innerHTML;
    };

    // Quill 2.0.3 has an upstream HTML-export advisory. Sanitize its result.
    var exportHtml = Quill.prototype.getSemanticHTML;
    Quill.prototype.getSemanticHTML = function () {
        return window.cleanEditorHtml(exportHtml.apply(this, arguments));
    };
})();
