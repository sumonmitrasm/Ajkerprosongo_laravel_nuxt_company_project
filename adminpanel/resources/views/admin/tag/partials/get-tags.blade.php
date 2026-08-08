@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddTag = $currentAdmin?->hasModuleAccess('tag', 'add');
    $canEditTag = $currentAdmin?->hasModuleAccess('tag', 'edit');
    $canDeleteTag = $currentAdmin?->hasModuleAccess('tag', 'delete');
@endphp
<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title">{{ $title }}</h4>
                </div>
            </div>
            <div class="card">
                <div class="card-header justify-content-between">
                            <div class="card-title">{{ $title }}</div>@if ($canAddTag)<button type="button" class="btn btn-info"
                                data-crud-create data-crud-modal="#tag-form-modal"
                                data-store-url="{{ route('admin-tag.store') }}"
                                data-create-title="Add Tag">Add
                                Tag</button>@endif
                        </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int) request('per_page',10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span><button class="btn btn-sm btn-success" data-table-export="#settings-table" data-table-export-type="excel">Excel</button><button class="btn btn-sm btn-primary" data-table-export="#settings-table" data-table-export-type="word">Word</button></div>
                    <div class="table-responsive">
                        <table id="tags-table" data-server-pagination class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($getTags as $setting)
                                    <tr>
                                        <td>{{ $setting['id'] ?? '-' }}</td>
                                        <td>{{ $setting['side_name'] ?? '-' }}</td>
                                        <td>{{ $setting['email'] ?? '-' }}</td>
                                        <td>{{ $setting['phone'] ?? $setting['perronal_phone'] ?? '-' }}</td>
                                        <td>@if ($canEditSetting)<button type="button"
                                                        class="btn btn-sm {{ ($setting['status'] ?? false) ? 'btn-success' : 'btn-secondary' }}"
                                                        data-crud-status
                                                        data-url="{{ route('admin-setting.status', $setting['id']) }}">{{ ($setting['status'] ?? false) ? 'Active' : 'Inactive' }}</button>@else {{ ($setting['status'] ?? false) ? 'Active' : 'Inactive' }} @endif
                                                </td>
                                                <td>@if ($canEditSetting)<button type="button" class="btn btn-sm btn-primary" data-crud-edit
                                                        data-crud-modal="#setting-form-modal"
                                                        data-url="{{ route('admin-setting.show', $setting['id']) }}"
                                                        data-update-url="{{ route('admin-setting.update', $setting['id']) }}">Edit</button>@endif
                                                    @if ($canDeleteSetting)
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-crud-delete
                                                        data-url="{{ route('admin-setting.delete', $setting['id']) }}">Delete</button>@endif
                                                </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No settings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $getTags->links() }}</div>

                </div>
            </div>
        </div>
    </div>
</div>
