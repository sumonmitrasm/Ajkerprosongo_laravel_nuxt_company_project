@extends('admin.layout.layout')
@section('content')
@php
    $admin = Auth::guard('admin')->user();
    // Static design fixtures, not live analytics.
    $metrics = [
        ['file-text','Total news','12,486','124 stories this week','blue'],
        ['check-circle','Published','10,842','86.8% of all stories','green'],
        ['clock','In review','48','Awaiting editorial review','amber'],
        ['users','Visitors','248.6K','14.2% growth this month','violet'],
        ['image','Active ads','24','Across 8 placements','rose'],
        ['user-check','Reporters','36','28 contributed this month','cyan']
    ];
    $reporters = [
        ['1.jpg','Nusrat Jahan','National',86,74,8,'42.8K'],
        ['2.jpg','Rahim Ahmed','Politics',72,65,4,'38.2K'],
        ['3.jpg','Farhana Islam','Business',64,58,3,'31.6K'],
        ['4.jpg','Tanvir Hasan','Sports',58,51,5,'28.4K']
    ];
@endphp
<div class="app-content main-content press-dashboard">
<div class="side-app"><div class="container-fluid main-container">
    <div class="press-heading">
        <div><div class="press-eyebrow">EDITORIAL / OVERVIEW</div><h1>Newsroom overview<span>.</span></h1><p>Your stories, audience and advertising — all in one place.</p></div>
        <div class="press-period"><i class="fe fe-calendar" aria-hidden="true"></i> This month <span class="press-badge">Sample analytics</span></div>
    </div>
    <section class="press-welcome">
        <div class="press-identity">
            <img src="{{ $admin?->image ? asset('admin/adminimage/'.$admin->image) : asset('admin/site_settings/no-image.png') }}" alt="{{ $admin?->name }}">
            <div><small>YOUR WORKSPACE</small><h2>Welcome back, {{ $admin?->name ?? 'Editor' }}</h2><p>{{ ucfirst($admin?->type ?? 'Administrator') }} · Signed in to the newsroom</p></div>
        </div>
        <div class="press-welcome-note"><i class="fe fe-edit-3" aria-hidden="true"></i><span><strong>Every story starts here.</strong><small>Make today’s edition count.</small></span></div>
    </section>
    <div class="press-metrics">
        @foreach($metrics as [$icon,$label,$value,$note,$tone])
        <article class="press-stat"><div><span class="press-icon {{ $tone }}"><i class="fe fe-{{ $icon }}" aria-hidden="true"></i></span><span>{{ $label }}</span></div><strong>{{ $value }}</strong><small>{{ $note }}</small></article>
        @endforeach
    </div>
    <div class="press-grid">
        <section class="press-panel">
            <header><div><h2>Audience growth</h2><p>How readers discover your newsroom</p></div><span class="press-badge">This month</span></header>
            <div class="press-chart-summary"><strong>248,620</strong><span class="press-positive">↗ 14.2%</span><small>visitors this month</small></div>
            <div class="press-line-chart">
                <svg viewBox="0 0 720 210" role="img" aria-label="Sample audience chart: readership rises across four weeks">
                    <defs><linearGradient id="audience-fill" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#4386f4" stop-opacity=".23"/><stop offset="1" stop-color="#4386f4" stop-opacity="0"/></linearGradient></defs>
                    <g class="press-grid-lines"><path d="M45 20H710M45 65H710M45 110H710M45 155H710M45 195H710"/></g>
                    <g class="press-axis"><text x="0" y="25">30K</text><text x="0" y="70">20K</text><text x="0" y="115">10K</text><text x="14" y="195">0</text></g>
                    <path d="M45 160L90 150L135 167L180 110L225 125L270 82L315 107L360 57L405 78L450 47L495 65L540 30L585 53L630 18L675 35L710 20V195H45Z" fill="url(#audience-fill)"/>
                    <path d="M45 160L90 150L135 167L180 110L225 125L270 82L315 107L360 57L405 78L450 47L495 65L540 30L585 53L630 18L675 35L710 20" fill="none" stroke="#4386f4" stroke-width="3" stroke-linejoin="round"/>
                    <path d="M45 180L90 175L135 179L180 160L225 163L270 143L315 152L360 131L405 146L450 110L495 122L540 104L585 118L630 88L675 99L710 81" fill="none" stroke="#24b69a" stroke-width="2.5" stroke-dasharray="5 4"/>
                </svg>
                <div class="press-axis-labels"><span>Week 1</span><span>Week 2</span><span>Week 3</span><span>Week 4</span></div>
            </div>
            <div class="press-legend"><span><i class="blue"></i>Total visitors</span><span><i class="green"></i>Returning readers</span></div>
        </section>
        <section class="press-panel">
            <header><div><h2>Publishing overview</h2><p>Story status distribution</p></div><i class="fe fe-pie-chart" aria-hidden="true"></i></header>
            <div class="press-donut" role="img" aria-label="Sample: 86.8 percent published, 12.8 percent drafts, 0.4 percent review"><div><strong>12,486</strong><small>Total stories</small></div></div>
            <div class="press-distribution"><span><i class="blue"></i>Published <b>10,842</b></span><span><i class="amber"></i>Drafts <b>1,596</b></span><span><i class="rose"></i>In review <b>48</b></span></div>
        </section>
        <section class="press-panel">
            <header><div><h2>Advertising performance</h2><p>Impressions and clicks across placements</p></div><span class="press-badge">Sample report</span></header>
            <div class="press-ad-stats"><div><small>Impressions</small><strong>1.28M</strong></div><div><small>Ad clicks</small><strong>24,680</strong></div><div><small>Click-through rate</small><strong>1.93%</strong></div><div><small>Est. revenue</small><strong>৳ 84,200</strong></div></div>
            <div class="press-bars" role="img" aria-label="Sample advertising impressions and clicks by placement">
                @foreach([[52,26],[68,38],[59,31],[82,49],[72,42],[94,58],[85,53],[100,65]] as $bars)
                <div><span style="height:{{ $bars[0] }}%"></span><span style="height:{{ $bars[1] }}%"></span><small>P{{ $loop->iteration }}</small></div>
                @endforeach
            </div>
            <div class="press-legend"><span><i class="violet"></i>Impressions</span><span><i class="cyan"></i>Clicks (relative scale)</span></div>
        </section>
        <section class="press-panel">
            <header><div><h2>Ad placements</h2><p>Inventory at a glance</p></div><i class="fe fe-layout" aria-hidden="true"></i></header>
            <div class="press-placements">
                @foreach([['Homepage banner',8,90],['Article inline',6,72],['Sidebar display',7,82],['Mobile banner',3,46]] as [$label,$count,$width])
                <div><p>{{ $label }}<strong>{{ $count }} ads</strong></p><div class="press-track"><span style="width:{{ $width }}%"></span></div></div>
                @endforeach
            </div>
        </section>
        <section class="press-panel">
            <header><div><h2>Reporter performance</h2><p>News count and readership · sample people</p></div><span class="press-badge">This month</span></header>
            <div class="press-table-wrap"><table class="press-table"><thead><tr><th>Reporter</th><th>Stories</th><th>Published</th><th>Review</th><th>Views</th></tr></thead><tbody>
                @foreach($reporters as [$photo,$name,$desk,$stories,$published,$review,$views])
                <tr><td><div class="press-person"><img src="{{ asset('admin/assets/images/users/'.$photo) }}" alt=""><span><strong>{{ $name }}</strong><small>{{ $desk }} desk</small></span></div></td><td><b>{{ $stories }}</b></td><td><span class="press-positive">{{ $published }}</span></td><td>{{ $review }}</td><td>{{ $views }}</td></tr>
                @endforeach
            </tbody></table></div>
        </section>
        <section class="press-panel">
            <header><div><h2>Top categories</h2><p>News published this month</p></div><i class="fe fe-layers" aria-hidden="true"></i></header>
            <div class="press-placements">
                @foreach([['National',284,92],['Politics',216,75],['Sports',178,61],['Business',142,48],['Entertainment',96,34]] as [$label,$count,$width])
                <div><p>{{ $label }}<strong>{{ $count }}</strong></p><div class="press-track"><span style="width:{{ $width }}%"></span></div></div>
                @endforeach
            </div>
        </section>
    </div>
    <p class="press-footnote">Analytics, reporter entries and charts are design samples. Your signed-in profile is real.</p>
</div></div>
</div>
@endsection
