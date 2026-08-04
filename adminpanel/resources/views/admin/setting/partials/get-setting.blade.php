<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title">{{ $title }}</h4>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">General Settings</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="settings-table" data-server-pagination class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Site Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($getSettings as $setting)
                                    <tr>
                                        <td>{{ $setting['id'] ?? '-' }}</td>
                                        <td>{{ $setting['side_name'] ?? '-' }}</td>
                                        <td>{{ $setting['email'] ?? '-' }}</td>
                                        <td>{{ $setting['phone'] ?? $setting['perronal_phone'] ?? '-' }}</td>
                                        <td>{{ ($setting['status'] ?? false) ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No settings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10, 20, 50, 100] as $size)<option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span></div>
                    <div class="mt-3">{{ $getSettings->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
