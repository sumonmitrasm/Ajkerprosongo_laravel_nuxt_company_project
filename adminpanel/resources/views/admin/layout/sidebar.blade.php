<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<div class="sticky">
    <aside class="app-sidebar sidebar-scroll">
        <div class="main-sidebar-header active">
            <a class="desktop-logo logo-light active" href="{{ route('admin.dashboard') }}"><img
                    src="{{ optional($generalSetting)->image ? asset('admin/site_settings/' . basename($generalSetting->image)) : asset('admin/assets/images/brand/logo.png') }}" class="main-logo" alt="logo"></a>
            <a class="desktop-logo logo-dark active" href="{{ route('admin.dashboard') }}"><img
                    src="{{ optional($generalSetting)->image ? asset('admin/site_settings/' . basename($generalSetting->image)) : asset('admin/assets/images/brand/logo1.png') }}" class="main-logo" alt="logo"></a>
            <a class="logo-icon mobile-logo icon-light active" href="{{ route('admin.dashboard') }}"><img
                    src="{{ optional($generalSetting)->favicon ? asset('admin/site_settings/' . basename($generalSetting->favicon)) : asset('admin/assets/images/brand/favicon.png') }}" alt="logo"></a>
            <a class="logo-icon mobile-logo icon-dark active" href="{{ route('admin.dashboard') }}"><img
                    src="{{ optional($generalSetting)->favicon ? asset('admin/site_settings/' . basename($generalSetting->favicon)) : asset('admin/assets/images/brand/favicon1.png') }}" alt="logo"></a>
        </div>
        <div class="main-sidemenu">
            <div class="app-sidebar__user">
                <div class="dropdown user-pro-body text-center">
                    <div class="user-pic">
                        @php
                            $admin = Auth::guard('admin')->user();
                            $canManageAdmins = $admin?->hasModuleAccess('admin', 'view');
                            $canManageSections = $admin?->hasModuleAccess('section', 'view');
                            $canManageCategories = $admin?->hasModuleAccess('category', 'view');
                            $canManageSettings = $admin?->hasModuleAccess('setting', 'view');
                            $canManageTags = $admin?->hasModuleAccess('tag', 'view');
                        @endphp
                        <img src="{{ $admin && $admin->image
                            ? asset('admin/adminimage/' . $admin->image)
                            : asset('admin/site_settings/no-image.png') }}"
                            class="avatar avatar-xl brround mb-1">
                    </div>
                    <div class="user-info text-center">
                        <h5 class=" mb-1 font-weight-bold">{{ Auth::guard('admin')->user()?->name }}</h5>
                        <span
                            class="text-muted app-sidebar__user-name text-sm">{{ ucfirst(Auth::guard('admin')->user()?->type) }}</span>
                    </div>
                </div>
            </div>
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg></div>
            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="26"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span class="side-menu__label">Dashboard</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Dashboard</a></li>
                    </ul>
                </li>
                @if ($canManageAdmins)
                    <li class="slide {{ request()->routeIs('admin-user*') ? 'is-expanded' : '' }}">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="side-menu__icon fe fe-users"></i>
                            <span class="side-menu__label">Account</span><i class="angle fe fe-chevron-right"></i>
                        </a>
                        <ul class="slide-menu">
                            <li><a class="slide-item {{ request()->routeIs('admin-user*') ? 'active' : '' }}" href="{{ route('admin-user') }}">Users</a></li>
                        </ul>
                    </li>
                @endif
                @if ($canManageSections || $canManageCategories)
                    <li class="slide {{ request()->routeIs('section') || request()->routeIs('category') ? 'is-expanded' : '' }}">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="side-menu__icon fe fe-layers"></i>
                            <span class="side-menu__label">Pages</span><i class="angle fe fe-chevron-right"></i>
                        </a>
                        <ul class="slide-menu">
                            @if ($canManageSections)<li><a href="{{ route('section') }}" class="slide-item">Sections</a></li>@endif
                            @if ($canManageCategories)<li><a href="{{ route('category') }}" class="slide-item">Category</a></li>@endif
                        </ul>
                    </li>
                @endif
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="side-menu__icon">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span class="side-menu__label">Forms</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Forms</a>
                        </li>
                        <li><a href="form-elements.html" class="slide-item"> Form Elements</a></li>
                    </ul>
                </li>

                @if ($canManageSettings || $canManageTags)
                <li class="slide {{ request()->routeIs('settings') || request()->routeIs('tags') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                            <rect x="9" y="9" width="6" height="6"></rect>
                            <line x1="9" y1="1" x2="9" y2="4"></line>
                            <line x1="15" y1="1" x2="15" y2="4"></line>
                            <line x1="9" y1="20" x2="9" y2="23"></line>
                            <line x1="15" y1="20" x2="15" y2="23"></line>
                            <line x1="20" y1="9" x2="23" y2="9"></line>
                            <line x1="20" y1="14" x2="23" y2="14"></line>
                            <line x1="1" y1="9" x2="4" y2="9"></line>
                            <line x1="1" y1="14" x2="4" y2="14"></line>
                        </svg>
                        <span class="side-menu__label">Utilities</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Utilities</a>
                        </li>
                        @if ($canManageTags)<li><a href="{{ route('tags') }}" class="slide-item {{ request()->routeIs('tags') ? 'active' : '' }}">Tags</a></li>@endif
                        @if ($canManageSettings)<li><a href="{{ route('settings') }}" class="slide-item {{ request()->routeIs('settings') ? 'active' : '' }}">General Settings</a></li>@endif

                    </ul>
                </li>
                @endif

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                        </svg>
                        <span class="side-menu__label">Charts</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Charts</a>
                        </li>
                        <li><a href="chart-chartist.html" class="slide-item">Chartjs Charts</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                        <span class="side-menu__label">Tables</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Tables</a>
                        </li>
                        <li><a href="tables.html" class="slide-item">Default table</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="side-menu__icon">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span class="side-menu__label">Elements</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Elements</a>
                        </li>
                        <li><a href="accordion.html" class="slide-item"> Accordion</a></li>

                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="side-menu__icon">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                        </svg>
                        <span class="side-menu__label">Icons</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Icons</a>
                        </li>
                        <li><a href="icons.html" class="slide-item"> Font Awesome</a></li>

                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <span class="side-menu__label">Pages</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Pages</a>
                        </li>

                        <li><a href="about.html" class="slide-item"> About Us</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="side-menu__icon">
                            <line x1="4" y1="21" x2="4" y2="14"></line>
                            <line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line>
                            <line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line>
                            <line x1="9" y1="8" x2="15" y2="8"></line>
                            <line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                        <span class="side-menu__label">Submenus</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)"></a>Submenus</li>
                        <li><a href="javascript:void(0)" class="slide-item">Level-1</a></li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0)"><span
                                    class="sub-side-menu__label">Level-2</span><i
                                    class="sub-angle fe fe-chevron-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a class="sub-slide-item" href="javascript:void(0)">Level-2.1</a></li>

                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="side-menu__icon">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="side-menu__label">Error Pages</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1">
                            <a href="javascript:void(0)">Error Pages</a>
                        </li>
                        <li><a href="400.html" class="slide-item"> 400</a></li>

                    </ul>
                </li>

            </ul>
            <div class="app-sidebar-help">
                <div class="dropdown text-center">
                    <div class="help d-flex">
                        <a href="javascript:void(0)" class="nav-link p-0 help-dropdown" data-bs-toggle="dropdown">
                            <span class="font-weight-bold">Help Info</span> <i class="fa fa-angle-down ms-2"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow p-4">
                            <div class="sidebar-dropdown-divider pb-3">
                                <h4 class="font-weight-bold">Help</h4>
                                <a class="d-block" href="javascript:void(0)">Knowledge base</a>
                                <a class="d-block" href="javascript:void(0)">Contact@info.com</a>
                                <a class="d-block" href="javascript:void(0)">88 8888 8888</a>
                            </div>
                            <div class="sidebar-dropdown-divider pb-3 pt-3 mb-3">
                                <p class="mb-1">Your Fax Number</p>
                                <a class="font-weight-bold" href="javascript:void(0)">88 8888 8888</a>
                            </div>
                            <a href="{{route('logout-admin')}}">Logout</a>
                        </div>
                        <div class="ms-auto">
                            <a class="nav-link icon p-0" href="javascript:void(0)">
                                <svg class="header-icon" x="1008" y="1248" viewBox="0 0 24 24" height="100%"
                                    width="100%" preserveAspectRatio="xMidYMid meet" focusable="false">
                                    <path opacity=".3"
                                        d="M12 6.5c-2.49 0-4 2.02-4 4.5v6h8v-6c0-2.48-1.51-4.5-4-4.5z"></path>
                                    <path
                                        d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-11c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2v-5zm-2 6H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6zM7.58 4.08L6.15 2.65C3.75 4.48 2.17 7.3 2.03 10.5h2a8.445 8.445 0 013.55-6.42zm12.39 6.42h2c-.15-3.2-1.73-6.02-4.12-7.85l-1.42 1.43a8.495 8.495 0 013.54 6.42z">
                                    </path>
                                </svg>
                                <span class="pulse "></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg></div>
        </div>
    </aside>
</div>
