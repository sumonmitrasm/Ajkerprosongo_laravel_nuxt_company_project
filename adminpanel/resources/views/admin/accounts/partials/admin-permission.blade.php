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
                            <h3 class="card-title">Manage Permissions: {{ $user->name }}</h3>
                            <a href="{{ route('admin-user') }}" class="btn btn-secondary">Back to Users</a>
                        </div>
                        <div class="card-body">
                            <form id="permission-form" method="POST"
                                data-url="{{ route('admin-user.permission.update', $user->id) }}">
                                @csrf

                                @foreach ($modules as $module)
                                    @php
                                        $permission = $userPermissions[$module] ?? [];
                                        $hasFullAccess = !empty($permission['view_access']) && !empty($permission['add_access']) && !empty($permission['edit_access']) && !empty($permission['delete_access']);
                                    @endphp
                                    <div class="mb-4" data-permission-module>
                                        <h5 class="text-primary border-bottom pb-2">{{ ucfirst($module) }} Management</h5>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="permissions[{{ $module }}][view_access]"
                                                        value="1"
                                                        data-access-checkbox {{ !empty($permission['view_access']) ? 'checked' : '' }}>
                                                    <span class="custom-control-label">View Access</span>
                                                </label>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="permissions[{{ $module }}][add_access]"
                                                        value="1"
                                                        data-access-checkbox {{ !empty($permission['add_access']) ? 'checked' : '' }}>
                                                    <span class="custom-control-label">Add Access</span>
                                                </label>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="permissions[{{ $module }}][edit_access]"
                                                        value="1"
                                                        data-access-checkbox {{ !empty($permission['edit_access']) ? 'checked' : '' }}>
                                                    <span class="custom-control-label">Edit Access</span>
                                                </label>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="permissions[{{ $module }}][delete_access]"
                                                        value="1"
                                                        data-access-checkbox {{ !empty($permission['delete_access']) ? 'checked' : '' }}>
                                                    <span class="custom-control-label">Delete Access</span>
                                                </label>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="permissions[{{ $module }}][full_access]"
                                                        value="1"
                                                        data-full-access {{ $hasFullAccess ? 'checked' : '' }}>
                                                    <span class="custom-control-label">Full Access</span>
                                                </label>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="permissions[{{ $module }}][no_access]"
                                                        value="1"
                                                        data-no-access {{ !empty($permission['no_access']) ? 'checked' : '' }}>
                                                    <span class="custom-control-label">Module No Access</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary" id="btn-save-permissions">Save
                                        Permissions</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
