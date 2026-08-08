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

            <div class="row">
                <div class="col-12">
                    <div class="card">
                <div class="card-header justify-content-between">
                    <div class="card-title">{{ $title }}</div>
                    @if ($canAddTag)
                        <button type="button" class="btn btn-info" data-crud-create data-crud-modal="#tag-form-modal"
                            data-store-url="{{ route('admin-tag.store') }}" data-create-title="Add Tag">Add Tag</button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <label class="mb-0 text-muted">Show</label>
                        <select class="form-select form-select-sm w-auto" data-server-per-page>
                            @foreach ([10, 20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="text-muted">entries</span>
                        <button class="btn btn-sm btn-success" data-table-export="#tags-table" data-table-export-type="excel">Excel</button>
                        <button class="btn btn-sm btn-primary" data-table-export="#tags-table" data-table-export-type="word">Word</button>
                    </div>

                    <div class="table-responsive">
                        <table id="tags-table" data-server-pagination class="table table-bordered text-nowrap key-buttons">
                            <thead>
                                <tr>
                                    <th style="width: 80px">#</th>
                                    <th>Tag</th>
                                    <th>Page Title</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($getTags as $tag)
                                    <tr>
                                        <td class="text-muted">{{ $tag->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($tag->image)
                                                    <img src="{{ asset('admin/tagimage/' . $tag->image) }}" alt="{{ $tag->name }}" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                                                @else
                                                    <span class="avatar avatar-sm bg-primary-transparent text-primary"><i class="fe fe-tag"></i></span>
                                                @endif
                                                <span class="fw-semibold">{{ $tag->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $tag->title ?: '—' }}</td>
                                        <td><code>{{ $tag->slug }}</code></td>
                                        <td>
                                            @if ($canEditTag)
                                                <button type="button" class="btn btn-sm {{ $tag->status ? 'btn-success' : 'btn-secondary' }}" data-crud-status data-url="{{ route('admin-tag.status', $tag) }}">
                                                    {{ $tag->status ? 'Active' : 'Inactive' }}
                                                </button>
                                            @else
                                                <span class="badge {{ $tag->status ? 'bg-success' : 'bg-secondary' }}">{{ $tag->status ? 'Active' : 'Inactive' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($canEditTag)
                                                <button type="button" class="btn btn-sm btn-primary" data-crud-edit data-crud-modal="#tag-form-modal"
                                                    data-url="{{ route('admin-tag.show', $tag) }}" data-update-url="{{ route('admin-tag.update', $tag) }}">Edit</button>
                                            @endif
                                            @if ($canDeleteTag)
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-crud-delete data-url="{{ route('admin-tag.delete', $tag) }}">Delete</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-5">No tags found.</td></tr>
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
    </div>
</div>

<div class="modal fade" id="tag-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg my-3" style="height: calc(100vh - 1.5rem)">
        <div class="modal-content h-100">
            <form data-crud-form enctype="multipart/form-data" class="d-flex flex-column h-100" style="min-height: 0">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" data-crud-title>Add Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; min-height: 0">
                    <div class="alert alert-danger d-none js-crud-errors"></div>
                    <h6 class="fw-semibold text-primary border-bottom pb-2 mb-3">Basic information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tag Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Web Development" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Page Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Optional display title">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tag Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" data-image-input>
                            <img data-image-preview-for="image" class="d-none mt-2 rounded border" alt="Selected tag image" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                    </div>

                    <h6 class="fw-semibold text-primary border-bottom pb-2 mt-4 mb-3">SEO & meta information</h6>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="text" name="canonical_tag" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6"><label class="form-label">URL Structure</label><input type="text" name="url_structure" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Heading Tag</label><input type="text" name="heading_tag" class="form-control" placeholder="H1, H2"></div>
                        <div class="col-md-6"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Meta Robot</label><select name="meta_robot" class="form-select"><option value="index, follow">index, follow</option><option value="noindex, follow">noindex, follow</option><option value="index, nofollow">index, nofollow</option><option value="noindex, nofollow">noindex, nofollow</option></select></div>
                        <div class="col-md-6"><label class="form-label">Schema Markup</label><textarea name="schema_markup" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Meta Data</label><textarea name="meta_data" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" data-crud-submit>Save Tag</button></div>
            </form>
        </div>
    </div>
</div>
