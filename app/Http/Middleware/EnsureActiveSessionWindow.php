<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveSessionWindow
{
    /**
     * Enforce a timed session window ONLY for users who have at least
     * one configured session row. Users with no rows are not affected.
     *
     * Policy:
     * - If user has NO session rows -> allow access (no enforcement).
     * - If user has session rows:
     *      allow ONLY when (start_at <= now <= end_at), otherwise logout + block.
     * - Lifetime sessions always allow access.
     * - Yearly sessions allow access when current year is between start_year and end_year.
     * - Monthly sessions allow access when current month is between start_month and end_month
     *   AND current time is between monthly_start_time and monthly_end_time.
     * - Weekly sessions allow access when current day of week is between start_day and end_day
     *   AND current time is between weekly_start_time and weekly_end_time.
     * - Daily sessions allow access when current hour is between start_hour and end_hour.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Optional: bypass for super admins (remove if you don't want this)
        if (method_exists($user, 'hasRole') && $user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        // IMPORTANT: reference your Eloquent model explicitly to avoid name clash with Laravel's session
        $sessionsQuery = \App\Models\Session::where('user_id', $user->id);

        // ✔ If user has NO configured windows -> don't enforce
        $hasAnyWindow = $sessionsQuery->exists();
        if (!$hasAnyWindow) {
            return $next($request);
        }

        // Check if user has any lifetime session
        $hasLifetimeSession = (clone $sessionsQuery)->where('is_lifetime', true)->exists();
        if ($hasLifetimeSession) {
            return $next($request);
        }

        // Check if user has any valid yearly session
        $currentYear = now()->year;
        $hasValidYearlySession = (clone $sessionsQuery)
            ->where('is_yearly', true)
            ->where('start_year', '<=', $currentYear)
            ->where('end_year', '>=', $currentYear)
            ->exists();
        if ($hasValidYearlySession) {
            return $next($request);
        }

        // Check if user has any valid monthly session
        $currentMonth = now()->month;
        $currentTime = now()->format('H:i');
        $hasValidMonthlySession = (clone $sessionsQuery)
            ->where('is_monthly', true)
            ->where('start_month', '<=', $currentMonth)
            ->where('end_month', '>=', $currentMonth)
            ->where(function ($query) use ($currentTime) {
                $query->where(function ($q) use ($currentTime) {
                    $q->where('monthly_start_time', '<=', $currentTime)
                        ->where('monthly_end_time', '>=', $currentTime);
                })->orWhereNull('monthly_start_time')
                    ->orWhereNull('monthly_end_time');
            })
            ->exists();
        if ($hasValidMonthlySession) {
            return $next($request);
        }

        // Check if user has any valid weekly session
        $currentDayOfWeek = now()->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
        $hasValidWeeklySession = (clone $sessionsQuery)
            ->where('is_weekly', true)
            ->where('start_day', '<=', $currentDayOfWeek)
            ->where('end_day', '>=', $currentDayOfWeek)
            ->where(function ($query) use ($currentTime) {
                $query->where(function ($q) use ($currentTime) {
                    $q->where('weekly_start_time', '<=', $currentTime)
                        ->where('weekly_end_time', '>=', $currentTime);
                })->orWhereNull('weekly_start_time')
                    ->orWhereNull('weekly_end_time');
            })
            ->exists();
        if ($hasValidWeeklySession) {
            return $next($request);
        }

        // Check if user has any valid daily session
        $currentHour = now()->hour;
        $hasValidDailySession = (clone $sessionsQuery)
            ->where('is_daily', true)
            ->where('start_hour', '<=', $currentHour)
            ->where('end_hour', '>=', $currentHour)
            ->exists();
        if ($hasValidDailySession) {
            return $next($request);
        }

        // ✔ User has some configured manual windows -> enforce active window
        $now = now();
        $hasActiveWindow = (clone $sessionsQuery)
            ->where('is_lifetime', false)
            ->where('is_yearly', false)
            ->where('is_monthly', false)
            ->where('is_weekly', false)
            ->where('is_daily', false)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->exists();

        if ($hasActiveWindow) {
            return $next($request);
        }

        // ❌ No active window -> logout + block
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Access denied: No active session window.');
    }
}
