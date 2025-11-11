@extends('layouts.app')
@section('title', __('app.edit_permission'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'user.users',
            'app.permissions',
            'app.edit_permission',
        ]" />
            <div class="row">
                <!-- 🔹 Changed from col-12 col-lg-6 to col-12 for full width -->
                <div class="col-12">
                    <!-- 🔹 Added w-100 so the card fills the page width -->
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.general') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="permissionForm"
                                action="{{ route('permission.update') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')
                                <input type="hidden" name='id' value="{{ $permission->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="group_id" name="{{ __('app.group_name') }}" />
                                    <x-dropdown-permission-group selected="{{ $permission->permission_group_id }}" />
                                    <div class="valid-feedback"></div>
                                </div>

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.permission_name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $permission->name }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="display_name" name="{{ __('app.display_name') }}" />
                                    <x-input type="text" name="display_name" :required="true"
                                        value="{{ $permission->display_name }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="{{ $permission->status }}" dropdownName='status' />
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/user/permission/permission.js') }}"></script>
@endsection