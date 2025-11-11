@extends('layouts.guest')
@section('title', __('auth.login'))

@section('container')

    <!--wrapper-->
    <div class="wrapper">
        <div class="section-authentication-cover">
            <div class="">
                <div class="row g-0">

                    <div
                        class="col-12 col-xl-7 col-xxl-8 auth-cover-left align-items-center justify-content-center d-none d-xl-flex">

                        <div class="shadow-none bg-transparent shadow-none rounded-0 mb-0">
                            <div class="card-body">
                                <img src="assets/images/login-images/image-visual-13.png" class="img-fluid " width="650"
                                    alt="" />
                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-xl-5 col-xxl-4 auth-cover-right align-items-center justify-content-center">

                        <div class="card rounded-0 m-3 shadow-none bg-transparent mb-0">
                            @if (config('demo.enabled'))
                                <div class="position-absolute top-0 end-0 mt-3 me-3">
                                    <div class="d-grid">
                                        <a href="https://codecanyon.net/item/delta/51635135" target="_blank"
                                            class="btn btn-success btn-sm px-4">Buy Now</a>
                                    </div>
                                </div>
                            @endif
                            <div class="card-body p-sm-5">

                                @include('layouts.session')

                                <div class="">
                                    <div class="text-center">
                                        <img src={{ url('/app/getimage/' . app('site')['colored_logo']) }} width="165px"
                                            alt="">
                                    </div>
                                    <div class="text-center mb-4">
                                        <h5 class="">{{ app('site')['name'] }}</h5>
                                        <p class="mb-0">{{ __('auth.login_to_account') }}</p>
                                    </div>
                                    <div class="form-body">
                                        <form class="row g-3" id="loginForm" action="{{ route('login') }}"
                                            enctype="multipart/form-data">
                                            {{-- CSRF Protection --}}
                                            @csrf
                                            @method('POST')

                                            <div class="col-12">
                                                <x-label for="username" name="{{ __('app.user_name') }}" />
                                                <x-input placeholder="Enter Username" id="username" name="username"
                                                    type='text' :required="true" :autofocus="true" :autocomplete='true' />
                                            </div>

                                            <div class="col-12">
                                                <x-label for="password" name="{{ __('app.password') }}" />
                                                <div class="input-group" id="show_hide_password">
                                                    <x-input placeholder="Enter Password" id="password" name="password"
                                                        type='password' :required="true" />
                                                    <a href="javascript:;" class="input-group-text bg-transparent"><i
                                                            class="bx bx-hide"></i></a>

                                                </div>
                                            </div>
                                            <!---<div class="col-md-6">
                <x-radio-block id="remember" boxName="remember" text="{{ __('auth.remember_me') }}" parentDivClass='form-switch'/>
               </div>
               <div class="col-md-6 text-end">
                <x-anchor-tag href="{{ route('password.request') }}" text="{{ __('auth.forgot_password') }}" />
               </div>---->
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <x-button type="submit" class="primary"
                                                        text="{{ __('app.sign_in') }}" />
                                                </div>
                                            </div>
                                            @if (false)
                                                <div class="col-12">
                                                    <div class="text-center ">
                                                        <p class="mb-0">{{ __('auth.dont_have_account') }}
                                                            <x-anchor-tag href="{{ route('register') }}"
                                                                text="Sign up here" />
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            <!---<div class="col-12">
                <div class="text-center ">
                 <x-flag-toggle justLinks='true'/>
                </div>
               </div>---->

                                            @php
                                                $appVersion = getAppVersion();
                                                $dbVersion = getDatabaseMigrationAppVersion();
                                            @endphp

                                            <div class="text-center">
                                                <span>Version: {{ $appVersion }}</span>
                                            </div>


                                            @if ($appVersion != $dbVersion)
                                                <div
                                                    class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                                    <div class="text-white">
                                                        Version Mismatch!!<br>
                                                        <small>
                                                            App Version: {{ $appVersion }},
                                                            Database Version: {{ $dbVersion }}
                                                        </small>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                </div>
                                            @endif


                                            @include('auth.demo-login')

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
    <!-- Login page -->
    <script src="custom/js/login.js"></script>
    @if (config('demo.enabled'))
        <script src="custom/js/demo-login.js"></script>
    @endif
@endsection
