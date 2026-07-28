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
                            <div class="card-title">{{ $title }}</div><button type="button"
                                class="btn btn-info js-section-create">Add Section</button>
                        </div>
                        <div class="card-body">
                            <div id="section-action-message"></div>
                            <div class="table-responsive">
                                <table id="sections-table" class="table table-bordered text-nowrap key-buttons">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $section)
                                            <tr>
                                                <td>{{ $section->id }}</td>
                                                <td>{{ $section->name }}</td>
                                                <td><button type="button"
                                                        class="btn btn-sm {{ $section->status ? 'btn-success' : 'btn-secondary' }} js-section-status"
                                                        data-url="{{ route('admin-section.status', $section) }}">{{ $section->status ? 'Active' : 'Inactive' }}</button>
                                                </td>
                                                <td><button type="button" class="btn btn-sm btn-primary js-section-edit"
                                                        data-url="{{ route('admin-section.show', $section) }}"
                                                        data-update-url="{{ route('admin-section.update', $section) }}">Edit</button>
                                                    <button type="button" class="btn btn-sm btn-danger js-section-delete"
                                                        data-url="{{ route('admin-section.delete', $section) }}">Delete</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
