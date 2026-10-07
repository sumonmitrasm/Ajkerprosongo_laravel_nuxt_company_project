<!--Footer-->
        <footer class="footer">
            <div class="container">
                <div class="row align-items-center flex-row-reverse">
                    <div class="col-md-12 col-sm-12 mt-3 mt-lg-0 text-center">
                        &copy; {{ optional($generalSetting)->developed_year ?: now()->year }}
                        <a href="javascript:void(0);" class="text-primary">{{ optional($generalSetting)->side_name ?: 'Ajker Proshongo' }}</a>.
                        All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
        <!-- End Footer-->
