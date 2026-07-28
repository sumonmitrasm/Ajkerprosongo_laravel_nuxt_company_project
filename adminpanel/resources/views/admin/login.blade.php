<!doctype html>
<html lang="en" dir="ltr">
    <head>
        <!-- Meta data -->
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta content="{{ optional($generalSetting)->description ?? '' }}" name="description" />
        <meta content="PATHWAY" name="author" />
        <meta name="keywords" content="{{ optional($generalSetting)->meta_keywords ?? '' }}" />
        <!-- Title -->
        <title>{{ optional($generalSetting)->side_name ?? '' }}</title>
        <!--Favicon -->
        {{--
        <link
            rel="icon"
            href="{{ $generalSetting->favicon ? asset('admin/site_settings/'.$generalSetting->favicon) : asset('admin/site_settings/no-image.png') }}"
            type="image/x-icon"
        />
        --}}
        <!--Favicon -->
        <link rel="icon" href="{{ asset('admin/assets/images/brand/favicon.ico') }}" type="image/x-icon" />
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
        <link href="{{ url('admin/assets/plugins/web-fonts/font-awesome/font-awesome.min.css') }}" rel="stylesheet" />
        <link href="{{ url('admin/assets/plugins/web-fonts/plugin.css') }}" rel="stylesheet" />
    </head>

    <body class="main-body light-mode ltr page-style1 page-style2 error-page">
        <div class="page">
            <div class="row"></div>
            <div class="d-md-flex">
                <div class="w-40 bg-style h-100vh page-style">
                    <div class="page-content">
                        <div class="page-single-content">
                            <img
                                src="{{ asset('admin/assets/images/brand/logos.svg') }}"
                                alt="img"
                                class="header-brand-img mb-5"
                            />
                            <div class="card-body text-white py-5 px-8 text-center">
                                <img
                                    src="{{ asset('admin/assets/images/png/blood.jpg') }}"
                                    alt="img"
                                    class="w-100 mx-auto text-center"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-80 page-content">
                    <div class="page-single-content">
                        <div class="card-body p-6">
                            <div class="row">
                                <div class="col-md-8 mx-auto d-block">
                                    <div class="">
                                        <h1 class="mb-2">Login</h1>
                                        <p class="text-muted">Sign In to your account</p>
                                    </div>
                                    @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        @foreach ($errors->all() as $error)
                                        <strong>Error Message: </strong>{{ $error }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <br />
                                        @endforeach
                                    </div>
                                    @endif @if(Session::has('error_message'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>Error Message: </strong>{{Session::get('error_message')}}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif
                                    <div id="alertContainer"></div>
                                    <form
                                        class="pt-3"
                                        id="loginForm"
                                        method="POST"
                                        action="{{ route('admin.login') }}"
                                        novalidate
                                    >
                                        @csrf
                                        <div class="input-group mb-3">
                                            <span class="input-group-addon">
                                                <!-- Icon SVG -->
                                            </span>
                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control"
                                                placeholder="Enter your email"
                                                value="{{ old('email') }}"
                                                autocomplete="email"
                                                required
                                                autofocus
                                            />
                                        </div>

                                        <div class="input-group mb-4">
                                            <span class="input-group-addon">
                                                <!-- Icon SVG -->
                                            </span>
                                            <input
                                                type="password"
                                                name="password"
                                                id="password"
                                                class="form-control"
                                                placeholder="Enter your password"
                                                autocomplete="current-password"
                                                required
                                            />
                                            <span class="input-group-text" id="togglePassword" style="cursor: pointer"
                                                >👁️</span
                                            >
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" id="loginBtn" class="btn btn-primary btn-block">
                                                    <i class="fe fe-arrow-right"></i> Login
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Color Theme js -->
        <script src="{{ url('admin/assets/js/themeColors.js') }}"></script>

        <!-- Switcher-Styles js -->
        <script src="{{ url('admin/assets/js/switcher-styles.js') }}"></script>

        <!-- Custom js-->
        <script src="{{ url('admin/assets/js/custom.js') }}"></script>
        <!-- Include Bootstrap CSS and JavaScript -->
        <script>
            const togglePassword = document.querySelector("#togglePassword");
            const password = document.querySelector("#password");

            togglePassword.addEventListener("click", function (e) {
                const type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);

                if (type === "password") {
                    this.textContent = "👁️";
                } else {
                    this.textContent = "🙈";
                }
            });
        </script>
        <script>
            $(document).ready(function () {
                $("#loginForm").on("submit", function (e) {
                    e.preventDefault();

                    let formData = $(this).serialize();
                    let loginBtn = $("#loginBtn");

                    loginBtn.prop("disabled", true).text("Logging in...");
                    $("#alertContainer").html("");

                    $.ajax({
                        url: $(this).attr("action"),
                        type: "POST",
                        data: formData,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            Accept: "application/json",
                        },
                        success: function (response) {
                            if (response.status === true) {
                                $("#alertContainer").html(`
                        <div class="alert alert-success">
                            ${response.message} Redirecting...
                        </div>
                    `);

                                setTimeout(function () {
                                    window.location.href = response.redirect_url;
                                }, 1000);
                            } else {
                                $("#alertContainer").html(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            ${response.message}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    `);
                                loginBtn.prop("disabled", false).html('<i class="fe fe-arrow-right"></i> Login');
                            }
                        },
                        error: function (xhr) {
                            loginBtn.prop("disabled", false).html('<i class="fe fe-arrow-right"></i> Login');

                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorHtml = '<div class="alert alert-danger alert-dismissible fade show"><ul>';
                                $.each(errors, function (key, value) {
                                    errorHtml += "<li>" + value[0] + "</li>";
                                });
                                errorHtml +=
                                    '</ul><button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                $("#alertContainer").html(errorHtml);
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                $("#alertContainer").html(`
                        <div class="alert alert-danger">${xhr.responseJSON.message}</div>
                    `);
                            } else {
                                $("#alertContainer").html(`
                        <div class="alert alert-danger">An error occurred. Please try again.</div>
                    `);
                            }
                        },
                    });
                });
            });
        </script>
    </body>
</html>
