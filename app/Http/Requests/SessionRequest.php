<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->id ?? null;
        $isLifetime = $this->session_type === 'lifetime';
        $isYearly   = $this->session_type === 'yearly';
        $isMonthly  = $this->session_type === 'monthly';
        $isWeekly   = $this->session_type === 'weekly';
        $isDaily    = $this->session_type === 'daily';

        $userUniqueRule = Rule::unique('sessions', 'user_id');
        if ($id) {
            $userUniqueRule = $userUniqueRule->ignore($id);
        }

        $rules = [
            'user_id'      => ['required', 'exists:users,id', $userUniqueRule],
            'session_type' => 'required|in:lifetime,yearly,monthly,weekly,daily,manual',
        ];

        if (!$isLifetime && !$isYearly && !$isMonthly && !$isWeekly && !$isDaily) {
            $rules['start_at']         = 'required|date';
            $rules['end_at']           = 'nullable|date|after:start_at';
            $rules['duration_minutes'] = 'required|integer|min:1';
        }

        if ($isYearly) {
            $rules['start_year'] = 'required|integer|min:2000|max:2099';
            $rules['end_year']   = 'required|integer|min:2000|max:2099|gte:start_year';
        }

        if ($isMonthly) {
            $rules['start_month'] = 'required|integer|min:1|max:12';
            $rules['end_month']   = 'required|integer|min:1|max:12|gte:start_month';
            $rules['monthly_start_time'] = 'required|date_format:H:i';
            $rules['monthly_end_time']   = 'required|date_format:H:i|after:monthly_start_time';
        }

        if ($isWeekly) {
            $rules['start_day'] = 'required|integer|min:1|max:7';
            $rules['end_day']   = 'required|integer|min:1|max:7|gte:start_day';
            $rules['weekly_start_time'] = 'required|date_format:H:i';
            $rules['weekly_end_time']   = 'required|date_format:H:i|after:weekly_start_time';
        }

        if ($isDaily) {
            $rules['start_hour'] = 'required|integer|min:0|max:23';
            $rules['end_hour']   = 'required|integer|min:0|max:23|gte:start_hour';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_id.required'   => 'User is required.',
            'user_id.exists'     => 'Selected User is invalid.',
            'user_id.unique'     => 'This user already has a session. You cannot create another one.',
            'session_type.required' => 'Session type is required.',
            'session_type.in'       => 'Session type must be either lifetime, yearly, monthly, weekly, daily or manual.',

            'start_at.required'  => 'Start Time is required.',
            'end_at.after'       => 'End Time must be after Start Time.',
            'duration_minutes.required' => 'Duration is required.',
            'duration_minutes.integer'  => 'Duration must be a number.',
            'duration_minutes.min'      => 'Duration must be at least 1 minute.',

            'start_year.required' => 'Start Year is required.',
            'start_year.integer'  => 'Start Year must be a number.',
            'start_year.min'      => 'Start Year must be at least 2000.',
            'start_year.max'      => 'Start Year cannot exceed 2099.',
            'end_year.required'   => 'End Year is required.',
            'end_year.integer'    => 'End Year must be a number.',
            'end_year.min'        => 'End Year must be at least 2000.',
            'end_year.max'        => 'End Year cannot exceed 2099.',
            'end_year.gte'        => 'End Year must be greater than or equal to Start Year.',

            'start_month.required' => 'Start Month is required.',
            'start_month.integer'  => 'Start Month must be a number.',
            'start_month.min'      => 'Start Month must be at least 1.',
            'start_month.max'      => 'Start Month cannot exceed 12.',
            'end_month.required'   => 'End Month is required.',
            'end_month.integer'    => 'End Month must be a number.',
            'end_month.min'        => 'End Month must be at least 1.',
            'end_month.max'        => 'End Month cannot exceed 12.',
            'end_month.gte'        => 'End Month must be greater than or equal to Start Month.',

            'start_day.required' => 'Start Day is required.',
            'start_day.integer'  => 'Start Day must be a number.',
            'start_day.min'      => 'Start Day must be at least 1.',
            'start_day.max'      => 'Start Day cannot exceed 7.',
            'end_day.required'   => 'End Day is required.',
            'end_day.integer'    => 'End Day must be a number.',
            'end_day.min'        => 'End Day must be at least 1.',
            'end_day.max'        => 'End Day cannot exceed 7.',
            'end_day.gte'        => 'End Day must be greater than or equal to Start Day.',

            'start_hour.required' => 'Start Hour is required.',
            'start_hour.integer'  => 'Start Hour must be a number.',
            'start_hour.min'      => 'Start Hour must be at least 0.',
            'start_hour.max'      => 'Start Hour cannot exceed 23.',
            'end_hour.required'   => 'End Hour is required.',
            'end_hour.integer'    => 'End Hour must be a number.',
            'end_hour.min'        => 'End Hour must be at least 0.',
            'end_hour.max'        => 'End Hour cannot exceed 23.',
            'end_hour.gte'        => 'End Hour must be greater than or equal to Start Hour.',

            'monthly_start_time.required' => 'Monthly Start Time is required.',
            'monthly_start_time.date_format' => 'Monthly Start Time must be in valid time format (HH:MM).',
            'monthly_end_time.required' => 'Monthly End Time is required.',
            'monthly_end_time.date_format' => 'Monthly End Time must be in valid time format (HH:MM).',
            'monthly_end_time.after' => 'Monthly End Time must be after Start Time.',

            'weekly_start_time.required' => 'Weekly Start Time is required.',
            'weekly_start_time.date_format' => 'Weekly Start Time must be in valid time format (HH:MM).',
            'weekly_end_time.required' => 'Weekly End Time is required.',
            'weekly_end_time.date_format' => 'Weekly End Time must be in valid time format (HH:MM).',
            'weekly_end_time.after' => 'Weekly End Time must be after Start Time.',
        ];
    }
}