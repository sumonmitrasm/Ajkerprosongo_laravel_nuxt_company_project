@extends('admin.layout.layout')

@section('content')
@php
    $avatar = $admin->image
        ? asset('admin/adminimage/'.$admin->image)
        : asset('admin/site_settings/no-image.png');
@endphp
<style>
    .account-workspace { --account-accent:#4054c6; }
    .account-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; margin-bottom:24px; }
    .account-eyebrow { margin-bottom:6px; color:#4054c6; font-size:10px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
    .account-heading h4 { margin:0; color:var(--press-text); font-size:28px; font-weight:780; letter-spacing:-.035em; }
    .account-heading p { margin:6px 0 0; color:var(--press-muted); font-size:13px; }
    .account-status { display:inline-flex; align-items:center; gap:7px; padding:7px 11px; color:var(--press-green); border:1px solid rgba(23,129,90,.2); border-radius:999px; background:rgba(23,129,90,.08); font-size:11px; font-weight:750; }
    .account-status::before { width:7px; height:7px; border-radius:50%; background:currentColor; content:""; box-shadow:0 0 0 4px rgba(23,129,90,.1); }
    .account-shell { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(285px,.75fr); gap:20px; }
    .account-card { overflow:hidden; color:var(--press-text); border:1px solid var(--press-border); border-radius:16px; background:var(--press-surface); box-shadow:0 10px 30px rgba(15,23,42,.045); }
    .account-card-head { padding:20px 22px; border-bottom:1px solid var(--press-border); }
    .account-card-head h5 { margin:0; color:var(--press-text); font-size:15px; font-weight:760; }
    .account-card-head p { margin:4px 0 0; color:var(--press-muted); font-size:11px; }
    .account-card-body { padding:22px; }
    .account-section-title { display:flex; align-items:center; gap:10px; margin:3px 0 17px; color:var(--press-text); font-size:12px; font-weight:780; text-transform:uppercase; letter-spacing:.055em; }
    .account-section-title i { display:grid; width:30px; height:30px; place-items:center; color:#4054c6; border-radius:9px; background:rgba(64,84,198,.1); }
    .account-divider { height:1px; margin:23px 0; background:var(--press-border); }
    .account-workspace .form-label { margin-bottom:7px; color:var(--press-text); font-size:12px; font-weight:700; }
    .account-workspace .form-control { min-height:44px; color:var(--press-text); border-color:var(--press-border); border-radius:10px; background:var(--press-soft); }
    .account-workspace .form-control:focus { border-color:rgba(64,84,198,.58); box-shadow:0 0 0 3px rgba(64,84,198,.1); }
    .account-workspace .form-control[readonly] { color:var(--press-muted); cursor:not-allowed; }
    .account-help { display:block; margin-top:6px; color:var(--press-muted); font-size:10px; line-height:1.5; }
    .account-errors { margin-bottom:18px; padding:12px 14px; color:#a62231; border:1px solid rgba(217,54,69,.26); border-left:3px solid #d93645; border-radius:10px; background:rgba(217,54,69,.07); font-size:12px; }
    .account-save-row { display:flex; align-items:center; justify-content:flex-end; margin-top:22px; }
    .account-save-row .btn { min-width:150px; }
    .account-profile-card { align-self:start; }
    .account-profile-cover { height:82px; background:linear-gradient(125deg,#3042a8,#5267dc 62%,#e31e24); }
    .account-profile-body { margin-top:-49px; padding:0 22px 24px; text-align:center; }
    .account-avatar-wrap { position:relative; display:inline-block; }
    .account-avatar { width:98px; height:98px; object-fit:cover; border:5px solid var(--press-surface); border-radius:26px; background:var(--press-soft); box-shadow:0 10px 24px rgba(15,23,42,.16); }
    .account-avatar-button { position:absolute; right:-5px; bottom:-3px; display:grid; width:32px; height:32px; place-items:center; color:#fff; border:3px solid var(--press-surface); border-radius:50%; background:#4054c6; cursor:pointer; }
    .account-profile-body h5 { margin:14px 0 4px; color:var(--press-text); font-size:18px; font-weight:780; }
    .account-profile-body > p { margin:0; color:var(--press-muted); font-size:12px; }
    .account-role { display:inline-flex; margin-top:12px; padding:5px 10px; color:#4054c6; border-radius:999px; background:rgba(64,84,198,.1); font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
    .account-facts { display:grid; gap:0; margin-top:21px; text-align:left; border-top:1px solid var(--press-border); }
    .account-fact { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:13px 2px; border-bottom:1px solid var(--press-border); font-size:11px; }
    .account-fact span { color:var(--press-muted); }.account-fact strong { color:var(--press-text); font-weight:700; text-align:right; overflow-wrap:anywhere; }
    @media(max-width:991px){.account-shell{grid-template-columns:1fr}.account-profile-card{grid-row:1}.account-heading{align-items:flex-start}}
    @media(max-width:575px){.account-heading{display:block}.account-status{margin-top:13px}.account-card-head,.account-card-body{padding:17px}.account-save-row .btn{width:100%}}
</style>

<div class="app-content main-content account-workspace">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="account-heading">
                <div>
                    <div class="account-eyebrow">Account / Personal workspace</div>
                    <h4>My Account</h4>
                    <p>Manage your newsroom identity and account security.</p>
                </div>
                <span class="account-status">Active account</span>
            </div>

            <form data-my-account-form action="{{ route('admin.account.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="account-shell">
                    <section class="account-card">
                        <div class="account-card-head">
                            <h5>Account details</h5>
                            <p>Keep your byline and contact information accurate.</p>
                        </div>
                        <div class="account-card-body">
                            <div class="account-errors d-none" data-account-errors role="alert"></div>
                            <div class="account-section-title"><i class="fe fe-user"></i>Personal information</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="account-name">Full name</label>
                                    <input class="form-control" id="account-name" name="name" value="{{ $admin->name }}" required maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="account-mobile">Mobile number</label>
                                    <input class="form-control" id="account-mobile" name="mobile" value="{{ $admin->mobile }}" maxlength="30">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="account-email">Email address</label>
                                    <input class="form-control" id="account-email" value="{{ $admin->email }}" readonly>
                                    <small class="account-help">Email changes are restricted to account administrators.</small>
                                </div>
                            </div>

                            <div class="account-divider"></div>
                            <div class="account-section-title"><i class="fe fe-lock"></i>Password & security</div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="current-password">Current password</label>
                                    <input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="new-password">New password</label>
                                    <input class="form-control" id="new-password" name="password" type="password" autocomplete="new-password" minlength="8">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="confirm-password">Confirm new password</label>
                                    <input class="form-control" id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password">
                                </div>
                            </div>
                            <small class="account-help">Leave all password fields empty if you do not want to change your password.</small>
                            <div class="account-save-row">
                                <button class="btn btn-primary" type="submit" data-account-submit><i class="fe fe-check me-1"></i>Save changes</button>
                            </div>
                        </div>
                    </section>

                    <aside class="account-card account-profile-card">
                        <div class="account-profile-cover"></div>
                        <div class="account-profile-body">
                            <div class="account-avatar-wrap">
                                <img class="account-avatar" src="{{ $avatar }}" alt="{{ $admin->name }}" data-account-preview>
                                <label class="account-avatar-button" for="account-image" title="Change profile photo"><i class="fe fe-camera"></i></label>
                                <input class="d-none" id="account-image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-account-image>
                            </div>
                            <h5 data-account-card-name>{{ $admin->name }}</h5>
                            <p>{{ $admin->email }}</p>
                            <span class="account-role">{{ ucfirst($admin->type) }}</span>
                            <div class="account-facts">
                                <div class="account-fact"><span>Account ID</span><strong>{{ $admin->ap_id ?: '#'.$admin->id }}</strong></div>
                                <div class="account-fact"><span>Member since</span><strong>{{ $admin->created_at?->format('M Y') }}</strong></div>
                                <div class="account-fact"><span>Photo</span><strong>JPG, PNG or WebP · 2 MB</strong></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
