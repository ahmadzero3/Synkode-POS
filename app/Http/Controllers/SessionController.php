<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionRequest;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Display the session list page.
     */
    public function list()
    {
        return view('session.list');
    }

    public function create()
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();
        return view('session.create', compact('users'));
    }

    public function store(SessionRequest $request)
    {
        // Prevent duplicate session per user (clear 422 JSON)
        if (Session::where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'message' => 'This user already has a session. You cannot create another one.',
                'errors'  => ['user_id' => ['This user already has a session. You cannot create another one.']],
            ], 422);
        }

        $validated = $request->validated();
        $validated['created_by']  = Auth::id();
        $validated['is_lifetime'] = $request->session_type === 'lifetime';
        $validated['is_yearly']   = $request->session_type === 'yearly';
        $validated['is_monthly']  = $request->session_type === 'monthly';
        $validated['is_weekly']   = $request->session_type === 'weekly';
        $validated['is_daily']    = $request->session_type === 'daily';

        if ($validated['is_lifetime']) {
            $validated['start_at'] = now();
            $validated['end_at']   = Carbon::createFromDate(2099, 12, 31)->endOfDay();
            $validated['duration_minutes'] = 52560000; // ~100 years
        } elseif ($validated['is_yearly']) {
            $validated['start_at'] = Carbon::createFromDate((int)$validated['start_year'], 1, 1)->startOfDay();
            $validated['end_at']   = Carbon::createFromDate((int)$validated['end_year'], 12, 31)->endOfDay();
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
        } elseif ($validated['is_monthly']) {
            $currentYear = now()->year;
            $validated['start_at'] = Carbon::createFromDate($currentYear, (int)$validated['start_month'], 1)->startOfDay();
            $validated['end_at']   = Carbon::createFromDate($currentYear, (int)$validated['end_month'], 1)->endOfMonth()->endOfDay();
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
            
            // Set time for monthly session
            $validated['monthly_start_time'] = $request->monthly_start_time;
            $validated['monthly_end_time'] = $request->monthly_end_time;
        } elseif ($validated['is_weekly']) {
            $today     = now();
            $startDay  = (int)$validated['start_day'];
            $endDay    = (int)$validated['end_day'];
            $validated['start_at'] = $today->copy()->startOfWeek()->addDays($startDay - 1)->startOfDay();
            $validated['end_at']   = $today->copy()->startOfWeek()->addDays($endDay - 1)->endOfDay();
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
            
            // Set time for weekly session
            $validated['weekly_start_time'] = $request->weekly_start_time;
            $validated['weekly_end_time'] = $request->weekly_end_time;
        } elseif ($validated['is_daily']) {
            $today = now();
            $startHour = (int)$validated['start_hour'];
            $endHour   = (int)$validated['end_hour'];
            $validated['start_at'] = $today->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
            $validated['end_at']   = $today->copy()->setHour($endHour)->setMinute(0)->setSecond(0);
            if ($endHour <= $startHour) {
                $validated['end_at'] = $validated['end_at']->addDay();
            }
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
        } else {
            $start = Carbon::parse($validated['start_at']);
            $validated['end_at'] = (clone $start)->addMinutes((int) $validated['duration_minutes']);
        }

        Session::create($validated);

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function edit($id)
    {
        $session = Session::findOrFail($id);
        $users   = User::select('id', 'first_name', 'last_name', 'email')->get();
        return view('session.edit', compact('session', 'users'));
    }

    public function update(SessionRequest $request)
    {
        $validated = $request->validated();
        $session   = Session::findOrFail($request->id);

        $validated['is_lifetime'] = $request->session_type === 'lifetime';
        $validated['is_yearly']   = $request->session_type === 'yearly';
        $validated['is_monthly']  = $request->session_type === 'monthly';
        $validated['is_weekly']   = $request->session_type === 'weekly';
        $validated['is_daily']    = $request->session_type === 'daily';

        if ($validated['is_lifetime']) {
            $validated['start_at'] = now();
            $validated['end_at']   = Carbon::createFromDate(2099, 12, 31)->endOfDay();
            $validated['duration_minutes'] = 52560000;
        } elseif ($validated['is_yearly']) {
            $validated['start_at'] = Carbon::createFromDate((int)$validated['start_year'], 1, 1)->startOfDay();
            $validated['end_at']   = Carbon::createFromDate((int)$validated['end_year'], 12, 31)->endOfDay();
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
        } elseif ($validated['is_monthly']) {
            $currentYear = now()->year;
            $validated['start_at'] = Carbon::createFromDate($currentYear, (int)$validated['start_month'], 1)->startOfDay();
            $validated['end_at']   = Carbon::createFromDate($currentYear, (int)$validated['end_month'], 1)->endOfMonth()->endOfDay();
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
            
            // Set time for monthly session
            $validated['monthly_start_time'] = $request->monthly_start_time;
            $validated['monthly_end_time'] = $request->monthly_end_time;
        } elseif ($validated['is_weekly']) {
            $today     = now();
            $startDay  = (int)$validated['start_day'];
            $endDay    = (int)$validated['end_day'];
            $validated['start_at'] = $today->copy()->startOfWeek()->addDays($startDay - 1)->startOfDay();
            $validated['end_at']   = $today->copy()->startOfWeek()->addDays($endDay - 1)->endOfDay();
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
            
            // Set time for weekly session
            $validated['weekly_start_time'] = $request->weekly_start_time;
            $validated['weekly_end_time'] = $request->weekly_end_time;
        } elseif ($validated['is_daily']) {
            $today = now();
            $startHour = (int)$validated['start_hour'];
            $endHour   = (int)$validated['end_hour'];
            $validated['start_at'] = $today->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
            $validated['end_at']   = $today->copy()->setHour($endHour)->setMinute(0)->setSecond(0);
            if ($endHour <= $startHour) {
                $validated['end_at'] = $validated['end_at']->addDay();
            }
            $validated['duration_minutes'] = $validated['end_at']->diffInMinutes($validated['start_at']);
        } else {
            $start = Carbon::parse($validated['start_at']);
            $validated['end_at'] = (clone $start)->addMinutes((int) $validated['duration_minutes']);
        }

        $session->update($validated);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function datatableList(Request $request)
    {
        $query = Session::with(['creator', 'user'])->select('sessions.*');

        // Handle search - only by user name and created_by
        if ($request->has('search') && !empty($request->input('search.value'))) {
            $searchTerm = $request->input('search.value');

            $query->where(function ($q) use ($searchTerm) {
                // Search by user name (first_name + last_name)
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name', 'like', "%{$searchTerm}%");
                })
                    // Search by creator username (created_by)
                    ->orWhereHas('creator', function ($creatorQuery) use ($searchTerm) {
                        $creatorQuery->where('username', 'like', "%{$searchTerm}%");
                    });
            });
        }

        return DataTables::of($query)
            ->editColumn('start_at', function ($row) {
                if ($row->is_lifetime) {
                    return 'Lifetime';
                } elseif ($row->is_yearly) {
                    return 'Yearly: ' . $row->start_year . ' - ' . $row->end_year;
                } elseif ($row->is_monthly) {
                    $months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    $timeInfo = '';
                    if ($row->monthly_start_time && $row->monthly_end_time) {
                        $timeInfo = ' (' . $row->monthly_start_time . ' - ' . $row->monthly_end_time . ')';
                    }
                    return 'Monthly: ' . ($months[$row->start_month] ?? $row->start_month) . ' - ' . ($months[$row->end_month] ?? $row->end_month) . $timeInfo;
                } elseif ($row->is_weekly) {
                    $days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $timeInfo = '';
                    if ($row->weekly_start_time && $row->weekly_end_time) {
                        $timeInfo = ' (' . $row->weekly_start_time . ' - ' . $row->weekly_end_time . ')';
                    }
                    return 'Weekly: ' . ($days[$row->start_day] ?? $row->start_day) . ' - ' . ($days[$row->end_day] ?? $row->end_day) . $timeInfo;
                } elseif ($row->is_daily) {
                    $startHour = sprintf('%02d:00', $row->start_hour);
                    $endHour = sprintf('%02d:00', $row->end_hour);
                    return 'Daily: ' . $startHour . ' - ' . $endHour;
                } else {
                    return $row->start_at ? $row->start_at->format('Y-m-d H:i') : '-';
                }
            })
            ->editColumn('end_at', function ($row) {
                if ($row->is_lifetime) {
                    return 'Lifetime';
                } elseif ($row->is_yearly) {
                    return 'Yearly: ' . $row->start_year . ' - ' . $row->end_year;
                } elseif ($row->is_monthly) {
                    $months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    $timeInfo = '';
                    if ($row->monthly_start_time && $row->monthly_end_time) {
                        $timeInfo = ' (' . $row->monthly_start_time . ' - ' . $row->monthly_end_time . ')';
                    }
                    return 'Monthly: ' . ($months[$row->start_month] ?? $row->start_month) . ' - ' . ($months[$row->end_month] ?? $row->end_month) . $timeInfo;
                } elseif ($row->is_weekly) {
                    $days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $timeInfo = '';
                    if ($row->weekly_start_time && $row->weekly_end_time) {
                        $timeInfo = ' (' . $row->weekly_start_time . ' - ' . $row->weekly_end_time . ')';
                    }
                    return 'Weekly: ' . ($days[$row->start_day] ?? $row->start_day) . ' - ' . ($days[$row->end_day] ?? $row->end_day) . $timeInfo;
                } elseif ($row->is_daily) {
                    $startHour = sprintf('%02d:00', $row->start_hour);
                    $endHour = sprintf('%02d:00', $row->end_hour);
                    return 'Daily: ' . $startHour . ' - ' . $endHour;
                } else {
                    return $row->end_at ? $row->end_at->format('Y-m-d H:i') : '-';
                }
            })
            ->editColumn('duration_minutes', function ($row) {
                if ($row->is_lifetime) {
                    return 'Lifetime';
                } elseif ($row->is_yearly) {
                    return 'Yearly: ' . $row->start_year . ' - ' . $row->end_year;
                } elseif ($row->is_monthly) {
                    $months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    $timeInfo = '';
                    if ($row->monthly_start_time && $row->monthly_end_time) {
                        $timeInfo = ' (' . $row->monthly_start_time . ' - ' . $row->monthly_end_time . ')';
                    }
                    return 'Monthly: ' . ($months[$row->start_month] ?? $row->start_month) . ' - ' . ($months[$row->end_month] ?? $row->end_month) . $timeInfo;
                } elseif ($row->is_weekly) {
                    $days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $timeInfo = '';
                    if ($row->weekly_start_time && $row->weekly_end_time) {
                        $timeInfo = ' (' . $row->weekly_start_time . ' - ' . $row->weekly_end_time . ')';
                    }
                    return 'Weekly: ' . ($days[$row->start_day] ?? $row->start_day) . ' - ' . ($days[$row->end_day] ?? $row->end_day) . $timeInfo;
                } elseif ($row->is_daily) {
                    $startHour = sprintf('%02d:00', $row->start_hour);
                    $endHour = sprintf('%02d:00', $row->end_hour);
                    return 'Daily: ' . $startHour . ' - ' . $endHour;
                } else {
                    return $row->duration_minutes;
                }
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format(app('company')['date_format']) : '';
            })
            ->addColumn('user_name', function ($row) {
                return $row->user ? $row->user->first_name . ' ' . $row->user->last_name : '-';
            })
            ->addColumn('created_by_display', function ($row) {
                return $row->creator ? $row->creator->username : '';
            })
            ->addColumn('session_type', function ($row) {
                if ($row->is_lifetime) {
                    return 'Lifetime';
                } elseif ($row->is_yearly) {
                    return 'Yearly';
                } elseif ($row->is_monthly) {
                    return 'Monthly';
                } elseif ($row->is_weekly) {
                    return 'Weekly';
                } elseif ($row->is_daily) {
                    return 'Daily';
                } else {
                    return 'Manual';
                }
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;
                $editUrl = route('session.edit', ['id' => $id]);
                $deleteUrl = route('session.delete', ['id' => $id]);

                return '
            <div class="dropdown ms-auto">
                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="' . $editUrl . '">
                            <i class="bx bx-edit"></i> ' . __('app.edit') . '
                        </a>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id="' . $id . '">
                            <i class="bx bx-trash"></i> ' . __('app.delete') . '
                        </button>
                    </li>
                </ul>
            </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function delete(Request $request)
    {
        $ids = $request->input('record_ids', []);
        if (!empty($ids)) {
            Session::whereIn('id', $ids)->delete();
        }

        return response()->json([
            'message' => __('app.record_deleted_successfully'),
        ]);
    }
}