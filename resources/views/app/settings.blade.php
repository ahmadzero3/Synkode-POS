@extends('layouts.app')

@section('title', __('app.app_settings'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">

            <x-breadcrumb :langArray="['app.settings', 'app.app_settings']" />

            <div class="row">
                <div class="col-12 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="my-3">{{ __('app.settings') }}</h5>
                            <div class="fm-menu">
                                <div class="list-group list-group-flush">
                                    <a href="javascript:;" class="list-group-item py-1 active text-white show_general"><i
                                            class='bx bx-folder me-2'></i><span>{{ __('app.general') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_logo"><i
                                            class='bx bx-images me-2'></i><span>{{ __('app.app_logo') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_smtp"><i
                                            class='bx bx-envelope me-2'></i><span>{{ __('app.email_settings') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_sms"><i
                                            class='bx bx-message me-2'></i><span>{{ __('app.sms_settings') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_cache"><i
                                            class='bx bx-pulse me-2'></i><span>{{ __('app.cache') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_app_log"><i
                                            class='bx bx-trash me-2'></i><span>{{ __('app.app_log') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_database"><i
                                            class='bx bx-plug me-2'></i><span>{{ __('app.database_backup') }}</span></a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-9">
                    <!--Tab: General -->
                    <div class="card general_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.general') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="generalForm" action="{{ route('general.store') }}"
                                enctype="multipart/form-data" method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                <div class="col-md-12">
                                    <x-label for="application_name" name="{{ __('app.application_name') }}" />
                                    <x-input type="text" name="application_name" :required="true"
                                        value="{{ $data->application_name }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="footer_text" name="{{ __('app.footer_text') }}" />
                                    <x-input type="text" name="footer_text" :required="true"
                                        value="{{ $data->footer_text }}"
                                        placeholder="Copyright © 2022. All right reserved." />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="phone_number" name="Phone Number" />
                                    <x-input type="text" name="phone_number" :required="true"
                                        value="{{ $data->phone_number }}" placeholder="71793152" />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="language" name="{{ __('app.language') }}" />
                                    <x-dropdown-language selected="{{ $data->language_id }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="timezone" name="{{ __('app.timezone') }}" />
                                    <x-dropdown-timezone selected="{{ $company->timezone }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="date_format" name="{{ __('app.date_format') }}" />
                                    <x-dropdown-date-format selected="{{ $company->date_format }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="time_format" name="{{ __('app.time_format') }}" />
                                    <x-dropdown-time-format selected="{{ $company->time_format }}" />
                                    <div class="valid-feedback"></div>
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
                    <!--Tab End: General -->
                    <!--Tab: Logo-->
				<div class="card logo_tab">
					<div class="card-header px-4 py-3">
						<h5 class="mb-0">{{ __('app.app_logo') }}</h5>
					</div>
					<div class="card-body p-4">
						<form class="row g-3 needs-validation" id="logoForm" action="{{ route('logo.store') }}" enctype="multipart/form-data" method="post">
							{{-- CSRF Protection --}}
							@csrf
							<div class="col-md-12">
								<x-label for="fevicon" name="{{ __('app.fevicon') }}" />
								<x-browse-image
									src='{{ url("/fevicon/" . $data->fevicon) }}'
									name='fevicon'
									imageid='uploaded-image-1'
									inputBoxClass='input-box-class-1'
									imageResetClass='image-reset-class-1' />
							</div>

							<div class="col-md-12">
								<x-label for="colored_logo" name="{{ __('app.colored_logo') }}" />
								<x-browse-image
									src='{{ url("/app/getimage/" . $data->colored_logo) }}'
									name='colored_logo'
									imageid='uploaded-image-2'
									inputBoxClass='input-box-class-2'
									imageResetClass='image-reset-class-2' />
							</div>

							<div class="col-md-12 d-none">
								<x-label for="light_logo" name="{{ __('app.light_logo') }}" />
								<x-browse-image
									src='{{ url("/app/getimage/" . $data->light_logo) }}'
									name='light_logo'
									imageid='uploaded-image-3'
									inputBoxClass='input-box-class-3'
									imageResetClass='image-reset-class-3' />
							</div>

							<div class="col-md-12">
								<div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
									<x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
									<x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
								</div>
							</div>
						</form>
					</div>
				</div>
				<!--Tab End: Logo -->
                    <!--Tab: SMTP -->
                    <div class="card smtp_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.smtp_settings') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="smtpForm" action="{{ route('smtp.store') }}"
                                enctype="multipart/form-data" method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                <div class="col-md-12">
                                    <x-label for="host" name="{{ __('app.host') }}" />
                                    <x-input type="text" name="host" :required="true"
                                        value="{{ $smtp->host }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="port" name="{{ __('app.port') }}" />
                                    <x-input type="text" name="port" :required="true"
                                        value="{{ $smtp->port }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="username" name="{{ __('user.username') }}" />
                                    <x-input type="text" name="username" :required="true"
                                        value="{{ $smtp->username }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="password" name="{{ __('user.password') }}" />
                                    <x-input type="password" name="password" :required="true"
                                        value="{{ $smtp->password }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="encryption" name="{{ __('app.encryption') }}" />
                                    <x-input type="text" name="encryption" :required="true"
                                        value="{{ $smtp->encryption }}" placeholder="tls" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="smtp_status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="{{ $data->status }}" dropdownName='smtp_status'
                                        optionNaming='EnableDisable' />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="smtpSubmit" class="primary px-4"
                                            text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: SMTP -->
                    <!--Tab: SMS API -->
                    <div class="card sms_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.sms_settings') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">

                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false"
                                            aria-controls="collapseOne">
                                            {{ __('app.twilio') }}
                                            @if ($twilio->status)
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </button>
                                    </h2>


                                    <div id="collapseOne" class="accordion-collapse collapse "
                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <form class="row g-3 needs-validation" id="twilioForm"
                                                action="{{ route('twilio.store') }}" enctype="multipart/form-data"
                                                method="post">
                                                {{-- CSRF Protection --}}
                                                @csrf
                                                <div class="col-md-12">
                                                    <div
                                                        class="alert border-0 border-start border-5 border-info alert-dismissible fade show py-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="font-35 text-info"><i
                                                                    class="bx bx-info-square"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <h6 class="mb-0 text-info">For more information</h6>
                                                                <div>
                                                                    Website: <a href="https://twilio.com" target="_blank"
                                                                        class="">https://twilio.com</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close"></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="sid" name="{{ __('app.account_SID') }}" />
                                                    <x-input type="text" name="sid" :required="true"
                                                        value="{{ $twilio->sid }}" />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="auth_token" name="{{ __('app.auth_token') }}" />
                                                    <x-input type="text" name="auth_token" :required="true"
                                                        value="{{ $twilio->auth_token }}" />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="twilio_number"
                                                        name="{{ __('app.twilio_phone_number') }}" />
                                                    <x-input type="text" name="twilio_number" :required="true"
                                                        value="{{ $twilio->twilio_number }}" />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="twilio_status" name="{{ __('app.status') }}" />
                                                    <x-dropdown-status selected="{{ $twilio->status }}"
                                                        dropdownName='twilio_status' optionNaming='EnableDisable' />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div
                                                        class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                        <x-button type="submit" buttonId="twilioSubmit"
                                                            class="primary px-4" text="{{ __('app.submit') }}" />
                                                        <x-anchor-tag href="{{ route('dashboard') }}"
                                                            text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">

                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false"
                                            aria-controls="collapseTwo">
                                            {{ __('app.vonage') }}

                                            @if ($vonage->status)
                                                <span class="badge bg-success">Active</span>
                                            @endif

                                        </button>
                                    </h2>

                                    <div id="collapseTwo" class="accordion-collapse collapse"
                                        aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <form class="row g-3 needs-validation" id="vonageForm"
                                                action="{{ route('vonage.store') }}" enctype="multipart/form-data"
                                                method="post">
                                                {{-- CSRF Protection --}}
                                                @csrf
                                                <div class="col-md-12">
                                                    <div
                                                        class="alert border-0 border-start border-5 border-info alert-dismissible fade show py-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="font-35 text-info"><i
                                                                    class="bx bx-info-square"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <h6 class="mb-0 text-info">For more information</h6>
                                                                <div>
                                                                    Website: <a
                                                                        href="https://www.vonage.com/communications-apis/sms/"
                                                                        target="_blank"
                                                                        class="">https://www.vonage.com/communications-apis/sms/</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close"></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="api_key" name="{{ __('app.api_key') }}" />
                                                    <x-input type="text" name="api_key" :required="true"
                                                        value="{{ $vonage->api_key }}" />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="api_secret" name="{{ __('app.api_secret') }}" />
                                                    <x-input type="text" name="api_secret" :required="true"
                                                        value="{{ $vonage->api_secret }}" />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <x-label for="vonage_status" name="{{ __('app.status') }}" />
                                                    <x-dropdown-status selected="{{ $vonage->status }}"
                                                        dropdownName='vonage_status' optionNaming='EnableDisable' />
                                                    <div class="valid-feedback"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div
                                                        class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                        <x-button type="submit" buttonId="twilioSubmit"
                                                            class="primary px-4" text="{{ __('app.submit') }}" />
                                                        <x-anchor-tag href="{{ route('dashboard') }}"
                                                            text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--Tab End: SMTP -->
                    <!--Tab: Clear Cache -->
                    <div class="card cache_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.clear_cache') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="cacheForm" action="{{ route('clear.cache') }}"
                                enctype="multipart/form-data" method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                <div class="col-md-12">
                                    <div
                                        class="alert border-0 border-start border-5 border-info alert-dismissible fade show py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="font-35 text-info"><i class='bx bx-info-square'></i>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-0 text-info">{{ __('app.info') }}</h6>
                                                <div>{{ __('app.cache_clear_message') }}</div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="cacheSubmit" class="primary px-4"
                                            text="{{ __('app.clear_cache') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: Clear Cache -->
                    <!--Tab: Clear App Error Log -->
                    <div class="card app_log_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.app_log') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="appLogForm" action="{{ route('clear.app.log') }}"
                                enctype="multipart/form-data" method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                <div class="col-md-12">
                                    <div
                                        class="alert border-0 border-start border-5 border-info alert-dismissible fade show py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="font-35 text-info"><i class='bx bx-info-square'></i>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-0 text-info">{{ __('app.info') }}</h6>
                                                <div>{{ __('app.clear_log_message') }}</div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="appLogSubmit" class="primary px-4"
                                            text="{{ __('app.clear_log') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: Clear Cache -->
                    <!--Tab: Database Backup -->
                    <div class="card database_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.database_backup') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="databaseForm"
                                action="{{ route('database.backup') }}" enctype="multipart/form-data" method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                <div class="col-md-12">
                                    Click the download button to initiate the process of downloading the database backup
                                    file.
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="databaseSubmit" class="primary px-4"
                                            text="{{ __('app.download') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: Database Backup -->

                </div>
            </div>
            <!--end row-->
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ versionedAsset('custom/js/settings.js') }}"></script>
@endsection
