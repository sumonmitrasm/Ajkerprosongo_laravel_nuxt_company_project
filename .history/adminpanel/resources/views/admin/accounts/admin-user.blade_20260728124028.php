@extends('admin.layout.layout')
@section('content')
<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">

            <!--Page header-->
            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title">All default {{ $title }}</h4>
                </div>
            </div>
            <!--End Page header-->

            <!-- Row -->
            <div class="row">
                <div class="col-12">
                    <!--div-->
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">{{ $title }}</div>

                            <div><a href="{{url('admin/add-edit-setting')}}"  class="btn btn-block btn-info">Add Setting</a></div>

                        </div>
                        <div class="card-body">
                            @if(Session::has('success_message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Success:</strong> {{Session::get('success_message')}}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                            <div class="">
                                <div class="table-responsive">
                                    <table id="example" class="table table-bordered text-nowrap key-buttons">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Meta Title</th>
                                                <th class="border-bottom-0">Logo</th>
                                                <th class="border-bottom-0">Status</th>
                                                <th class="border-bottom-0">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- @foreach($settings as $setting)
                                            <tr>
                                                <td>{{ $setting['id'] }}</td>
                                                <td>{{ $setting['meta_title'] }}</td>
                                                <td><img style="width: 200px; height: auto; margin-top: 5px;" src="{{asset('admin/site_settings/'.$setting['logo'])}}" alt=""></td>
                                                <td>
                                                    @if($sectionModule['edit_access']==1 || $sectionModule['full_access']==1)
                                                    @if($setting['status']==1)
                                                    <a class="updatesettingStatus" id="setting-{{$setting['id']}}" setting_id="{{$setting['id']}}" href="javascript:void(0)"><i style="font-size:25px;" class="mdi mdi-bookmark-check" status="Active"></i></a>
                                                    @else
                                                    <a class="updatesettingStatus" id="setting-{{$setting['id']}}" setting_id="{{$setting['id']}}" href="javascript:void(0)"><i style="font-size:25px;" class="mdi mdi-bookmark-outline" status="Inactive"></i></a>
                                                    @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sectionModule['edit_access']==1 || $sectionModule['full_access']==1)
                                                    <a href="{{url('admin/add-edit-setting/'.$setting['id'])}}"><i style="font-size:25px;" class="mdi mdi-pencil-box"></i></a>
                                                    @endif

                                                    <!-- <a title="Setting" class="confirmDelete" href="{{url('admin/delete-setting/'.$setting['id'])}}"><i style="font-size:25px;" class="mdi mdi-file-excel-box"></i></a> -->

                                                </td>
                                            </tr>
                                            @endforeach --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/div-->
                </div>
            </div>
            <!-- /Row -->

        </div>
    </div>
</div>
<!-- Include Bootstrap CSS and JavaScript -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection
