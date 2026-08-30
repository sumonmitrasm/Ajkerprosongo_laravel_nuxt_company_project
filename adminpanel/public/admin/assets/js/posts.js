(function ($) {
    "use strict";

    function elements() {
        return {
            modal: document.getElementById("post-form-modal"),
            form: $("#post-form"),
            errors: $("#post-form-errors"),
        };
    }
    // Build one reusable full rich-text editor for the current AJAX-loaded Post form.
    function richEditor() {
        var node = document.getElementById("post-rich-editor");
        if (!node || typeof window.Quill === "undefined") return null;
        if (node.__postQuill) return node.__postQuill;

        var toolbar = [
            [{ header: [1, 2, 3, 4, 5, 6, false] }, { font: [] }, { size: ["small", false, "large", "huge"] }],
            ["bold", "italic", "underline", "strike"],
            [{ color: [] }, { background: [] }],
            [{ script: "sub" }, { script: "super" }],
            ["blockquote", "code-block"],
            [{ list: "ordered" }, { list: "bullet" }, { indent: "-1" }, { indent: "+1" }],
            [{ direction: "rtl" }, { align: [] }],
            ["link", "image", "video", "clean"]
        ];

        var quill = new Quill(node, {
            theme: "snow",
            placeholder: "Enter news description here...",
            modules: { toolbar: toolbar }
        });

        // Keep the hidden textarea ready for normal Laravel validation and FormData.
        quill.on("text-change", function () {
            var html = quill.root.innerHTML === "<p><br></p>" ? "" : quill.root.innerHTML;
            $("#post-description").val(html);
            var words = quill.getText().trim().split(/\s+/).filter(Boolean).length;
            $("[data-word-count]").text(words);
            $("[data-read-time]").text((words ? Math.max(1, Math.ceil(words / 220)) : 0) + " min");
        });
        node.__postQuill = quill;
        return quill;
    }

    function setEditorHtml(html) {
        var quill = richEditor();
        if (!quill) return $("#post-description").val(html || "");
        quill.clipboard.dangerouslyPasteHTML(html || "");
    }
    function errors(xhr) {
        var e = elements(),
            response = xhr.responseJSON || {},
            messages = [];

        if (response.errors) {
            $.each(response.errors, function (_, values) {
                messages = messages.concat(values);
            });
        } else {
            messages.push(response.message || "We could not save this post. Please try again.");
        }

        var list = messages
            .map(function (message) {
                return "<li>" + $("<div>").text(message).html() + "</li>";
            })
            .join("");

        e.errors
            .html(
                '<div class="post-validation-inner">' +
                    '<span class="post-validation-icon">!</span>' +
                    '<div><strong class="post-validation-title">Please check the highlighted information</strong>' +
                    '<p class="post-validation-copy">Correct the following item' + (messages.length > 1 ? "s" : "") + ' and save again.</p>' +
                    '<ul class="post-validation-list">' + list + "</ul></div></div>"
            )
            .removeClass("d-none");

        if (e.modal) e.modal.querySelector(".post-modal-body").scrollTo({ top: 0, behavior: "smooth" });
    }    function refresh(message) {
        if (window.loadAjaxPage) window.loadAjaxPage(window.location.href, false);
        else window.location.reload();
        setTimeout(function () {
            if (window.Swal) Swal.fire({ icon: "success", title: message, timer: 1500, showConfirmButton: false });
        }, 250);
    }
    function slug(v) {
        return String(v || "")
            .trim()
            .toLowerCase()
            .replace(/[^\p{L}\p{N}\s-]/gu, "")
            .replace(/[\s_-]+/g, "-")
            .replace(/^-+|-+$/g, "");
    }
    function optionFilter($select, key, value, keep) {
        $select.find("option").each(function () {
            if (!this.value) return;
            var show = !value || String($(this).data(key)) === String(value);
            $(this).prop("disabled", !show).toggle(show);
        });
        if (keep) $select.val(String(keep));
        else if ($select.find("option:selected").prop("disabled")) $select.val("");
    }
    function galleryRow() {
        return $('<div class="gallery-row">').append(
            $("<input>", {
                type: "file",
                name: "gallery_images[]",
                class: "form-control form-control-sm",
                accept: "image/*",
            }),
            $("<input>", {
                name: "gallery_captions[]",
                class: "form-control form-control-sm gallery-caption",
                placeholder: "Caption",
            }),
            $("<button>", { type: "button", class: "btn btn-sm btn-outline-danger remove-gallery" }).append(
                '<i class="fe fe-trash-2"></i>'
            )
        );
    }
    function preview() {
        var title = $("#post-title").val() || "Your headline",
            s = $("#post-slug").val() || "post-slug",
            d = $("#meta-description").val() || $("#post-summary").val() || "Search preview description";
        $("[data-seo-title]").text($("#meta-title").val() || title);
        $("[data-seo-url]").text("/" + s);
        $("[data-seo-description]").text(d);
    }
    function setValue(name, value) {
        $('#post-form [name="' + name + '"]').val(value == null ? "" : value);
    }
    // Present every immutable status transition without allowing manual editing.
    function showEditorialHistory(history) {
        var $audit = $("#post-audit");
        var $timeline = $audit.find("[data-editorial-history]").empty();
        var labels = { draft: "Draft", review: "Review", scheduled: "Scheduled", published: "Published", archived: "Archived" };

        if (!Array.isArray(history) || !history.length) {
            $timeline.append($("<p>", { class: "post-history-empty", text: "No status history has been recorded yet." }));
            return $audit.removeClass("d-none");
        }

        history.forEach(function (entry) {
            var from = entry.from_status ? (labels[entry.from_status] || entry.from_status) : "Created";
            var to = labels[entry.to_status] || entry.to_status || "Unknown";
            var $item = $("<div>", { class: "post-history-item" });
            $item.append($("<div>", { class: "post-history-transition", text: from + " -> " + to }));
            $item.append($("<div>", { class: "post-history-meta", text: (entry.changed_by || "Deleted or unknown user") + (entry.changed_at ? " | " + entry.changed_at : "") }));
            if (entry.note) $item.append($("<div>", { class: "post-history-note", text: entry.note }));
            $timeline.append($item);
        });
        $audit.removeClass("d-none");
    }
    function resetForm() {
        var e = elements();
        e.form[0].reset();
        e.form.data({ url: e.form.data("store-url"), method: "POST" });
        e.errors.addClass("d-none").empty();
        $("#post-audit").addClass("d-none");
        $("#post-form-title").text("Create New Post");
        $("#post-submit").html('<i class="fe fe-save me-1"></i>Save Post');
        $("#gallery-list,#existing-gallery").empty();
        $("#gallery-list").append(galleryRow());
        $("#allow-comments").prop("checked", true);
        $("#post-form input[type=checkbox]:not(#allow-comments)").prop("checked", false);
        $("#post-tags").val([]);
        $("#post-form [name=reviewed_by]").val("").prop("required", false);
        $("[data-image-preview]").addClass("d-none").attr("src", "");
        $("[data-upload-text]").removeClass("d-none");
        optionFilter($("#post-category"), "section", "");
        optionFilter($("#post-district"), "division", "");
        optionFilter($("#post-upazila"), "district", "");
        setEditorHtml("");
        preview();
    }

    $(document).on("change", "#post-form [name=status]", function () {
        var needsReviewer = this.value === "review";
        $("#post-form [name=reviewed_by]").prop("required", needsReviewer);
    });
    $(document).on("click", ".js-post-create", function () {
        resetForm();
        bootstrap.Modal.getOrCreateInstance(elements().modal).show();
    });
    $(document).on("click", ".js-post-edit", function () {
        var e = elements(),
            $b = $(this);
        $b.prop("disabled", true);
        $.get($b.data("url"))
            .done(function (r) {
                var p = r.post;
                resetForm();
                e.form.data({ url: $b.data("update-url"), method: "PUT" });
                $("#post-form-title").text("Edit Post");
                $("#post-submit").html('<i class="fe fe-save me-1"></i>Update Post');
                [
                    "post_type",
                    "special_title",
                    "title",
                    "slug",
                    "summary",                    "image_caption",
                    "image_alt",
                    "video_type",
                    "video_url",
                    "status",
                    "scheduled_at",
                    "author_id",
                    "reviewed_by",
                    "section_id",
                    "category_id",
                    "division_id",
                    "district_id",
                    "upazila_id",
                    "source",
                    "meta_title",
                    "meta_description",
                    "meta_keywords",
                    "meta_robots",
                    "canonical_url",
                ].forEach(function (n) {
                    setValue(n, n === "scheduled_at" ? p.scheduled_at_input : p[n]);
                });
                $("#post-form [name=status]").trigger("change");
                showEditorialHistory(p.editorial_history);
                $("textarea[name=schema_markup]").val(p.schema_markup ? JSON.stringify(p.schema_markup) : "");
                setEditorHtml(p.description || "");
                $("#allow-comments").prop("checked", !!p.allow_comments);
                $("#is-breaking").prop("checked", !!p.is_breaking);
                $("#is-featured").prop("checked", !!p.is_featured);
                $("#is-pinned").prop("checked", !!p.is_pinned);
                $("#post-tags").val((p.tag_ids || []).map(String));
                (p.featured_positions || []).forEach(function (v) {
                    $("#place-" + v).prop("checked", true);
                });
                optionFilter($("#post-category"), "section", p.section_id, p.category_id);
                optionFilter($("#post-district"), "division", p.division_id, p.district_id);
                optionFilter($("#post-upazila"), "district", p.district_id, p.upazila_id);
                if (p.featured_image_url) {
                    $("[data-image-preview]").attr("src", p.featured_image_url).removeClass("d-none");
                    $("[data-upload-text]").addClass("d-none");
                }
                $("#existing-gallery").empty();
                $.each(p.images || [], function (_, im) {
                    var $row = $('<div class="existing-image">').append(
                        $("<img>", { src: im.image_url, alt: "" }),
                        $('<span class="flex-grow-1">').text(im.caption || "Gallery image"),
                        $('<label class="btn btn-sm btn-outline-danger mb-0">').append(
                            $("<input>", {
                                type: "checkbox",
                                name: "remove_image_ids[]",
                                value: im.id,
                                class: "d-none",
                            }),
                            $("<span>").text("Remove")
                        )
                    );
                    $row.find("input").on("change", function () {
                        $row.toggleClass("opacity-50", this.checked);
                        $row.find("span:last").text(this.checked ? "Restore" : "Remove");
                    });
                    $("#existing-gallery").append($row);
                });
                preview();
                bootstrap.Modal.getOrCreateInstance(e.modal).show();
            })
            .fail(errors)
            .always(function () {
                $b.prop("disabled", false);
            });
    });
    $(document).on("click", "#make-post-slug", function () {
        $("#post-slug").val(slug($("#post-title").val()));
        preview();
    });
    $(document).on("input", "#post-title,#post-slug,#post-summary,#meta-title,#meta-description", preview);
    $(document).on("input", "[name=description]", function () {
        var w = $(this).val().trim().split(/\s+/).filter(Boolean).length;
        $("[data-word-count]").text(w);
        $("[data-read-time]").text((w ? Math.max(1, Math.ceil(w / 220)) : 0) + " min");
    });
    $(document).on("change", "#post-section", function () {
        optionFilter($("#post-category"), "section", this.value);
    });
    $(document).on("change", "#post-division", function () {
        optionFilter($("#post-district"), "division", this.value);
        $("#post-upazila").val("");
        optionFilter($("#post-upazila"), "district", "__none__");
    });
    $(document).on("change", "#post-district", function () {
        optionFilter($("#post-upazila"), "district", this.value);
    });
    $(document).on("click", "#add-gallery", function () {
        $("#gallery-list").append(galleryRow());
    });
    $(document).on("click", ".remove-gallery", function () {
        $(this).closest(".gallery-row").remove();
    });
    $(document).on("change", "#post-featured-image", function () {
        var f = this.files && this.files[0];
        if (!f) return;
        $("[data-image-preview]").attr("src", URL.createObjectURL(f)).removeClass("d-none");
        $("[data-upload-text]").addClass("d-none");
    });
    $(document).on("submit", "#post-form", function (ev) {
        ev.preventDefault();
        var quill = richEditor();
        if (quill) $("#post-description").val(quill.root.innerHTML === "<p><br></p>" ? "" : quill.root.innerHTML);
        var e = elements(),
            data = new FormData(this),
            $s = $("#post-submit");
        data.set("_method", e.form.data("method") || "POST");
        ["allow_comments", "is_breaking", "is_featured", "is_pinned"].forEach(function (n) {
            data.set(n, $("#" + n.replaceAll("_", "-")).is(":checked") ? "1" : "0");
        });
        e.errors.addClass("d-none").empty();
        $("#post-audit").addClass("d-none");
        $s.prop("disabled", true);
        $.ajax({
            url: e.form.data("url"),
            method: "POST",
            data: data,
            processData: false,
            contentType: false,
            headers: { Accept: "application/json" },
        })
            .done(function (r) {
                bootstrap.Modal.getInstance(e.modal).hide();
                refresh(r.message);
            })
            .fail(errors)
            .always(function () {
                $s.prop("disabled", false);
            });
    });
    $(document).on("submit", "#post-filter-form", function (ev) {
        ev.preventDefault();
        var u = this.action + "?" + $(this).serialize();
        if (window.loadAjaxPage) window.loadAjaxPage(u, true);
        else location.href = u;
    });
    $(document)
        .on("focus", ".js-post-status", function () {
            $(this).data("old", this.value);
        })
        .on("change", ".js-post-status", function () {
            var $s = $(this);
            $s.prop("disabled", true);
            $.post($s.data("url"), {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "PATCH",
                status: $s.val(),
            })
                .done(function (r) {
                    refresh(r.message);
                })
                .fail(function (x) {
                    $s.val($s.data("old"));
                    errors(x);
                })
                .always(function () {
                    $s.prop("disabled", false);
                });
        });
    $(document).on("click", ".js-post-delete", function () {
        var u = $(this).data("url"),
            go = function () {
                return $.post(u, { _token: $("meta[name=csrf-token]").attr("content"), _method: "DELETE" })
                    .done(function (r) {
                        refresh(r.message);
                    })
                    .fail(errors);
            };
        if (window.Swal)
            Swal.fire({
                title: "Move post to trash?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
            }).then(function (r) {
                if (r.isConfirmed) go();
            });
        else if (confirm("Move this post to trash?")) go();
    });
})(jQuery);
