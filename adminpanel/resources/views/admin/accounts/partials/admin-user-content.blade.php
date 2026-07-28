<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
    <div class="page-header"><div class="page-leftheader"><h4 class="page-title">{{ $title }}</h4></div></div>
    <div class="row"><div class="col-12"><div class="card">
        <div class="card-header justify-content-between"><div class="card-title">{{ $title }}</div><button type="button" class="btn btn-info js-user-create">Add User</button></div>
        <div class="card-body"><div id="user-action-message"></div><div class="table-responsive">
            <table id="users-table" class="table table-bordered text-nowrap key-buttons">
                <thead><tr><th>ID</th><th>AP ID</th><th>Name</th><th>Email</th><th>Type</th><th>Mobile</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>@foreach($users as $user)<tr>
                    <td>{{ $user->id }}</td><td>{{ $user->ap_id }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->type }}</td><td>{{ $user->mobile }}</td>
                    <td><button type="button" class="btn btn-sm {{ $user->status ? 'btn-success' : 'btn-secondary' }} js-user-status" data-url="{{ route('admin-user.status', $user) }}">{{ $user->status ? 'Active' : 'Inactive' }}</button></td>
                    <td><button type="button" class="btn btn-sm btn-primary js-user-edit" data-url="{{ route('admin-user.show', $user) }}" data-update-url="{{ route('admin-user.update', $user) }}">Edit</button> <button type="button" class="btn btn-sm btn-danger js-user-delete" data-url="{{ route('admin-user.delete', $user) }}">Delete</button></td>
                </tr>@endforeach</tbody>
            </table>
        </div></div>
    </div></div></div>
</div></div></div>

<div class="modal fade" id="user-form-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form id="user-form">
    @csrf
    <div class="modal-header"><h5 class="modal-title" id="user-form-title">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>
        <div class="mb-3"><label class="form-label">AP ID</label><input type="number" name="ap_id" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Mobile</label><input type="text" name="mobile" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Password <small id="password-help">(minimum 6 characters)</small></label><input type="password" name="password" class="form-control"></div>
        <div><label class="form-label">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" id="user-form-submit">Save User</button></div>
</form></div></div></div>
