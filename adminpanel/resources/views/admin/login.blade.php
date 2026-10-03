<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ optional($generalSetting)->description ?? '' }}">
    <meta name="keywords" content="{{ optional($generalSetting)->meta_keywords ?? '' }}">
    <title>{{ optional($generalSetting)->side_name ?? 'Admin Login' }}</title>
    <link rel="icon" href="{{ optional($generalSetting)->favicon ? asset('admin/site_settings/'.basename($generalSetting->favicon)) : asset('admin/assets/images/brand/favicon.ico') }}" type="image/x-icon">
    <link href="{{ url('admin/assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        :root { --ink:#101828; --muted:#667085; --line:#d8dce5; --red:#e31e24; --red-dark:#bd1218; --navy:#11182d; }
        * { box-sizing:border-box; }
        html,body { min-height:100%; }
        body { margin:0; color:var(--ink); background:#f8f9fc; font-family:Inter,"Segoe UI",Arial,sans-serif; -webkit-font-smoothing:antialiased; }
        button,input { font:inherit; }
        .auth-page { display:grid; grid-template-columns:minmax(420px,46%) 1fr; min-height:100vh; }

        .brand-panel { position:relative; isolation:isolate; display:flex; min-height:100vh; padding:clamp(32px,5vw,72px); overflow:hidden; color:#fff; background:radial-gradient(circle at 15% 15%,rgba(227,30,36,.2),transparent 30%),linear-gradient(145deg,#0c1224 0%,var(--navy) 50%,#253357 100%); }
        .brand-panel::before { content:""; position:absolute; inset:0; z-index:-1; opacity:.13; background-image:linear-gradient(rgba(255,255,255,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.2) 1px,transparent 1px); background-size:42px 42px; mask-image:linear-gradient(to bottom right,#000,transparent 72%); }
        .brand-panel::after { content:""; position:absolute; right:-120px; bottom:-130px; z-index:-1; width:390px; height:390px; border:78px solid rgba(227,30,36,.11); border-radius:50%; }
        .brand-content { display:flex; flex:1; max-width:580px; flex-direction:column; justify-content:space-between; }
        .logo-wrap { display:inline-flex; align-self:flex-start; align-items:center; min-height:66px; padding:13px 20px; border:1px solid rgba(255,255,255,.2); border-radius:15px; background:#fff; box-shadow:0 14px 38px rgba(0,0,0,.18); }
        .brand-logo { display:block; width:auto; max-width:230px; height:42px; object-fit:contain; }
        .brand-message { margin:auto 0; padding:70px 0; }
        .eyebrow { display:flex; align-items:center; gap:10px; margin-bottom:20px; color:#ff9498; font-size:12px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        .eyebrow::before { content:""; width:30px; height:2px; border-radius:10px; background:var(--red); }
        .brand-message h1 { max-width:530px; margin:0 0 24px; color:#fff; font-family:Georgia,"Times New Roman",serif; font-size:clamp(38px,4.2vw,62px); font-weight:700; line-height:1.08; letter-spacing:-.035em; }
        .brand-message p { max-width:480px; margin:0; color:#b7c0d8; font-size:16px; line-height:1.75; }
        .brand-footer { display:flex; align-items:center; gap:10px; color:#8993ad; font-size:13px; }
        .secure-dot { width:8px; height:8px; border-radius:50%; background:#35c98b; box-shadow:0 0 0 5px rgba(53,201,139,.12); }

        .form-panel { position:relative; display:flex; min-height:100vh; align-items:center; justify-content:center; padding:48px clamp(24px,6vw,96px); background:#f8f9fc; }
        .form-panel::before { content:""; position:absolute; top:0; right:0; width:180px; height:180px; background:linear-gradient(225deg,rgba(227,30,36,.07),transparent 67%); pointer-events:none; }
        .login-card { width:100%; max-width:460px; }
        .mobile-logo { display:none; margin-bottom:38px; }
        .welcome-badge { display:inline-flex; align-items:center; gap:8px; margin-bottom:18px; padding:7px 11px; color:var(--red-dark); border:1px solid #ffd3d5; border-radius:999px; background:#fff4f4; font-size:12px; font-weight:700; }
        .welcome-badge span { width:6px; height:6px; border-radius:50%; background:var(--red); }
        .login-card h2 { margin:0 0 10px; color:var(--ink); font-size:clamp(30px,3vw,38px); font-weight:750; letter-spacing:-.035em; }
        .login-subtitle { margin:0 0 34px; color:var(--muted); font-size:15px; line-height:1.6; }
        .form-group { margin-bottom:20px; }
        .form-label { display:block; margin-bottom:8px; color:#344054; font-size:13px; font-weight:700; }
        .input-shell { position:relative; }
        .input-icon { position:absolute; top:50%; left:17px; width:19px; height:19px; color:#98a2b3; transform:translateY(-50%); pointer-events:none; }
        .auth-input { width:100%; height:56px; padding:0 48px 0 50px; color:var(--ink); border:1px solid var(--line); border-radius:12px; outline:0; background:#fff; font-size:14px; box-shadow:0 1px 2px rgba(16,24,40,.03); transition:border-color .2s,box-shadow .2s; }
        .auth-input::placeholder { color:#98a2b3; }
        .auth-input:hover { border-color:#b7bec9; }
        .auth-input:focus { border-color:var(--red); box-shadow:0 0 0 4px rgba(227,30,36,.1); }
        .password-toggle { position:absolute; top:50%; right:12px; display:grid; width:36px; height:36px; padding:0; place-items:center; color:#667085; border:0; border-radius:8px; background:transparent; transform:translateY(-50%); cursor:pointer; }
        .password-toggle:hover { color:var(--ink); background:#f2f4f7; }
        .password-toggle:focus-visible { outline:3px solid rgba(227,30,36,.18); }
        .password-toggle svg { width:19px; height:19px; }
        .login-button { display:flex; width:100%; height:56px; align-items:center; justify-content:center; gap:10px; margin-top:28px; color:#fff; border:0; border-radius:12px; background:linear-gradient(135deg,var(--red),#c8141a); font-size:15px; font-weight:750; box-shadow:0 10px 24px rgba(227,30,36,.22); cursor:pointer; transition:transform .2s,box-shadow .2s,opacity .2s; }
        .login-button:hover { color:#fff; transform:translateY(-1px); box-shadow:0 14px 30px rgba(227,30,36,.29); }
        .login-button:focus-visible { outline:3px solid rgba(227,30,36,.25); outline-offset:3px; }
        .login-button:disabled { opacity:.7; cursor:wait; transform:none; }
        .login-button svg { width:18px; height:18px; transition:transform .2s; }
        .login-button:hover svg { transform:translateX(3px); }
        .security-note { display:flex; align-items:center; justify-content:center; gap:7px; margin-top:23px; color:#98a2b3; font-size:12px; }
        .security-note svg { width:14px; height:14px; }

        .auth-alert { position:relative; margin-bottom:22px; padding:13px 42px 13px 44px; color:#9b1c1c; border:1px solid #fecaca; border-radius:11px; background:#fff1f2; font-size:13px; line-height:1.5; }
        .auth-alert::before { content:"!"; position:absolute; top:13px; left:15px; display:grid; width:19px; height:19px; place-items:center; color:#fff; border-radius:50%; background:#dc2626; font-size:12px; font-weight:800; }
        .auth-alert ul { margin:0; padding-left:16px; }
        .alert-close { position:absolute; top:8px; right:10px; padding:4px 7px; color:inherit; border:0; background:transparent; font-size:20px; line-height:1; opacity:.6; cursor:pointer; }

        @media (max-width:900px) { .auth-page{grid-template-columns:minmax(340px,42%) 1fr}.brand-panel{padding:38px}.brand-message h1{font-size:40px} }
        @media (max-width:720px) { .auth-page{display:block}.brand-panel{display:none}.form-panel{min-height:100svh;padding:36px 22px;background:#fff}.mobile-logo{display:inline-flex}.login-card{max-width:430px}.login-subtitle{margin-bottom:28px} }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after{scroll-behavior:auto!important;transition:none!important} }
    </style>
</head>
<body>
    @php
        $logo = optional($generalSetting)->image
            ? asset('admin/site_settings/'.basename($generalSetting->image))
            : asset('admin/assets/images/brand/logo.png');
    @endphp

    <main class="auth-page">
        <section class="brand-panel" aria-label="Company introduction">
            <div class="brand-content">
                <div class="logo-wrap">
                    <img src="{{ $logo }}" alt="{{ optional($generalSetting)->side_name ?? 'Company logo' }}" class="brand-logo">
                </div>
                <div class="brand-message">
                    <div class="eyebrow">Admin workspace</div>
                    <h1>Newsroom control, made simple.</h1>
                    <p>Manage stories, publishing and your digital newsroom from one secure, focused workspace.</p>
                </div>
                <div class="brand-footer"><span class="secure-dot" aria-hidden="true"></span>Secure administration portal</div>
            </div>
        </section>

        <section class="form-panel">
            <div class="login-card">
                <div class="logo-wrap mobile-logo">
                    <img src="{{ $logo }}" alt="{{ optional($generalSetting)->side_name ?? 'Company logo' }}" class="brand-logo">
                </div>
                <div class="welcome-badge"><span aria-hidden="true"></span>Welcome back</div>
                <h2>Sign in to your account</h2>
                <p class="login-subtitle">Enter your credentials to continue to the admin dashboard.</p>

                @if ($errors->any())
                    <div class="auth-alert" role="alert">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
                    </div>
                @endif
                @if (Session::has('error_message'))
                    <div class="auth-alert" role="alert">
                        {{ Session::get('error_message') }}
                        <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
                    </div>
                @endif
                <div id="alertContainer" aria-live="polite"></div>

                <form id="loginForm" method="POST" action="{{ route('admin.login') }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-shell">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                            <input id="email" type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-shell">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input id="password" type="password" name="password" class="auth-input" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" id="togglePassword" class="password-toggle" aria-label="Show password" aria-pressed="false">
                                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" id="loginBtn" class="login-button">
                        <span>Sign in securely</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                    </button>
                </form>
                <div class="security-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                    Your connection is encrypted and secure
                </div>
            </div>
        </section>
    </main>

    <script src="{{ url('admin/assets/js/vendors/jquery.min.js') }}"></script>
    <script>
        (function () {
            const password = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            toggle.addEventListener('click', function () {
                const show = password.type === 'password';
                password.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                toggle.setAttribute('aria-pressed', String(show));
                eyeIcon.innerHTML = show
                    ? '<path d="m3 3 18 18"></path><path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path><path d="M9.9 4.2A11 11 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-2 3.1"></path><path d="M6.6 6.6C3.5 8.6 2 12 2 12s3.5 8 10 8a10 10 0 0 0 5.4-1.6"></path>'
                    : '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.5"></circle>';
            });

            document.querySelectorAll('.alert-close').forEach(function (button) {
                button.addEventListener('click', function () { button.parentElement.remove(); });
            });

            $('#loginForm').on('submit', function (event) {
                event.preventDefault();
                const form = $(this);
                const button = $('#loginBtn');
                const original = button.html();
                const container = $('#alertContainer');
                button.prop('disabled', true).find('span').text('Signing in...');
                container.empty();

                $.ajax({
                    url: form.attr('action'), type: 'POST', data: form.serialize(),
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), Accept: 'application/json'},
                    success: function (response) {
                        if (response.status === true) { window.location.replace(response.redirect_url); return; }
                        showAlert(response.message || 'Unable to sign in. Please check your details.'); reset();
                    },
                    error: function (xhr) {
                        let messages = ['An error occurred. Please try again.'];
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            messages = Object.values(xhr.responseJSON.errors).map(function (errors) { return errors[0]; });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) { messages = [xhr.responseJSON.message]; }
                        showAlert(messages); reset();
                    }
                });

                function showAlert(messages) {
                    const values = Array.isArray(messages) ? messages : [messages];
                    const alert = $('<div>', {class:'auth-alert', role:'alert'});
                    const list = $('<ul>');
                    values.forEach(function (message) { list.append($('<li>').text(message)); });
                    const close = $('<button>', {type:'button', class:'alert-close', 'aria-label':'Close alert', html:'&times;'}).on('click', function () { alert.remove(); });
                    container.append(alert.append(list, close));
                }
                function reset() { button.prop('disabled', false).html(original); }
            });
        })();
    </script>
</body>
</html>
