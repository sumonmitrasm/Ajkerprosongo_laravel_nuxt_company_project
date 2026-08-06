@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddCategories = $currentAdmin?->hasModuleAccess('category', 'add');
    $canEditCategories = $currentAdmin?->hasModuleAccess('category', 'edit');
    $canDeleteCategories = $currentAdmin?->hasModuleAccess('category', 'delete');
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
                            <div class="card-title">{{ $title }}</div>@if ($canAddCategories)<button type="button" class="btn btn-info"
                                data-crud-create data-crud-modal="#category-form-modal"
                                data-store-url="{{ route('admin-category.store') }}"
                                data-create-title="Add Category">Add
                                Category</button>@endif
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int) request('per_page',10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span><button class="btn btn-sm btn-success" data-table-export="#category-table" data-table-export-type="excel">Excel</button><button class="btn btn-sm btn-primary" data-table-export="#category-table" data-table-export-type="word">Word</button></div>
                            <div class="table-responsive">
                                <table id="category-table" data-server-pagination
                                    class="table table-bordered text-nowrap key-buttons">
                                    <thead>
                                        <tr>
                                            <th class="border-bottom-0">Category ID</th>
                                            <th class="border-bottom-0">Parent Category</th>
                                            <th class="border-bottom-0">Section Name</th>
                                            <th class="border-bottom-0">Category Name</th>
                                            <th class="border-bottom-0">Status</th>
                                            <th class="border-bottom-0">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($categories as $category)
                                            <tr>
                                                <td>{{ $category['id'] ?? '-' }}</td>
                                                <td>{{ $category['parent_category_name'] ?? 'Root / Main Category' }}</td>
                                                <td>{{ $category['section_name'] ?? '' }}</td>
                                                <td>{{ $category['category_name'] ?? '-' }}</td>
                                                <td>@if ($canEditCategories)<button type="button"
                                                        class="btn btn-sm {{ ($category['status'] ?? false) ? 'btn-success' : 'btn-secondary' }}"
                                                        data-crud-status
                                                        data-url="{{ route('admin-category.status', $category['id']) }}">{{ ($category['status'] ?? false) ? 'Active' : 'Inactive' }}</button>@else {{ ($category['status'] ?? false) ? 'Active' : 'Inactive' }} @endif
                                                </td>
                                                <td>@if ($canEditCategories)<button type="button" class="btn btn-sm btn-primary" data-crud-edit
                                                        data-crud-modal="#category-form-modal"
                                                        data-url="{{ route('admin-category.show', $category['id']) }}"
                                                        data-update-url="{{ route('admin-category.update', $category['id']) }}">Edit</button>@endif
                                                    @if ($canDeleteCategories)
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-crud-delete
                                                        data-url="{{ route('admin-category.delete', $category['id']) }}">Delete</button>@endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $categories->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="category-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form data-crud-form enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" data-crud-title>Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none js-crud-errors"></div>

                    <!-- Part 1: Basic Category Details -->
                    <h6 class="fw-bold mb-3 text-primary">Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="category_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Section ID</label>
                            <select id="section_id" name="section_id" class="form-control">
                                <option value="">Select</option>
                                @foreach ($getSection as $section)
                                    <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputName">Select category label</label>
                            <select id="parent_id" name="parent_id" class="form-control">
                                <option value="0">Main Category</option>
                                @if (!empty($getCategories))
                                    @foreach ($getCategories as $parentcategory)
                                        <option value="{{ $parentcategory['id'] }}">{{ $parentcategory['category_name'] }}
                                        </option>
                                        @if (!empty($parentcategory['subcategories']))
                                            @foreach ($parentcategory['subcategories'] as $subcategory)
                                                <option value="{{ $subcategory['id'] }}">
                                                    &nbsp;&raquo;&nbsp;{{ $subcategory['category_name'] }}</option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position / Order</label>
                            <input type="number" name="position" class="form-control" value="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">URL Slug</label>
                            <input type="text" name="url" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">URL Structure</label>
                            <input type="text" name="url_structure" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
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
                            <label class="form-label">Heading Tag (H1, H2)</label>
                            <input type="text" name="heading_tag" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control">
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

                        <div class="col-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Meta Data</label>
                            <textarea name="meta_data" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Schema Markup</label>
                            <textarea name="schema_markup" class="form-control" rows="2" placeholder='Enter Schema'></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-crud-submit>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
