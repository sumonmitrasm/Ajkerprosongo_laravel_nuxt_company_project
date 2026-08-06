@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddSetting = $currentAdmin?->hasModuleAccess('setting', 'add');
    $canEditSetting = $currentAdmin?->hasModuleAccess('setting', 'edit');
    $canDeleteSetting = $currentAdmin?->hasModuleAccess('setting', 'delete');
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
                            <div class="card-title">{{ $title }}</div>@if ($canAddSetting)<button type="button" class="btn btn-info"
                                data-crud-create data-crud-modal="#setting-form-modal"
                                data-store-url="{{ route('admin-setting.store') }}"
                                data-create-title="Add Setting">Add
                                Setting</button>@endif
                        </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int) request('per_page',10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span><button class="btn btn-sm btn-success" data-table-export="#settings-table" data-table-export-type="excel">Excel</button><button class="btn btn-sm btn-primary" data-table-export="#settings-table" data-table-export-type="word">Word</button></div>
                    <div class="table-responsive">
                        <table id="settings-table" data-server-pagination class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Site Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($getSettings as $setting)
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

                    <div class="mt-3">{{ $getSettings->links() }}</div>

                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="setting-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form data-crud-form enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" data-crud-title>Add Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none js-crud-errors"></div>

                    <!-- Part 1: Basic setting Details -->
                    <h6 class="fw-bold mb-3 text-primary">Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site Logo</label>
                            <input type="file" name="image" class="form-control" accept="image/*" data-image-input>
                            <img data-image-preview-for="image" class="d-none mt-2 rounded border" alt="Selected site logo" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Site Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept="image/*,.ico" data-image-input>
                            <img data-image-preview-for="favicon" class="d-none mt-2 rounded border" alt="Selected favicon" style="width: 64px; height: 64px; object-fit: contain;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perronal Phone</label>
                            <input type="text" name="perronal_phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Phone</label>
                            <input type="number" name="phone" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Side Name</label>
                            <input type="text" name="side_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Developed Year</label>
                            <input type="text" name="developed_year" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Facebook Url</label>
                            <input type="text" name="facebook_url" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Twitter Url</label>
                            <input type="text" name="twitter_url" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Linkedin Url</label>
                            <input type="url" name="linkedin_url" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Instagram Url</label>
                            <input type="text" name="instagram_url" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Youtube Url</label>
                            <input type="text" name="youtube_url" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <hr class="my-4">
                    <!-- Part 2: SEO & Meta Details -->
                    <h6 class="fw-bold mb-3 text-primary">SEO & Meta Information</h6>
                    <div class="row g-3">
                         <div class="col-md-6">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Canonical Tag</label>
                            <input type="text" name="canonical_tag" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">URL Structure</label>
                            <input type="text" name="url_structure" class="form-control">
                        </div>
                          <div class="col-md-6">
                            <label class="form-label">Heading Tag (H1, H2)</label>
                            <input type="text" name="heading_tag" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schema Markup</label>
                            <textarea name="schema_markup" class="form-control" rows="2" placeholder='Enter Schema'></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Data</label>
                            <textarea name="meta_data" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Robot</label>
                            <select name="meta_robot" class="form-select">
                                <option value="index, follow">index, follow</option>
                                <option value="noindex, follow">noindex, follow</option>
                                <option value="index, nofollow">index, nofollow</option>
                                <option value="noindex, nofollow">noindex, nofollow</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-crud-submit>Save Setting</button>
                </div>
            </form>
        </div>
    </div>
</div>
