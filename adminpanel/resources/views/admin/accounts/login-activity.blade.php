@extends('admin.layout.layout')

@section('content')
<style>
    .activity-page .activity-head { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; margin-bottom:22px; }
    .activity-page .activity-eyebrow { margin-bottom:6px; color:#4054c6; font-size:10px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
    .activity-page .activity-head h4 { margin:0; color:var(--press-text); font-size:28px; font-weight:780; letter-spacing:-.035em; }
    .activity-page .activity-head p { margin:6px 0 0; color:var(--press-muted); font-size:13px; }
    .activity-actions { display:flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:8px; }
    .activity-filter { display:flex; align-items:center; gap:8px; }
    .activity-filter .input-group { width:280px; }.activity-filter .form-control,.activity-filter .input-group-text{min-height:40px;color:var(--press-text);border-color:var(--press-border);background:var(--press-surface)}
    .activity-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
    .activity-stat { display:flex; align-items:center; gap:13px; padding:17px; border:1px solid var(--press-border); border-radius:14px; background:var(--press-surface); box-shadow:0 8px 22px rgba(15,23,42,.035); }
    .activity-stat-icon { display:grid; flex:0 0 42px; width:42px; height:42px; place-items:center; color:#4054c6; border-radius:12px; background:rgba(64,84,198,.1); font-size:18px; }
    .activity-stat:nth-child(2) .activity-stat-icon { color:var(--press-green); background:rgba(23,129,90,.1); }.activity-stat:nth-child(3) .activity-stat-icon{color:#e31e24;background:rgba(227,30,36,.09)}
    .activity-stat span { display:block; color:var(--press-muted); font-size:10px; font-weight:750; text-transform:uppercase; letter-spacing:.055em; }.activity-stat strong{display:block;margin-top:3px;color:var(--press-text);font-size:23px;font-weight:780}
    .activity-card { overflow:hidden; border:1px solid var(--press-border); border-radius:15px; background:var(--press-surface); box-shadow:0 10px 28px rgba(15,23,42,.04); }
    .activity-card-head { display:flex; align-items:center; justify-content:space-between; gap:15px; min-height:66px; padding:15px 19px; border-bottom:1px solid var(--press-border); }
    .activity-card-head h5 { margin:0;color:var(--press-text);font-size:15px;font-weight:760}.activity-card-head span{color:var(--press-muted);font-size:11px}
    .activity-table { margin:0;color:var(--press-text)}.activity-table thead th{padding:12px 18px;color:var(--press-muted);border-color:var(--press-border);background:var(--press-soft);font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}.activity-table tbody td{padding:15px 18px;border-color:var(--press-border);vertical-align:middle}
    .activity-admin { display:flex;align-items:center;gap:10px;min-width:190px}.activity-avatar{width:38px;height:38px;object-fit:cover;border-radius:11px;background:var(--press-soft)}.activity-admin strong{display:block;color:var(--press-text);font-size:12px}.activity-admin small{display:block;margin-top:2px;color:var(--press-muted);font-size:10px}
    .activity-time strong,.activity-client strong{display:block;color:var(--press-text);font-size:12px}.activity-time small,.activity-client small{display:block;margin-top:3px;color:var(--press-muted);font-size:10px}.activity-ip{color:var(--press-text);font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px}
    .device-badge { display:inline-flex;align-items:center;gap:6px;padding:6px 9px;color:#4054c6;border-radius:999px;background:rgba(64,84,198,.1);font-size:10px;font-weight:750}.device-badge i{font-size:12px}
    .activity-empty { padding:54px 20px;text-align:center}.activity-empty i{display:grid;width:52px;height:52px;margin:0 auto 13px;place-items:center;color:#4054c6;border-radius:15px;background:rgba(64,84,198,.1);font-size:22px}.activity-empty strong{display:block;color:var(--press-text)}.activity-empty span{display:block;margin-top:5px;color:var(--press-muted);font-size:11px}
    .activity-card-footer { display:flex;justify-content:flex-end;padding:13px 18px;border-top:1px solid var(--press-border)}
    @media(max-width:767px){.activity-page .activity-head{display:block}.activity-actions{justify-content:flex-start;margin-top:15px}.activity-filter{width:100%}.activity-filter .input-group{width:100%}.activity-summary{grid-template-columns:1fr}.activity-card-head{padding:14px}.activity-card-footer{justify-content:center}.activity-table thead th,.activity-table tbody td{padding-left:14px;padding-right:14px}}
    @media(max-width:480px){.activity-filter{display:grid;grid-template-columns:1fr auto}.activity-filter .input-group{grid-column:1/-1}.activity-actions>[data-activity-prune],.activity-actions>[data-activity-clear]{flex:1}}
</style>

<div class="app-content main-content activity-page">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="activity-head">
                <div>
                    <div class="activity-eyebrow">Account / Security audit</div>
                    <h4>Login Activity</h4>
                    <p>{{ $canViewAll ? 'Successful sign-ins across your newsroom team.' : 'Your recent successful sign-ins and devices.' }}</p>
                </div>
                <div class="activity-actions">
                    <form class="activity-filter" action="{{ route('admin.login-activity') }}" data-activity-filter>
                        @csrf
                        <div class="input-group">
                            <span class="input-group-text"><i class="fe fe-search"></i></span>
                            <input class="form-control" name="search" value="{{ $search }}" placeholder="Search name, IP or device">
                        </div>
                        <button class="btn btn-primary" type="submit">Search</button>
                        @if($search)<a class="btn btn-secondary" href="{{ route('admin.login-activity') }}" data-ajax-page aria-label="Clear search"><i class="fe fe-x"></i></a>@endif
                    </form>
                    @if($canPrune)<button class="btn btn-secondary" type="button" data-activity-prune data-url="{{ route('admin.login-activity.prune') }}"><i class="fe fe-clock me-1"></i>Clear 90+ days</button>@endif
                    @if($canClear)<button class="btn btn-danger" type="button" data-activity-clear data-url="{{ route('admin.login-activity.clear') }}"><i class="fe fe-trash-2 me-1"></i>Delete all</button>@endif
                </div>
            </div>

            <div class="activity-summary">
                <div class="activity-stat"><span class="activity-stat-icon"><i class="fe fe-log-in"></i></span><div><span>Total sign-ins</span><strong>{{ number_format($summary['total']) }}</strong></div></div>
                <div class="activity-stat"><span class="activity-stat-icon"><i class="fe fe-calendar"></i></span><div><span>Last 7 days</span><strong>{{ number_format($summary['week']) }}</strong></div></div>
                <div class="activity-stat"><span class="activity-stat-icon"><i class="fe fe-smartphone"></i></span><div><span>Mobile sign-ins</span><strong>{{ number_format($summary['mobile']) }}</strong></div></div>
            </div>

            <section class="activity-card">
                <div class="activity-card-head">
                    <div><h5>Successful login history</h5><span>Newest activity appears first</span></div>
                    <select class="form-select form-select-sm w-auto" data-server-per-page aria-label="Rows per page">
                        @foreach([10,20,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page',10)===$size)>{{ $size }} rows</option>@endforeach
                    </select>
                </div>
                <div class="table-responsive" tabindex="0" role="region" aria-label="Login activity table">
                    <table class="table activity-table">
                        <thead><tr><th>Time</th><th>Account</th><th>IP address</th><th>Device</th><th>Client</th></tr></thead>
                        <tbody>
                        @forelse($activities as $activity)
                            @php
                                $person = $activity->admin;
                                $personAvatar = $person?->image ? asset('admin/adminimage/'.$person->image) : asset('admin/site_settings/no-image.png');
                                $deviceIcon = match($activity->device){'Mobile'=>'fe-smartphone','Tablet'=>'fe-tablet',default=>'fe-monitor'};
                            @endphp
                            <tr>
                                <td class="activity-time"><strong>{{ $activity->logged_in_at?->timezone('Asia/Dhaka')->format('d M Y') }}</strong><small>{{ $activity->logged_in_at?->timezone('Asia/Dhaka')->format('h:i A') }}</small></td>
                                <td><div class="activity-admin"><img class="activity-avatar" src="{{ $personAvatar }}" alt=""><div><strong>{{ $person?->name ?? 'Deleted account' }}</strong><small>{{ $person?->email ?? 'Account unavailable' }}</small></div></div></td>
                                <td><span class="activity-ip">{{ $activity->ip_address ?: 'Unknown' }}</span></td>
                                <td><span class="device-badge"><i class="fe {{ $deviceIcon }}"></i>{{ $activity->device }}</span></td>
                                <td class="activity-client"><strong>{{ $activity->browser ?: 'Unknown browser' }}</strong><small>{{ $activity->platform ?: 'Unknown platform' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="activity-empty"><i class="fe fe-shield"></i><strong>No login activity yet</strong><span>New successful logins will appear here.</span></div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activities->hasPages())<div class="activity-card-footer">{{ $activities->links() }}</div>@endif
            </section>
        </div>
    </div>
</div>
@endsection
