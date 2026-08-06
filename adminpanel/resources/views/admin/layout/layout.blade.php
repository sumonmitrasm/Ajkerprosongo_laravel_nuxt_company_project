<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

    <!-- Meta data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta content="Dashtic - Bootstrap Webapp Responsive Dashboard Simple Admin Panel Premium HTML5 Template" name="description">
    <meta content="Spruko Technologies Private Limited" name="author">
    <meta name="keywords" content="admin, admin template, dashboard, admin dashboard, bootstrap 5, responsive, clean, ui, admin panel, ui kit, responsive admin, application, bootstrap 4, flat, bootstrap5, admin dashboard template"
    />

    <!-- Title -->
    <title>{{ optional($generalSetting)->side_name ?? '' }}</title>

    <!--Favicon -->
    <link rel="icon" href="{{ optional($generalSetting)->favicon ? asset('admin/site_settings/' . basename($generalSetting->favicon)) : url('admin/assets/images/brand/favicon.ico') }}" type="image/x-icon" />

    <!-- Bootstrap css -->
    <link id="style" href="{{ url('admin/assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

    <!-- Style css -->
    <link href="{{ url('admin/assets/css/style.css') }}" rel="stylesheet" />

    <!-- Plugin css -->
    <link href="{{ url('admin/assets/css/plugin.css') }}" rel="stylesheet" />

    <!-- Animate css -->
    <link href="{{ url('admin/assets/css/animated.css') }}" rel="stylesheet" />

    <!---Icons css-->
    <link href="{{ url('admin/assets/plugins/web-fonts/icons.css') }}" rel="stylesheet" />
    <link href="{{ url('admin/assets/plugins/web-fonts/font-awesome/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ url('admin/assets/plugins/web-fonts/plugin.css') }}" rel="stylesheet" />

    <style>
        .table-list-toolbar {
            min-height: 38px;
        }

        .table-list-search {
            margin-left: 0 !important;
        }

        .table-list-search .input-group {
            width: 260px;
        }

        .table-list-search .input-group-text,
        .table-list-search .form-control,
        .table-list-search .btn {
            height: 34px;
        }

        @media (max-width: 575.98px) {
            .table-list-search {
                width: 100%;
            }

            .table-list-search .input-group {
                flex: 1;
            }
        }
    </style>

</head>

