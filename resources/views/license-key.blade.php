@extends('layouts.guest')
@section('title', __('License Activation'))

@section('container')
    <!--wrapper-->
    <div class="wrapper">
        <div class="section-authentication-cover">
            <div class="">
                <div class="row g-0">

                    <div class="col-12 col-xl-12 auth-cover-right align-items-center justify-content-center">
                        <div class="card rounded-0 m-3 shadow-none bg-transparent mb-0">
                            <div class="card-body p-sm-5">

                                @include('layouts.session')

                                <div class="">
                                    <div class="text-center">
                                        @php
                                            $site = app('site') ?? [];
                                            $logo = $site['colored_logo'] ?? 'default-logo.png';
                                        @endphp
                                        <img src="{{ url('/app/getimage/' . $logo) }}" width="165px" alt="" class="mb-2">
                                    </div>

                                    <div class="text-center mb-4">
                                        <h5 class="">{{ $site['name'] ?? config('app.name') }}</h5>
                                        <p class="mb-0">{{ __('Enter your license key to activate') }}</p>
                                    </div>

                                    <div class="form-body">
                                        <form class="row g-3" id="licenseForm" method="POST" action="{{ route('license.verify') }}" autocomplete="off">
                                            @csrf

                                            {{-- Hidden dummy fields to fully prevent Chrome autofill --}}
                                            <input type="text" name="fakeusernameremembered" id="fakeusernameremembered" autocomplete="off" style="display:none">
                                            <input type="password" name="fakepasswordremembered" id="fakepasswordremembered" autocomplete="off" style="display:none">

                                            <div class="col-12">
                                                <x-label for="license_key" name="{{ __('License Key') }}" />
                                                <x-input 
                                                    placeholder="Enter license key" 
                                                    id="license_key" 
                                                    name="license_key" 
                                                    type='text' 
                                                    :required="true" 
                                                    :autofocus="true" 
                                                    autocomplete="off" 
                                                    inputmode="off" 
                                                />
                                            </div>

                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <x-button type="submit" class="primary" text="{{ __('Activate') }}" />
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="text-center">
                                                    <small class="text-muted">
                                                        {{ __('app.contact_support') }}
                                                        <strong>{{ $phone_number }}</strong>
                                                    </small>
                                                </div>
                                            </div>

                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!--end row-->
            </div>
        </div>
    </div>
    <!--end wrapper-->
@endsection

@section('js')
    <script src="{{ asset('custom/js/license-key.js') }}"></script>
@endsection
