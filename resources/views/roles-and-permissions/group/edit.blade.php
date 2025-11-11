@extends('layouts.app')
@section('title', __('app.edit_group'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">

            <x-breadcrumb :langArray="[
            'user.users',
            'app.permissions',
            'app.edit_group',
        ]" />

            <div class="row">
                <!-- 🔹 Changed from col-12 col-lg-6 to col-12 for full width -->
                <div class="col-12">
                    <!-- 🔹 Added w-100 so the card takes full width -->
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.general') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="groupForm"
                                action="{{ route('permission.group.update') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')
                                <input type="hidden" name='id' value="{{ $group->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.group_name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $group->name }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="{{ $group->status }}" dropdownName='status' />
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
    <script src="{{ versionedAsset('custom/js/user/permission/group.js') }}"></script>
@endsection