<body class="main-body app sidebar-mini light-mode ltr">

    <!---Global-loader-->
    <div id="global-loader">
        <img src="{{ asset('admin/assets/images/svgs/loader.svg') }}" alt="Loading">
    </div>

    <div class="page">
        <div class="page-main">

            <!--app header-->
            @include('admin.layout.header')
            <!--/app header-->

           <!-- main-sidebar -->
            @include('admin.layout.sidebar')
			<!-- main-sidebar -->

            <!-- app-content start-->
            <main id="ajax-page-content">
                @yield('content')
            </main>
            <!-- app-content end-->

        </div>

        <!--Footer-->
        @include('admin.layout.footer')
        <!-- End Footer-->

    </div>

    <!-- Back to top -->
    <a href="#top" id="back-to-top">
        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 12l1.41 1.41L11 7.83V20h2V7.83l5.58 5.59L20 12l-8-8-8 8z"/></svg>
    </a>

    <!-- Jquery js-->
    <script src="{{ url('admin/assets/js/vendors/jquery.min.js') }}"></script>

    <!-- Bootstrap5 js-->
    <script src="{{ url('admin/assets/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ url('admin/assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

    <!--Othercharts js-->
    <script src="{{ url('admin/assets/plugins/othercharts/jquery.sparkline.min.js') }}"></script>

    <!-- Circle-progress js-->
    <script src="{{ url('admin/assets/js/vendors/circle-progress.min.js') }}"></script>

    <!-- Jquery-rating js-->
    <script src="{{ url('admin/assets/plugins/rating/jquery.rating-stars.js') }}"></script>

    <!-- P-scroll js-->
    <script src="{{ url('admin/assets/plugins/p-scrollbar/p-scrollbar.js') }}"></script>

    <!--Sidemenu js-->
    <script src="{{ url('admin/assets/plugins/sidemenu/sidemenu.js') }}"></script>

    <!-- Sticky js -->
    <script src="{{ url('admin/assets/js/sticky.js') }}"></script>

    <!--Moment js-->
    <script src="{{ url('admin/assets/plugins/moment/moment.js') }}"></script>

    <!-- Admin AJAX navigation, pagination, export, and reusable CRUD -->
    <script>
        $(function () {
            // Loads only #ajax-page-content so header/sidebar stay in place.
            window.loadAjaxPage = function (url, pushHistory, tablePages) {
                var $content = $('#ajax-page-content');
                $content.css('opacity', '.5');

                $.ajax({
                    url: url
                }).done(function (html) {
                    var $response = $('<div>').append($.parseHTML(html));
                    var $newContent = $response.find('#ajax-page-content').first();

                    if (!$newContent.length) {
                        window.location.href = url;
                        return;
                    }

                    $content.html($newContent.html()).css('opacity', '1');
                    window.initServerSearch();
                    $('.side-menu .slide-item').removeClass('active');
                    $('.side-menu a[href="' + url + '"]').addClass('active');

                    if (pushHistory) {
                        window.history.pushState({}, '', url);
                    }

                }).fail(function () {
                    window.location.href = url;
                }).always(function () {
                    $content.css('opacity', '1');
                });
            };

            $(document).on('click', '.side-menu a[href]', function (event) {
                var href = $(this).attr('href');

                if (!href || href === '#' || href.indexOf('javascript:') === 0 || this.target ||
                    event.ctrlKey || event.metaKey || event.shiftKey || event.which === 2) {
                    return;
                }

                var url = new URL(this.href, window.location.href);

                if (url.origin !== window.location.origin) {
                    return;
                }

                event.preventDefault();
                window.loadAjaxPage(url.href, true);
            });

            $(document).on('click', '[data-ajax-page]', function (event) {
                var url = new URL(this.href, window.location.href);

                if (url.origin !== window.location.origin || this.target || event.ctrlKey || event.metaKey || event.shiftKey || event.which === 2) {
                    return;
                }

                event.preventDefault();
                window.loadAjaxPage(url.href, true);
            });

            $(document).on('click', '#ajax-page-content .pagination a', function (event) {
                event.preventDefault();
                window.loadAjaxPage(this.href, true);
            });

            $(document).on('change', '[data-server-per-page]', function () {
                var url = new URL(window.location.href);
                url.searchParams.set('per_page', this.value);
                url.searchParams.delete('page');
                window.loadAjaxPage(url.href, true);
            });

            window.initServerSearch = function () {
                $('[data-server-pagination]').each(function () {
                    var $table = $(this);
                    if ($table.data('server-search-ready')) return;
                    var $toolbar = $table.closest('.card-body').find('[data-server-per-page]').first().closest('div');
                    if (!$toolbar.length) return;
                    $table.data('server-search-ready', true);
                    $toolbar.addClass('flex-wrap table-list-toolbar');
                    var search = new URL(window.location.href).searchParams.get('search') || '';
                    $toolbar.append('<form class="d-flex align-items-center gap-2 table-list-search" data-server-search><label class="mb-0 fw-semibold" for="table-search-' + $table.attr('id') + '">Search</label><div class="input-group input-group-sm"><input id="table-search-' + $table.attr('id') + '" type="search" class="form-control" name="search" placeholder="Search records..." value="' + $('<div>').text(search).html() + '"><button class="btn btn-primary" type="submit" aria-label="Search"><i class="fa fa-search"></i></button></div></form>');
                });
            };

            window.initServerSearch();

            $(document).on('submit', '[data-server-search]', function (event) {
                event.preventDefault();
                var url = new URL(window.location.href), search = $(this).find('[name="search"]').val().trim();
                if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
                url.searchParams.delete('page');
                window.loadAjaxPage(url.href, true);
            });

            $(document).on('click', '[data-table-export]', function () {
                var $table = $($(this).data('table-export'));
                if (!$table.length) return;
                var type = $(this).data('table-export-type');
                var rows = [];
                $table.find('tr').each(function () { rows.push($(this).find('th,td').map(function () { return $(this).text().trim(); }).get()); });
                var blob, name = $table.attr('id') || 'export';
                if (type === 'word') {
                    blob = new Blob(['<html><head><meta charset="utf-8"></head><body>' + $table.prop('outerHTML') + '</body></html>'], { type: 'application/msword' });
                    name += '.doc';
                } else {
                    blob = new Blob(['\ufeff' + rows.map(function (row) { return row.map(function (cell) { return '"' + cell.replace(/"/g, '""') + '"'; }).join(','); }).join('\n')], { type: 'text/csv;charset=utf-8' });
                    name += '.csv';
                }
                var link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = name; link.click(); URL.revokeObjectURL(link.href);
            });

            window.addEventListener('popstate', function () {
                window.loadAjaxPage(window.location.href, false);
            });

            function showUserFormErrors(errors) {
                var messages = $.map(errors, function (items) { return items.join('<br>'); });
                $('#user-form-errors').html(messages.join('<br>')).removeClass('d-none');
            }

            function saveUserForm(url, method) {
                var $form = $('#user-form');
                var data = $form.serializeArray();
                data.push({ name: '_method', value: method });

                $('#user-form-submit').prop('disabled', true);
                $.post(url, $.param(data)).done(function (response) {
                    bootstrap.Modal.getInstance(document.getElementById('user-form-modal')).hide();
                    window.loadAjaxPage(window.location.href, false);
                    setTimeout(function () { alert(response.message); }, 250);
                }).fail(function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        showUserFormErrors(xhr.responseJSON.errors);
                    } else {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Request failed.');
                    }
                }).always(function () {
                    $('#user-form-submit').prop('disabled', false);
                });
            }

            $(document).on('click', '.js-user-create', function () {
                var form = document.getElementById('user-form');
                form.reset();
                $(form).data({ url: '{{ route('admin-user.store') }}', method: 'POST' });
                $('#user-form-title').text('Add User');
                $('#password-help').text('(minimum 6 characters)');
                $('#user-form-errors').addClass('d-none').empty();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('user-form-modal')).show();
            });

            $(document).on('click', '.js-user-edit', function () {
                var $button = $(this);
                $.get($button.data('url')).done(function (response) {
                    var user = response.user;
                    var form = document.getElementById('user-form');
                    form.reset();
                    $(form).data({ url: $button.data('update-url'), method: 'PUT' });
                    $.each(['ap_id', 'name', 'email', 'type', 'mobile'], function (_, field) {
                        $(form).find('[name="' + field + '"]').val(user[field] || '');
                    });
                    $(form).find('[name="status"]').val(user.status ? '1' : '0');
                    $('#user-form-title').text('Edit User');
                    $('#password-help').text('(leave blank to keep the current password)');
                    $('#user-form-errors').addClass('d-none').empty();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('user-form-modal')).show();
                }).fail(function () { alert('Could not load this user.'); });
            });

            $(document).on('submit', '#user-form', function (event) {
                event.preventDefault();
                saveUserForm($(this).data('url'), $(this).data('method'));
            });

            $(document).on('click', '.js-user-status', function () {
                var url = $(this).data('url');
                $.post(url, { _token: $('input[name="_token"]').first().val(), _method: 'PATCH' }).done(function (response) {
                    window.loadAjaxPage(window.location.href, false);
                    setTimeout(function () { alert(response.message); }, 250);
                }).fail(function () { alert('Status update failed.'); });
            });

            $(document).on('click', '.js-user-delete', function () {
                var url = $(this).data('url');
                if (!window.confirm('Do you want to delete this user?')) return;

                $.post(url, { _token: $('input[name="_token"]').first().val(), _method: 'DELETE' }).done(function (response) {
                    window.loadAjaxPage(window.location.href, false);
                    setTimeout(function () { alert(response.message); }, 250);
                }).fail(function (xhr) {
                    alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Delete failed.');
                });
            });

            // Reusable CRUD: any module can use this by adding data-crud-* attributes to its form/buttons.
            function crudToast(icon, title) {
                if (window.Swal) {
                    Swal.fire({ position: 'top-end', icon: icon, title: title, showConfirmButton: false, timer: 1500 });
                } else {
                    alert(title);
                }
            }

            function crudErrors($form, errors) {
                var messages = $.map(errors, function (items) { return items.join('<br>'); });
                $form.find('.js-crud-errors').html(messages.join('<br>')).removeClass('d-none');
            }

            function crudModal($button) {
                return $($button.data('crud-modal'));
            }

            function setImagePreview($form, source, $input) {
                var $preview = $();
                if ($input && $input.length) $preview = $form.find('[data-image-preview-for="' + $input.attr('name') + '"]');
                if (!$preview.length) $preview = $form.find('[data-image-preview]').first();
                if (!$preview.length) {
                    $input = $input && $input.length ? $input : $form.find('input[type="file"][data-image-input], input[type="file"][accept*="image"]').first();
                    if (!$input.length) return;
                    $preview = $('<img>', {
                        'data-image-preview': '',
                        alt: 'Selected image'
                    }).addClass('d-none mt-2 rounded border').css({
                        width: '100px',
                        height: '100px',
                        objectFit: 'cover'
                    });
                    $input.after($preview);
                }

                if (source) {
                    $preview.attr('src', source).removeClass('d-none');
                } else {
                    $preview.attr('src', '').addClass('d-none');
                }
            }

            // Refreshes the current server-paginated page after a CRUD action.
            function refreshCrudPage(message) {
                window.loadAjaxPage(window.location.href, false);
                setTimeout(function () { crudToast('success', message); }, 250);
            }

            $(document).on('click', '[data-crud-create]', function () {
                var $button = $(this), $modal = crudModal($button), $form = $modal.find('[data-crud-form]');
                $form[0].reset();
                $form.data({ url: $button.data('store-url'), method: 'POST' });
                $form.find('.js-crud-errors').empty().addClass('d-none');
                $form.find('[name="email"]').prop('readonly', false);
                $form.find('[data-image-preview], [data-image-preview-for]').attr('src', '').addClass('d-none');
                $modal.find('[data-crud-title]').text($button.data('create-title') || 'Add Record');
                $modal.find('[data-password-help]').text('(minimum 6 characters)');
                bootstrap.Modal.getOrCreateInstance($modal[0]).show();
            });

            $(document).on('click', '[data-crud-edit]', function () {
                var $button = $(this), $modal = crudModal($button), $form = $modal.find('[data-crud-form]');
                $.get($button.data('url')).done(function (response) {
                    var record = response.record || response.user || response;
                    $form[0].reset();
                    $form.data({ url: $button.data('update-url'), method: 'PUT' });
                    $.each(record, function (field, value) {
                        var $field = $form.find('[name="' + field + '"]');
                        if (!$field.length || field === 'password' || $field.is('[type="file"]')) return;
                        $field.val(field === 'status' ? (value ? '1' : '0') : (value || ''));
                    });
                    $form.find('[name="email"]').prop('readonly', true);
                    var imageUrl = response.image_url || record.image_url || '';
                    if (!imageUrl && record.image && $form.data('image-base-url')) {
                        imageUrl = $form.data('image-base-url').replace(/\/$/, '') + '/' + record.image;
                    }
                    setImagePreview($form, imageUrl, $form.find('[name="image"]'));
                    setImagePreview($form, response.favicon_url || record.favicon_url || '', $form.find('[name="favicon"]'));
                    $form.find('.js-crud-errors').empty().addClass('d-none');
                    $modal.find('[data-crud-title]').text('Edit Record');
                    $modal.find('[data-password-help]').text('(leave blank to keep the current password)');
                    bootstrap.Modal.getOrCreateInstance($modal[0]).show();
                }).fail(function () { crudToast('error', 'Record load'); });
            });

            $(document).on('change', '[data-crud-form] input[type="file"][data-image-input], [data-crud-form] input[type="file"][accept*="image"]', function () {
                var file = this.files && this.files[0];
                var $form = $(this).closest('[data-crud-form]');
                if (!file) return setImagePreview($form, null, $(this));
                if (!file.type || file.type.indexOf('image/') !== 0) return setImagePreview($form, null, $(this));
                setImagePreview($form, URL.createObjectURL(file), $(this));
            });

            $(document).on('submit', '[data-crud-form]', function (event) {
                event.preventDefault();
                var $form = $(this), data = new FormData(this);
                data.set('_method', $form.data('method') || 'POST');
                $form.find('[data-crud-submit]').prop('disabled', true);
                $.ajax({
                    url: $form.data('url'),
                    method: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    headers: { Accept: 'application/json' }
                }).done(function (response) {
                    bootstrap.Modal.getInstance($form.closest('.modal')[0]).hide();
                    refreshCrudPage(response.message || 'Your work has been saved');
                }).fail(function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) crudErrors($form, xhr.responseJSON.errors);
                    else crudToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Request failed.');
                }).always(function () { $form.find('[data-crud-submit]').prop('disabled', false); });
            });

            $(document).on('change', '#permission-form [data-full-access]', function () {
                var $module = $(this).closest('[data-permission-module]');
                $module.find('[data-access-checkbox]').prop('checked', this.checked);
                if (this.checked) $module.find('[data-no-access]').prop('checked', false);
            });

            $(document).on('change', '#permission-form [data-no-access]', function () {
                if (!this.checked) return;
                var $module = $(this).closest('[data-permission-module]');
                $module.find('[data-access-checkbox], [data-full-access]').prop('checked', false);
            });

            $(document).on('change', '#permission-form [data-access-checkbox]', function () {
                var $module = $(this).closest('[data-permission-module]');
                var allSelected = $module.find('[data-access-checkbox]').length === $module.find('[data-access-checkbox]:checked').length;
                $module.find('[data-full-access]').prop('checked', allSelected);
                if (this.checked) $module.find('[data-no-access]').prop('checked', false);
            });

            $(document).on('submit', '#permission-form', function (event) {
                event.preventDefault();

                var $form = $(this);
                var $button = $form.find('#btn-save-permissions');

                $button.prop('disabled', true);

                $.ajax({
                    url: $form.data('url'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: { Accept: 'application/json' }
                }).done(function (response) {
                    crudToast('success', response.message || 'Permissions updated successfully.');
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Permissions could not be saved.';
                    crudToast('error', message);
                }).always(function () {
                    $button.prop('disabled', false);
                });
            });

            $(document).on('click', '[data-crud-status]', function () {
                var url = $(this).data('url'), token = $('[data-crud-form] input[name="_token"]').first().val();
                $.post(url, { _token: token, _method: 'PATCH' }).done(function (response) {
                    refreshCrudPage(response.message || 'Status updated successfully.');
                }).fail(function () { crudToast('error', 'Status update failed.'); });
            });

            $(document).on('click', '[data-crud-delete]', function () {
                var url = $(this).data('url'), token = $('[data-crud-form] input[name="_token"]').first().val();
                if (!window.Swal) {
                    if (!window.confirm('Do you want to delete this record?')) return;
                    return $.post(url, { _token: token, _method: 'DELETE' }).done(function (response) { refreshCrudPage(response.message); });
                }
                Swal.fire({ title: 'Are you sure?', text: 'This data cannot be recovered.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!', cancelButtonText: 'Cancel' }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post(url, { _token: token, _method: 'DELETE' }).done(function (response) {
                        refreshCrudPage(response.message || 'Deleted successfully.');
                    }).fail(function (xhr) { crudToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.'); });
                });
            });
        });
    $(document).on('click', '[data-crud-permission]', function () {
        var url = $(this).data('url');
        if (url) {
            window.loadAjaxPage(url, true);
        }
    });
    </script>

    <!-- Daterangepicker js-->
    <script src="{{ url('admin/assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ url('admin/assets/js/daterange.js') }}"></script>

    <!--Chart js -->
    <script src="{{ url('admin/assets/plugins/chart/chart.min.js') }}"></script>

    <!-- ECharts js-->
    <script src="{{ url('admin/assets/plugins/echarts/echarts.js') }}"></script>
    <script src="{{ url('admin/assets/js/index2.js') }}"></script>

    <!-- Color Theme js -->
     <script src="{{ url('admin/assets/js/themeColors.js') }}"></script>

	 <!-- Switcher-Styles js -->
    <script src="{{ url('admin/assets/js/switcher-styles.js') }}"></script>

    <!-- Custom js-->
    <script src="{{ url('admin/assets/js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
