@extends('layouts.app')

@section('title', __('app.edit_session'))

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.session', 'app.edit_session']" />

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('app.edit_session') }}</h5>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('session.update') }}" method="POST" class="row g-3 needs-validation"
                                id="sessionForm" novalidate>
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $session->id }}">

                                <!-- Session Type Radio Buttons -->
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <x-label name="{{ __('app.Session Type') }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input session-type" type="radio" name="session_type"
                                                id="lifetime_session" value="lifetime"
                                                {{ $session->is_lifetime ? 'checked' : '' }}>
                                            <label class="form-check-label" for="lifetime_session">
                                                {{ __('app.Life Time') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input session-type" type="radio" name="session_type"
                                                id="yearly_session" value="yearly"
                                                {{ $session->is_yearly ? 'checked' : '' }}>
                                            <label class="form-check-label" for="yearly_session">
                                                {{ __('app.Yearly') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input session-type" type="radio" name="session_type"
                                                id="monthly_session" value="monthly"
                                                {{ $session->is_monthly ? 'checked' : '' }}>
                                            <label class="form-check-label" for="monthly_session">
                                                {{ __('app.Monthly') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input session-type" type="radio" name="session_type"
                                                id="weekly_session" value="weekly"
                                                {{ $session->is_weekly ? 'checked' : '' }}>
                                            <label class="form-check-label" for="weekly_session">
                                                {{ __('app.Weekly') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input session-type" type="radio" name="session_type"
                                                id="daily_session" value="daily"
                                                {{ $session->is_daily ? 'checked' : '' }}>
                                            <label class="form-check-label" for="daily_session">
                                                {{ __('app.Daily') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input session-type" type="radio" name="session_type"
                                                id="manual_session" value="manual"
                                                {{ !$session->is_lifetime && !$session->is_yearly && !$session->is_monthly && !$session->is_weekly && !$session->is_daily ? 'checked' : '' }}>
                                            <label class="form-check-label" for="manual_session">
                                                {{ __('app.Manual') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 1: User -->
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <x-label for="user_id" name="{{ __('app.User') }}" />
                                        <select name="user_id" id="user_id" class="form-select user-ajax w-100" required
                                            data-placeholder="{{ __('app.select') }} {{ __('app.User') }}"
                                            style="width:100%">
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ $session->user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>

                                    <div class="col-md-6 manual-fields"
                                        style="display: {{ !$session->is_lifetime && !$session->is_yearly && !$session->is_monthly && !$session->is_weekly && !$session->is_daily ? 'block' : 'none' }};">
                                        <x-label for="start_at" name="{{ __('app.Start Time') }}" />
                                        <x-input type="text" id="start_at" additionalClasses="session-datetime-picker"
                                            name="start_at" :required="true"
                                            value="{{ $session->start_at ? $session->start_at->format('Y-m-d H:i') : '' }}"
                                            placeholder="{{ __('app.Select start date and time') }}" />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                </div>

                                <!-- Yearly Fields (side by side on one row, 50% each) -->
                                <div class="row g-3 yearly-fields"
                                    style="display: {{ $session->is_yearly ? 'flex' : 'none' }};">

                                    <div class="col-md-6 d-flex flex-column">
                                        <x-label for="start_year" name="{{ __('app.Start Year') }}"
                                            class="mb-2 fw-semibold w-100" />
                                        <x-input type="text" id="start_year"
                                            additionalClasses="session-year-picker w-100" name="start_year"
                                            :required="true" value="{{ $session->start_year }}"
                                            placeholder="{{ __('app.Enter start year') }}" readonly />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>

                                    <div class="col-md-6 d-flex flex-column">
                                        <x-label for="end_year" name="{{ __('app.End Year') }}"
                                            class="mb-2 fw-semibold w-100" />
                                        <x-input type="text" id="end_year"
                                            additionalClasses="session-year-picker w-100" name="end_year"
                                            :required="true" value="{{ $session->end_year }}"
                                            placeholder="{{ __('app.Enter end year') }}" readonly />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                </div>


                                <!-- Monthly Fields -->
                                <div class="row g-3 monthly-fields"
                                    style="display: {{ $session->is_monthly ? 'flex' : 'none' }};">
                                    <div class="col-md-6">
                                        <x-label for="start_month" name="{{ __('app.Start Month') }}" />
                                        <div class="input-group">
                                            <x-input type="text" id="start_month"
                                                additionalClasses="session-month-picker" name="start_month"
                                                :required="true" value="{{ $session->start_month }}"
                                                placeholder="{{ __('app.Select start month') }}" readonly />
                                            <span class="input-group-text month-display" style="min-width: 100px;">
                                                @if ($session->start_month)
                                                    {{ DateTime::createFromFormat('!m', $session->start_month)->format('F') }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="end_month" name="{{ __('app.End Month') }}" />
                                        <div class="input-group">
                                            <x-input type="text" id="end_month"
                                                additionalClasses="session-month-picker" name="end_month"
                                                :required="true" value="{{ $session->end_month }}"
                                                placeholder="{{ __('app.Select end month') }}" readonly />
                                            <span class="input-group-text month-display" style="min-width: 100px;">
                                                @if ($session->end_month)
                                                    {{ DateTime::createFromFormat('!m', $session->end_month)->format('F') }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    
                                    <!-- Monthly Time Selection -->
                                    <div class="col-md-6">
                                        <x-label for="monthly_start_time" name="{{ __('app.Start Time') }}" />
                                        <x-input type="time" id="monthly_start_time" name="monthly_start_time"
                                            :required="true" value="{{ $session->monthly_start_time ?? '09:00' }}" />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="monthly_end_time" name="{{ __('app.End Time') }}" />
                                        <x-input type="time" id="monthly_end_time" name="monthly_end_time"
                                            :required="true" value="{{ $session->monthly_end_time ?? '17:00' }}" />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                </div>

                                <!-- Weekly Fields -->
                                <div class="row g-3 weekly-fields"
                                    style="display: {{ $session->is_weekly ? 'flex' : 'none' }};">
                                    <div class="col-md-6">
                                        <x-label for="start_day" name="{{ __('app.Start Day') }}" />
                                        <select name="start_day" id="start_day" class="form-control day-picker" required>
                                            <option value="">{{ __('app.Select start day') }}</option>
                                            <option value="1" {{ $session->start_day == 1 ? 'selected' : '' }}>Monday
                                            </option>
                                            <option value="2" {{ $session->start_day == 2 ? 'selected' : '' }}>
                                                Tuesday</option>
                                            <option value="3" {{ $session->start_day == 3 ? 'selected' : '' }}>
                                                Wednesday</option>
                                            <option value="4" {{ $session->start_day == 4 ? 'selected' : '' }}>
                                                Thursday</option>
                                            <option value="5" {{ $session->start_day == 5 ? 'selected' : '' }}>Friday
                                            </option>
                                            <option value="6" {{ $session->start_day == 6 ? 'selected' : '' }}>
                                                Saturday</option>
                                            <option value="7" {{ $session->start_day == 7 ? 'selected' : '' }}>Sunday
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="end_day" name="{{ __('app.End Day') }}" />
                                        <select name="end_day" id="end_day" class="form-control day-picker" required>
                                            <option value="">{{ __('app.Select end day') }}</option>
                                            <option value="1" {{ $session->end_day == 1 ? 'selected' : '' }}>Monday
                                            </option>
                                            <option value="2" {{ $session->end_day == 2 ? 'selected' : '' }}>Tuesday
                                            </option>
                                            <option value="3" {{ $session->end_day == 3 ? 'selected' : '' }}>
                                                Wednesday</option>
                                            <option value="4" {{ $session->end_day == 4 ? 'selected' : '' }}>Thursday
                                            </option>
                                            <option value="5" {{ $session->end_day == 5 ? 'selected' : '' }}>Friday
                                            </option>
                                            <option value="6" {{ $session->end_day == 6 ? 'selected' : '' }}>Saturday
                                            </option>
                                            <option value="7" {{ $session->end_day == 7 ? 'selected' : '' }}>Sunday
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    
                                    <!-- Weekly Time Selection -->
                                    <div class="col-md-6">
                                        <x-label for="weekly_start_time" name="{{ __('app.Start Time') }}" />
                                        <x-input type="time" id="weekly_start_time" name="weekly_start_time"
                                            :required="true" value="{{ $session->weekly_start_time ?? '09:00' }}" />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="weekly_end_time" name="{{ __('app.End Time') }}" />
                                        <x-input type="time" id="weekly_end_time" name="weekly_end_time"
                                            :required="true" value="{{ $session->weekly_end_time ?? '17:00' }}" />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                </div>

                                <!-- Daily Fields -->
                                <div class="row g-3 daily-fields"
                                    style="display: {{ $session->is_daily ? 'flex' : 'none' }};">
                                    <div class="col-md-6">
                                        <x-label for="start_hour" name="{{ __('app.Start Hour') }}" />
                                        <select name="start_hour" id="start_hour" class="form-control hour-picker" required>
                                            <option value="">{{ __('app.Select start hour') }}</option>
                                            @for ($i = 0; $i < 24; $i++)
                                                <option value="{{ $i }}" {{ $session->start_hour == $i ? 'selected' : '' }}>
                                                    {{ sprintf('%02d:00', $i) }}
                                                </option>
                                            @endfor
                                        </select>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="end_hour" name="{{ __('app.End Hour') }}" />
                                        <select name="end_hour" id="end_hour" class="form-control hour-picker" required>
                                            <option value="">{{ __('app.Select end hour') }}</option>
                                            @for ($i = 0; $i < 24; $i++)
                                                <option value="{{ $i }}" {{ $session->end_hour == $i ? 'selected' : '' }}>
                                                    {{ sprintf('%02d:00', $i) }}
                                                </option>
                                            @endfor
                                        </select>
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                </div>

                                <!-- Row 2: End Time + Duration -->
                                <div class="row g-3 manual-fields"
                                    style="display: {{ !$session->is_lifetime && !$session->is_yearly && !$session->is_monthly && !$session->is_weekly && !$session->is_daily ? 'flex' : 'none' }};">
                                    <div class="col-md-6">
                                        <x-label for="end_at" name="{{ __('app.End Time') }}" />
                                        <x-input type="text" id="end_at"
                                            additionalClasses="session-datetime-picker" name="end_at" :required="true"
                                            value="{{ $session->end_at ? $session->end_at->format('Y-m-d H:i') : '' }}"
                                            placeholder="{{ __('app.Select end date and time') }}" />
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <x-label for="duration_minutes" name="{{ __('app.Duration (Minutes)') }}" />
                                        <input type="number" name="duration_minutes" id="duration_minutes"
                                            value="{{ $session->duration_minutes }}" class="form-control"
                                            placeholder="{{ __('app.Enter duration in minutes') }}">
                                        <div class="invalid-feedback">{{ __('app.This field is required.') }}</div>
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4">
                                        {{ __('app.update') }}
                                    </button>
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
    <script src="{{ versionedAsset('custom/js/session/session.js') }}"></script>
@endsection