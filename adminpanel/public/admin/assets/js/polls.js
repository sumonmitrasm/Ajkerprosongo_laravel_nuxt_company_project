(function ($) {
    'use strict';

    // Return the reusable poll modal and form only when the poll page is present.
    function pollElements() {
        return {
            modal: document.getElementById('poll-form-modal'),
            form: $('#poll-form'),
            options: $('#poll-options-container'),
            errors: $('#poll-form-errors')
        };
    }

    // Build one safe option field with jQuery so database text is never injected as HTML.
    function createOptionRow(option) {
        option = option || {};
        var index = $('#poll-options-container .poll-option-row').length;
        var letter = String.fromCharCode(65 + index);
        var votesCount = Number(option.votes_count || 0);
        var $column = $('<div>', { class: 'col-md-6 poll-option-row' });
        var $field = $('<div>', { class: 'poll-option-field' });
        var $letter = $('<span>').text(letter);
        var $input = $('<input>', {
            type: 'text',
            name: 'options[' + index + '][content]',
            maxlength: 150,
            required: true,
            placeholder: 'Enter an option',
            'aria-label': 'Poll option ' + letter
        }).val(option.content || '');
        var $remove = $('<button>', {
            type: 'button',
            class: 'js-poll-option-remove',
            title: votesCount ? 'Options with votes cannot be removed' : 'Remove option',
            disabled: votesCount > 0
        }).append($('<i>', { class: 'fe fe-trash-2' }));

        // Existing option IDs let the backend update instead of recreating them.
        if (option.id) {
            $field.append($('<input>', {
                type: 'hidden',
                name: 'options[' + index + '][id]',
                value: option.id
            }));
        }

        $field.append($letter, $input, $remove);
        return $column.append($field);
    }

    // Reindex field names and labels after an option is added or removed.
    function reindexOptions() {
        $('#poll-options-container .poll-option-row').each(function (index) {
            var letter = String.fromCharCode(65 + index);
            $(this).find('.poll-option-field > span').text(letter);
            $(this).find('input[type="text"]')
                .attr('name', 'options[' + index + '][content]')
                .attr('aria-label', 'Poll option ' + letter);
            $(this).find('input[type="hidden"]').attr('name', 'options[' + index + '][id]');
        });
    }

    // Rebuild the correct-answer list from the currently written option fields.
    function refreshCorrectOptions(selectedIndex) {
        var $select = $('#correct-option');
        var previousValue = selectedIndex !== undefined ? String(selectedIndex) : String($select.val() || '');
        $select.empty().append($('<option>', { value: '', text: 'No correct answer yet' }));

        $('#poll-options-container .poll-option-row').each(function (index) {
            var content = $(this).find('input[type="text"]').val().trim();
            $select.append($('<option>', {
                value: index,
                text: content || ('Option ' + String.fromCharCode(65 + index))
            }));
        });

        if ($select.find('option[value="' + previousValue + '"]').length) {
            $select.val(previousValue);
        }
    }

    // Convert Laravel validation responses into one readable error panel.
    function showPollErrors(xhr) {
        var elements = pollElements();
        var response = xhr.responseJSON || {};
        var messages = [];

        if (response.errors) {
            $.each(response.errors, function (_, fieldMessages) {
                messages = messages.concat(fieldMessages);
            });
        } else {
            messages.push(response.message || 'The poll could not be saved.');
        }

        elements.errors.html(messages.map(function (message) {
            return $('<div>').text(message).html();
        }).join('<br>')).removeClass('d-none');

        elements.modal.querySelector('.modal-body').scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Reload only the main admin content after a successful poll action.
    function refreshPollPage(message) {
        if (window.loadAjaxPage) {
            window.loadAjaxPage(window.location.href, false);
        } else {
            window.location.reload();
        }

        window.setTimeout(function () {
            if (window.Swal) Swal.fire({ icon: 'success', title: message, timer: 1600, showConfirmButton: false });
        }, 250);
    }

    // Open a clean create form with two required blank options.
    $(document).on('click', '.js-poll-create', function () {
        var elements = pollElements();
        if (!elements.modal) return;

        elements.form[0].reset();
        elements.form.data({ url: elements.form.data('store-url'), method: 'POST' });
        elements.errors.addClass('d-none').empty();
        elements.options.empty();
        elements.options.append(createOptionRow());
        elements.options.append(createOptionRow());
        reindexOptions();
        $('#poll-form-title').text('Create New Poll');
        $('#poll-form-submit').html('<i class="fe fe-save me-1"></i>Save Poll');
        $('#result-visibility').val('after_end');
        $('#poll-status').val('draft');
        $('#allow-poll-comments').prop('checked', true);
        $('#poll-image-preview').addClass('d-none').attr('src', '');
        $('.poll-upload-placeholder').removeClass('d-none');
        refreshCorrectOptions('');
        bootstrap.Modal.getOrCreateInstance(elements.modal).show();
    });

    // Load a poll and fill the same modal for editing.
    $(document).on('click', '.js-poll-edit', function () {
        var elements = pollElements();
        var $button = $(this);
        if (!elements.modal) return;

        $button.prop('disabled', true);
        $.get($button.data('url')).done(function (response) {
            var poll = response.poll;
            elements.form[0].reset();
            elements.form.data({ url: $button.data('update-url'), method: 'PUT' });
            elements.errors.addClass('d-none').empty();
            $('#poll-form-title').text('Edit Poll');
            $('#poll-form-submit').html('<i class="fe fe-save me-1"></i>Update Poll');
            $('#poll-title').val(poll.title);
            $('#poll-description').val(poll.description || '');
            $('#poll-type').val(poll.poll_type);
            $('#result-visibility').val(poll.result_visibility);
            $('#poll-start').val(poll.starts_at_input || '');
            $('#poll-end').val(poll.ends_at_input || '');
            $('#poll-status').val(poll.status);
            $('#allow-poll-comments').prop('checked', Boolean(poll.allow_comments));

            elements.options.empty();
            $.each(poll.options || [], function (_, option) {
                elements.options.append(createOptionRow(option));
            });
            var selectedAnswerIndex = (poll.options || []).findIndex(function (option) {
                return String(option.id) === String(poll.correct_option_id || '');
            });
            refreshCorrectOptions(selectedAnswerIndex >= 0 ? selectedAnswerIndex : '');

            if (poll.image_url) {
                $('#poll-image-preview').attr('src', poll.image_url).removeClass('d-none');
                $('.poll-upload-placeholder').addClass('d-none');
            } else {
                $('#poll-image-preview').addClass('d-none').attr('src', '');
                $('.poll-upload-placeholder').removeClass('d-none');
            }

            bootstrap.Modal.getOrCreateInstance(elements.modal).show();
        }).fail(showPollErrors).always(function () {
            $button.prop('disabled', false);
        });
    });

    // Add up to twenty options while keeping names correctly indexed.
    $(document).on('click', '#poll-add-option', function () {
        var $container = $('#poll-options-container');
        if ($container.find('.poll-option-row').length >= 20) return;
        $container.append(createOptionRow());
        reindexOptions();
        refreshCorrectOptions();
        $container.find('input[type="text"]').last().trigger('focus');
    });

    // Keep at least two options in every poll form.
    $(document).on('click', '.js-poll-option-remove', function () {
        var $rows = $('#poll-options-container .poll-option-row');
        if ($rows.length <= 2) {
            if (window.Swal) Swal.fire('At least two options are required.');
            return;
        }
        $(this).closest('.poll-option-row').remove();
        reindexOptions();
        refreshCorrectOptions();
    });

    // Keep correct-answer labels synchronized while the admin types options.
    $(document).on('input', '#poll-options-container input[type="text"]', function () {
        refreshCorrectOptions();
    });

    // Preview a selected local image before it is uploaded.
    $(document).on('change', '#poll-image', function () {
        var file = this.files && this.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        $('#poll-image-preview').attr('src', URL.createObjectURL(file)).removeClass('d-none');
        $('.poll-upload-placeholder').addClass('d-none');
    });

    // Submit multipart create/update requests without reloading the layout.
    $(document).on('submit', '#poll-form', function (event) {
        event.preventDefault();
        var elements = pollElements();
        var data = new FormData(this);
        var $submit = $('#poll-form-submit');

        data.set('_method', elements.form.data('method') || 'POST');
        data.set('allow_comments', $('#allow-poll-comments').is(':checked') ? '1' : '0');
        elements.errors.addClass('d-none').empty();
        $submit.prop('disabled', true);

        $.ajax({
            url: elements.form.data('url'),
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            headers: { Accept: 'application/json' }
        }).done(function (response) {
            bootstrap.Modal.getInstance(elements.modal).hide();
            refreshPollPage(response.message);
        }).fail(showPollErrors).always(function () {
            $submit.prop('disabled', false);
        });
    });

    // Apply list filters through the existing partial-page loader.
    $(document).on('submit', '#poll-filter-form', function (event) {
        event.preventDefault();
        var url = this.action + '?' + $(this).serialize();
        if (window.loadAjaxPage) window.loadAjaxPage(url, true);
        else window.location.href = url;
    });

    // Update status directly from the list and restore it if the request fails.
    $(document).on('focus', '.js-poll-status', function () {
        $(this).data('previous', this.value);
    }).on('change', '.js-poll-status', function () {
        var $select = $(this);
        $select.prop('disabled', true);
        $.post($select.data('url'), {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PATCH',
            status: $select.val()
        }).done(function (response) {
            refreshPollPage(response.message);
        }).fail(function (xhr) {
            $select.val($select.data('previous'));
            showPollErrors(xhr);
        }).always(function () {
            $select.prop('disabled', false);
        });
    });

    // Ask for confirmation before permanently deleting a poll and its responses.
    $(document).on('click', '.js-poll-delete', function () {
        var url = $(this).data('url');
        var removePoll = function () {
            return $.post(url, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            }).done(function (response) {
                refreshPollPage(response.message);
            }).fail(showPollErrors);
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Delete this poll?',
                text: 'Its options, votes and comments will also be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it'
            }).then(function (result) {
                if (result.isConfirmed) removePoll();
            });
        } else if (window.confirm('Delete this poll and all of its responses?')) {
            removePoll();
        }
    });
}(jQuery));
