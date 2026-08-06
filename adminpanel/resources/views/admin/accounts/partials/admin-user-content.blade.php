@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddUsers = $currentAdmin?->hasModuleAccess('admin', 'add');
    $canEditUsers = $currentAdmin?->hasModuleAccess('admin', 'edit');
    $canDeleteUsers = $currentAdmin?->hasModuleAccess('admin', 'delete');
    $canSetPermissions = $currentAdmin?->hasModuleAccess('admin', 'full');
@endphp
<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title">{{ $title }}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">{{ $title }}</div>@if ($canAddUsers)<button type="button" class="btn btn-info"
                                data-crud-create data-crud-modal="#user-form-modal"
                                data-store-url="{{ route('admin-user.store') }}" data-create-title="Add User">Add
                                User</button>@endif
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int) request('per_page',10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span><button class="btn btn-sm btn-success" data-table-export="#users-table" data-table-export-type="excel">Excel</button><button class="btn btn-sm btn-primary" data-table-export="#users-table" data-table-export-type="word">Word</button></div>
                            <div class="table-responsive">
                                <table id="users-table" data-server-pagination
                                    class="table table-bordered text-nowrap key-buttons">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>AP ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Type</th>
                                            <th>Mobile</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $user['id'] ?? '-' }}</td>
                                                <td>
                                                    @if ($user['image'] ?? false)
                                                        <img src="{{ asset('admin/adminimage/' . $user['image']) }}"
                                                            alt="Avatar" class="rounded-circle" width="40"
                                                            height="40">
                                                    @else
                                                        <img src="{{ asset('admin/site_settings/no-image.png') }}"
                                                            alt="Avatar" class="rounded-circle" width="40"
                                                            height="40">
                                                    @endif
                                                </td>
                                                <td>{{ $user['ap_id'] ?? '-' }}</td>
                                                <td>{{ $user['name'] ?? '-' }}</td>
                                                <td>{{ $user['email'] ?? '-' }}</td>
                                                <td>{{ $user['type'] ?? '-' }}</td>
                                                <td>{{ $user['mobile'] ?? '-' }}</td>
                                                <td>@if ($canEditUsers)<button type="button"
                                                        class="btn btn-sm {{ ($user['status'] ?? false) ? 'btn-success' : 'btn-secondary' }}"
                                                        data-crud-status
                                                        data-url="{{ route('admin-user.status', $user['id']) }}">{{ ($user['status'] ?? false) ? 'Active' : 'Inactive' }}</button>@else {{ ($user['status'] ?? false) ? 'Active' : 'Inactive' }} @endif
                                                </td>
                                                <td>@if ($canEditUsers)<button type="button" class="btn btn-sm btn-primary" data-crud-edit
                                                        data-crud-modal="#user-form-modal"
                                                        data-url="{{ route('admin-user.show', $user['id']) }}"
                                                        data-update-url="{{ route('admin-user.update', $user['id']) }}">Edit</button>@endif
                                                    @if ($canDeleteUsers)
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-crud-delete
                                                        data-url="{{ route('admin-user.delete', $user['id']) }}">Delete</button>@endif

                                                    @if ($canSetPermissions)
                                                    <a href="{{ route('admin-user.permission', $user['id']) }}" class="btn btn-sm btn-warning" data-ajax-page>
                                                        Permission
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($users instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-3">{{ $users->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="user-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form data-crud-form data-image-base-url="{{ asset('admin/adminimage') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" data-crud-title>Add User</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none js-crud-errors"></div>
                    {{-- <div class="mb-3"><label class="form-label">AP ID</label><input type="number" name="ap_id"
                            class="form-control"></div> --}}
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label" id="user-email">Email</label><input type="email"
                            name="email" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="" disabled selected>Select Type</option>
                            <option value="superadmin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="crospondent">Correspondent</option>
                            <option value="manager">Manager</option>
                            <option value="reporter">Reporter</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" data-image-input>
                        <img data-image-preview class="d-none mt-2 rounded border" alt="Selected profile image"
                            style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <div class="mb-3"><label class="form-label">Mobile</label><input type="text" name="mobile"
                            class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Password <small data-password-help>(minimum 6
                                characters)</small></label><input type="password" name="password" class="form-control">
                    </div>
                    <div><label class="form-label">Status</label><select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"
                        data-crud-submit>Save User</button></div>
            </form>
        </div>
    </div>
</div>
