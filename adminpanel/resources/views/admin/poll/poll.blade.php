@extends('admin.layout.layout')

@section('content')
    @php
        // Resolve action permissions once so the table and modal stay consistent.
        $admin = Auth::guard('admin')->user();
        $canAddPoll = $admin?->hasModuleAccess('poll', 'add');
        $canEditPoll = $admin?->hasModuleAccess('poll', 'edit');
        $canDeletePoll = $admin?->hasModuleAccess('poll', 'delete');
    @endphp

    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                {{-- Page heading and permission-aware create action. --}}
                <div class="page-header align-items-center">
                    <div class="page-leftheader">
                        <h4 class="page-title mb-1">Online Polling</h4>
                        <p class="text-muted mb-0">Create, schedule and monitor audience polls.</p>
                    </div>
                    @if ($canAddPoll)
                        <div class="page-rightheader ms-auto">
                            <button class="btn btn-primary js-poll-create" type="button">
                                <i class="fe fe-plus me-1"></i> Create Poll
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Database-driven summary cards show current poll activity. --}}
                <div class="row">
                    @foreach ([
                        ['label' => 'Total Polls', 'value' => $summary['total_polls'], 'icon' => 'fe-bar-chart-2', 'color' => 'primary'],
                        ['label' => 'Live Polls', 'value' => $summary['live_polls'], 'icon' => 'fe-radio', 'color' => 'success'],
                        ['label' => 'Total Votes', 'value' => $summary['total_votes'], 'icon' => 'fe-users', 'color' => 'warning'],
                        ['label' => 'Comments', 'value' => $summary['comments'], 'icon' => 'fe-message-square', 'color' => 'info'],
                    ] as $item)
                        <div class="col-xl-3 col-md-6">
                            <div class="card poll-summary-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="poll-summary-icon bg-{{ $item['color'] }}-transparent text-{{ $item['color'] }}"><i class="fe {{ $item['icon'] }}"></i></div>
                                    <div class="ms-3"><span class="text-muted">{{ $item['label'] }}</span><h3 class="mb-0">{{ number_format($item['value']) }}</h3></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Server-side filters keep large poll lists fast and bookmarkable. --}}
                <div class="card">
                    <div class="card-body">
                        <form id="poll-filter-form" action="{{ route('polls') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-5">
                                    <label class="form-label" for="poll-search">Search poll</label>
                                    <div class="input-group"><span class="input-group-text"><i class="fe fe-search"></i></span><input class="form-control" id="poll-search" name="search" type="search" value="{{ $search }}" placeholder="Search by poll title..."></div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label" for="poll-status-filter">Status</label>
                                    <select class="form-select" id="poll-status-filter" name="status"><option value="">All statuses</option>@foreach (['published' => 'Published', 'draft' => 'Draft', 'closed' => 'Closed'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label" for="poll-type-filter">Poll type</label>
                                    <select class="form-select" id="poll-type-filter" name="poll_type"><option value="">All types</option>@foreach (['opinion' => 'Opinion', 'prediction' => 'Prediction', 'quiz' => 'Quiz'] as $value => $label)<option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>@endforeach</select>
                                </div>
                                <div class="col-lg-1"><button class="btn btn-outline-primary w-100" type="submit" title="Apply filters"><i class="fe fe-filter"></i></button></div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Paginated poll table uses counts instead of loading every vote or comment. --}}
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Poll List</h3>
                        <span class="text-muted small">{{ number_format($polls->total()) }} polls found</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 poll-table">
                                <thead><tr><th>Poll</th><th>Type</th><th>Schedule</th><th>Response</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                                <tbody>
                                    @forelse ($polls as $poll)
                                        @php
                                            // Map stored values to readable labels and theme colors.
                                            $statusColor = ['published' => 'success', 'draft' => 'warning', 'closed' => 'secondary'][$poll->status] ?? 'secondary';
                                            $typeIcon = ['opinion' => 'fe-help-circle', 'prediction' => 'fe-award', 'quiz' => 'fe-check-circle'][$poll->poll_type] ?? 'fe-bar-chart-2';
                                            $schedule = $poll->starts_at?->format('d M Y, h:i A') ?? 'Starts immediately';
                                            $scheduleNote = $poll->ends_at ? 'Ends '.$poll->ends_at->format('d M Y, h:i A') : 'No end date';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($poll->image)
                                                        <img class="poll-thumb poll-thumb-image" src="{{ asset('admin/pollimage/'.$poll->image) }}" alt="">
                                                    @else
                                                        <div class="poll-thumb bg-primary-transparent text-primary"><i class="fe {{ $typeIcon }}"></i></div>
                                                    @endif
                                                    <div class="ms-3"><h6 class="mb-1">{{ $poll->title }}</h6><small class="text-muted">{{ $poll->options_count }} options · {{ str_replace('_', ' ', ucfirst($poll->result_visibility)) }}</small></div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark">{{ ucfirst($poll->poll_type) }}</span></td>
                                            <td><small class="d-block">{{ $schedule }}</small><small class="text-muted">{{ $scheduleNote }}</small></td>
                                            <td><strong>{{ number_format($poll->votes_count) }}</strong><small class="d-block text-muted">{{ number_format($poll->comments_count) }} comments</small></td>
                                            <td>
                                                @if ($canEditPoll)
                                                    <select class="form-select form-select-sm js-poll-status" data-url="{{ route('admin-poll.status', $poll) }}" aria-label="Change poll status">
                                                        @foreach (['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'] as $value => $label)<option value="{{ $value }}" @selected($poll->status === $value)>{{ $label }}</option>@endforeach
                                                    </select>
                                                @else
                                                    <span class="badge bg-{{ $statusColor }}-transparent text-{{ $statusColor }}"><span class="poll-status-dot bg-{{ $statusColor }}"></span>{{ ucfirst($poll->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                @if ($canEditPoll)<button class="btn btn-sm btn-outline-info js-poll-edit" type="button" data-url="{{ route('admin-poll.show', $poll) }}" data-update-url="{{ route('admin-poll.update', $poll) }}" title="Edit"><i class="fe fe-edit-2"></i></button>@endif
                                                @if ($canDeletePoll)<button class="btn btn-sm btn-outline-danger js-poll-delete" type="button" data-url="{{ route('admin-poll.delete', $poll) }}" title="Delete"><i class="fe fe-trash-2"></i></button>@endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6"><div class="poll-empty-state"><i class="fe fe-bar-chart-2"></i><h5>No polls found</h5><p>Change the filters or create your first online poll.</p>@if ($canAddPoll)<button class="btn btn-primary js-poll-create" type="button"><i class="fe fe-plus me-1"></i>Create Poll</button>@endif</div></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($polls->hasPages())<div class="card-footer">{{ $polls->onEachSide(1)->links() }}</div>@endif
                </div>
            </div>
        </div>
    </div>

    {{-- One reusable form handles both poll creation and editing. --}}
    <div class="modal fade" id="poll-form-modal" tabindex="-1" aria-labelledby="poll-form-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable poll-modal-dialog">
            <div class="modal-content poll-modal-content">
                <form id="poll-form" action="{{ route('admin-poll.store') }}" method="POST" enctype="multipart/form-data" data-store-url="{{ route('admin-poll.store') }}">
                    @csrf
                    <div class="modal-header poll-modal-header">
                        <div class="d-flex align-items-center"><div class="poll-modal-heading-icon"><i class="fe fe-bar-chart-2"></i></div><div class="ms-3"><h5 class="modal-title mb-1" id="poll-form-title">Create New Poll</h5><p class="text-muted mb-0">Create the question, choices and publishing schedule.</p></div></div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body poll-modal-body">
                        {{-- Validation errors are collected here without reloading the page. --}}
                        <div class="alert alert-danger d-none" id="poll-form-errors" role="alert"></div>
                        <div class="row g-4">
                            <div class="col-xl-7">
                                <section class="poll-editor-card h-100">
                                    <div class="poll-section-heading"><span class="poll-section-number">01</span><div><h6 class="mb-1">Poll information</h6><small class="text-muted">Write a clear question for your audience.</small></div></div>
                                    <div class="mb-3"><label class="form-label" for="poll-title">Poll title <span class="text-danger">*</span></label><input class="form-control form-control-lg" id="poll-title" name="title" type="text" maxlength="255" required placeholder="Example: Who will win the Asia Cup?"><small class="text-muted">Keep the question short and easy to understand.</small></div>
                                    <div class="mb-3"><label class="form-label" for="poll-description">Description</label><textarea class="form-control" id="poll-description" name="description" rows="4" maxlength="5000" placeholder="Add context or instructions for voters (optional)"></textarea></div>
                                    <div><label class="form-label" for="poll-image">Cover image</label><div class="poll-upload-box"><img class="poll-image-preview d-none" id="poll-image-preview" alt="Image preview"><i class="fe fe-image poll-upload-placeholder"></i><div><strong>Choose a cover image</strong><small>JPG, PNG or WebP · Maximum 2 MB</small></div><label class="btn btn-sm btn-outline-primary mb-0" for="poll-image">Browse</label><input class="d-none" id="poll-image" name="image" type="file" accept="image/jpeg,image/png,image/webp"></div></div>
                                </section>
                            </div>
                            <div class="col-xl-5">
                                <section class="poll-editor-card h-100">
                                    <div class="poll-section-heading"><span class="poll-section-number">02</span><div><h6 class="mb-1">Publishing settings</h6><small class="text-muted">Control when and how the poll appears.</small></div></div>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-xl-12"><label class="form-label" for="poll-type">Poll type</label><select class="form-select" id="poll-type" name="poll_type" required><option value="opinion">Opinion</option><option value="prediction">Prediction</option><option value="quiz">Quiz</option></select></div>
                                        <div class="col-md-6 col-xl-12"><label class="form-label" for="result-visibility">Result visibility</label><select class="form-select" id="result-visibility" name="result_visibility" required><option value="always">Always visible</option><option value="after_vote">After visitor votes</option><option value="after_end">After poll ends</option></select></div>
                                        <div class="col-md-6"><label class="form-label" for="poll-start">Start date</label><input class="form-control" id="poll-start" name="starts_at" type="datetime-local"></div>
                                        <div class="col-md-6"><label class="form-label" for="poll-end">End date</label><input class="form-control" id="poll-end" name="ends_at" type="datetime-local"></div>
                                        <div class="col-md-6 col-xl-12"><label class="form-label" for="poll-status">Status</label><select class="form-select" id="poll-status" name="status" required><option value="draft">Save as draft</option><option value="published">Publish</option><option value="closed">Closed</option></select></div>
                                        <div class="col-12"><label class="form-label" for="correct-option">Correct answer</label><select class="form-select" id="correct-option" name="correct_option_index"><option value="">No correct answer yet</option></select><small class="text-muted">Usually selected after a quiz or prediction ends.</small></div>
                                    </div>
                                    <div class="poll-setting-switch mt-3"><div><strong>Visitor comments</strong><small class="d-block text-muted">Allow visitors to leave comments.</small></div><div class="form-check form-switch mb-0"><input type="hidden" name="allow_comments" value="0"><input class="form-check-input" id="allow-poll-comments" name="allow_comments" type="checkbox" value="1" checked></div></div>
                                </section>
                            </div>
                            <div class="col-12">
                                <section class="poll-editor-card">
                                    <div class="poll-section-heading poll-options-heading"><div class="d-flex align-items-center"><span class="poll-section-number">03</span><div><h6 class="mb-1">Answer options</h6><small class="text-muted">Add between 2 and 20 unique choices.</small></div></div><button class="btn btn-sm btn-outline-primary" id="poll-add-option" type="button"><i class="fe fe-plus me-1"></i>Add option</button></div>
                                    <div class="row g-3" id="poll-options-container"></div>
                                </section>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer poll-modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary px-4" id="poll-form-submit" type="submit"><i class="fe fe-save me-1"></i>Save Poll</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- Page-only styles keep the poll module isolated from other admin pages. --}}
    <style>
        .poll-summary-card { border: 0; box-shadow: 0 4px 18px rgba(25,42,70,.06); }
        .poll-summary-icon,.poll-thumb { display:grid; place-items:center; flex:0 0 auto; }
        .poll-summary-icon { width:52px; height:52px; border-radius:14px; font-size:22px; }
        .poll-thumb { width:44px; height:44px; border-radius:12px; font-size:19px; }
        .poll-thumb-image { object-fit:cover; }
        .poll-table th { white-space:nowrap; font-size:12px; letter-spacing:.04em; text-transform:uppercase; }
        .poll-table td { padding-top:16px; padding-bottom:16px; }
        .poll-status-dot { display:inline-block; width:7px; height:7px; margin-right:5px; border-radius:50%; }
        .poll-empty-state { padding:55px 20px; text-align:center; }
        .poll-empty-state > i { display:block; margin-bottom:12px; color:var(--primary-bg-color,#6259ca); font-size:42px; }
        .poll-modal-dialog { width:min(1120px,calc(100vw - 32px)); height:calc(100vh - 48px)!important; max-width:none!important; max-height:none!important; margin:24px auto; }
        .poll-modal-content { display:flex!important; flex-direction:column!important; overflow:hidden!important; width:100%; height:100%!important; max-height:none!important; border:0; border-radius:16px; box-shadow:0 24px 70px rgba(11,18,38,.28); }
        /* The form is the direct content child, so it must carry the scroll flex layout. */
        .poll-modal-content > form { display:flex!important; flex-direction:column!important; width:100%; height:100%; min-height:0; }
        .poll-modal-header { flex:0 0 auto; padding:20px 24px; border-bottom:1px solid rgba(123,129,145,.18); }
        .poll-modal-heading-icon { display:grid; place-items:center; width:46px; height:46px; border-radius:13px; color:#fff; font-size:20px; background:linear-gradient(135deg,#6259ca,#7c74e8); }
        .poll-modal-body { flex:1 1 0!important; overflow-x:hidden!important; overflow-y:scroll!important; min-height:0!important; max-height:none!important; padding:24px; overscroll-behavior:contain; scrollbar-gutter:stable; }
        .poll-modal-footer { flex:0 0 auto; padding:16px 24px; border-top:1px solid rgba(123,129,145,.18); }
        /* Keep a visible scrollbar so long option lists are obviously scrollable. */
        .poll-modal-body::-webkit-scrollbar { width:8px; }
        .poll-modal-body::-webkit-scrollbar-track { background:rgba(123,129,145,.08); }
        .poll-modal-body::-webkit-scrollbar-thumb { border-radius:8px; background:rgba(98,89,202,.65); }
        .poll-modal-body { scrollbar-color:rgba(98,89,202,.65) rgba(123,129,145,.08); scrollbar-width:thin; }
        .poll-editor-card { padding:22px; border:1px solid rgba(123,129,145,.2); border-radius:14px; background:rgba(98,89,202,.035); }
        .poll-section-heading { display:flex; align-items:center; gap:11px; margin-bottom:22px; }
        .poll-section-number { display:inline-grid; place-items:center; flex:0 0 34px; width:34px; height:34px; border-radius:10px; color:#fff; font-size:12px; font-weight:700; background:var(--primary-bg-color,#6259ca); }
        .poll-options-heading { justify-content:space-between; }
        .poll-upload-box { display:flex; align-items:center; gap:13px; min-height:76px; padding:14px 16px; border:1px dashed rgba(98,89,202,.45); border-radius:11px; }
        .poll-upload-box > i { color:var(--primary-bg-color,#6259ca); font-size:24px; }
        .poll-upload-box > div { display:flex; flex:1; flex-direction:column; }
        .poll-image-preview { width:52px; height:52px; border-radius:9px; object-fit:cover; }
        .poll-setting-switch { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px; border:1px solid rgba(123,129,145,.18); border-radius:10px; }
        .poll-option-field { display:flex; align-items:center; overflow:hidden; min-height:48px; border:1px solid rgba(123,129,145,.25); border-radius:10px; background:#fff; }
        .poll-option-field > span { display:grid; align-self:stretch; place-items:center; width:48px; color:var(--primary-bg-color,#6259ca); font-weight:700; background:rgba(98,89,202,.1); }
        .poll-option-field input[type=text] { flex:1; min-width:0; padding:11px 14px; color:#1f2937; border:0; outline:0; background:transparent; }
        .poll-option-field button { align-self:stretch; width:48px; color:#dc3545; border:0; border-left:1px solid rgba(123,129,145,.18); background:transparent; }
        .poll-option-field button:hover:not(:disabled) { color:#fff; background:#dc3545; }
        .poll-option-field button:disabled { cursor:not-allowed; opacity:.35; }
        @media(max-width:767.98px){.poll-table{min-width:980px}.page-rightheader{margin-top:12px}.poll-modal-dialog{width:calc(100vw - 20px);height:calc(100vh - 20px)!important;margin:10px auto}.poll-modal-header,.poll-modal-body,.poll-modal-footer{padding-left:16px;padding-right:16px}.poll-options-heading{align-items:flex-start;flex-direction:column}.poll-modal-footer .btn{flex:1}}
    </style>
@endsection
