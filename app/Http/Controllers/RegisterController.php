<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RegisterController extends Controller
{
    public function list()
    {
        return view('register.list');
    }

    public function create()
    {
        $lastCode = Register::max('code');
        $nextCode = $lastCode ? $lastCode + 1 : 1;

        return view('register.create', compact('nextCode'));
    }

    public function store(RegisterRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['code'])) {
            $lastCode = Register::max('code');
            $validated['code'] = $lastCode ? $lastCode + 1 : 1;
        }

        if (Register::where('code', $validated['code'])->exists()) {
            return response()->json([
                'message' => __('register.code') . ' ' . $validated['code'] . ' ' . __('register.already_exists'),
            ], 422);
        }

        $validated['created_by'] = Auth::id();
        $validated['active'] = $request->has('active') ? 1 : 0;

        Register::create($validated);

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }


    public function edit($id)
    {
        $register = Register::findOrFail($id);
        return view('register.edit', compact('register'));
    }

    public function update(RegisterRequest $request)
    {
        $validated = $request->validated();
        $register = Register::findOrFail($request->id);

        if (isset($validated['code']) && $register->code != $validated['code']) {
            if (Register::where('code', $validated['code'])->where('id', '!=', $register->id)->exists()) {
                return response()->json([
                    'message' => __('register.code') . ' ' . $validated['code'] . ' ' . __('register.already_exists'),
                ], 422);
            }
        }

        $validated['active'] = $request->has('active') ? 1 : 0;

        $register->update($validated);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function datatableList(Request $request)
    {
        $query = Register::with(['creator', 'user'])->select('registers.*');

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format(app('company')['date_format']) : '';
            })
            ->addColumn('user_name', function ($row) {
                return $row->user ? $row->user->username : '-';
            })
            ->addColumn('created_by_display', function ($row) {
                return $row->creator ? $row->creator->username : '';
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;
                $editUrl = route('register.edit', ['id' => $id]);
                $deleteUrl = route('register.delete', ['id' => $id]);

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
            Register::whereIn('id', $ids)->delete();
        }

        return response()->json([
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

    public function ajaxList(\Illuminate\Http\Request $request)
    {
        $search = trim($request->input('search', ''));
        $query  = \App\Models\Register::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $registers = $query->orderBy('id', 'desc')->paginate(10);

        $results = $registers->map(function ($r) {
            return [
                'id'   => $r->id,
                'text' => $r->name . ' (' . $r->code . ')',
            ];
        });

        return response()->json([
            'results' => $results,
            'hasMore' => $registers->hasMorePages(),
        ]);
    }
}
