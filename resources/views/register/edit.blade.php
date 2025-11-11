@extends('layouts.app')
@section('title', __('register.update_register'))

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['register.registers', 'register.list', 'register.update_register']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('register.details') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="registerForm" action="{{ route('register.update') }}"
                                enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')
                                <input type="hidden" name='id' value="{{ $register->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-6">
                                    <x-label for="name" name="{{ __('register.name') }}" />
                                    <x-input type="text" name="name" id="name" value="{{ $register->name }}" />
                                </div>

                                <div class="col-md-6">
                                    <x-label for="code" name="{{ __('register.code') }}" />
                                    <x-input type="text" name="code" id="code" value="{{ $register->code }}"
                                        readonly />
                                </div>

                                <div class="col-md-6">
                                    <x-label for="user_name" name="{{ __('register.user') }}" />
                                    <input type="text" class="form-control" id="user_name"
                                        value="{{ $register->user ? $register->user->username : '-' }}" readonly>
                                </div>

                                <div class="col-md-6">
                                    <x-label for="phone_number" name="{{ __('register.phone_number') }}" />
                                    <x-input type="text" name="phone_number" id="phone_number"
                                        value="{{ $register->phone_number }}" />
                                </div>

                                <div class="col-md-6">
                                    <x-label for="note" name="{{ __('register.note') }}" />
                                    <x-textarea name="note" id="note" value="{{ $register->note }}" />
                                </div>

                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="active" id="active"
                                            value="1" {{ $register->active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">{{ __('register.active') }}</label>
                                    </div>
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
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/register/register.js') }}"></script>
@endsection
