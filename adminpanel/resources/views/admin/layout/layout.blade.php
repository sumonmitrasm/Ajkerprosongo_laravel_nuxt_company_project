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
    <title>Dashtic - Bootstrap Webapp Responsive Dashboard Simple Admin Panel Premium HTML5 Template</title>

    <!--Favicon -->
    <link rel="icon" href="{{ url('admin/assets/images/brand/favicon.ico') }}" type="image/x-icon" />

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
            @yield('content')
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
</body>

</html>
