<?php

namespace App\Http\Controllers;

use App\Models\AppLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class AppLogController extends Controller
{
    public function list()
    {
        return view('app-log.list');
    }

    public function datatableList(Request $request)
    {
        // This will now use the updated scope that shows ALL logs
        $query = AppLog::logs();

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('severity_badge', function ($row) {
                return '<div class="' . $row->badge_class . '">' . ucfirst($row->severity) . '</div>';
            })
            ->addColumn('type_badge', function ($row) {
                return '<div class="' . $row->type_badge_class . '">' . ucfirst($row->type) . '</div>';
            })
            ->addColumn('message', function ($row) {
                return Str::limit($row->formatted_message, 100);
            })
            ->addColumn('action', function ($row) {
                return '
                <div class="dropdown ms-auto">
                    <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <button type="button" class="dropdown-item view-details" data-log-id="' . $row->uuid . '">
                                <i class="bx bx-show"></i> ' . __('app.view_details') . '
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id="' . $row->uuid . '">
                                <i class="bx bx-trash"></i> ' . __('app.delete') . '
                            </button>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['severity_badge', 'type_badge', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $log = AppLog::where('uuid', $id)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $log->type,
                'severity' => $log->severity,
                'message' => $log->formatted_message,
                'content' => $log->content,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'tags' => $log->content['tags'] ?? [],
            ]
        ]);
    }

    public function delete(Request $request)
    {
        $ids = $request->input('record_ids', []);

        if (!empty($ids)) {
            AppLog::whereIn('uuid', $ids)->delete();
        }

        return response()->json([
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

    public function clearAll()
    {
        AppLog::logs()->delete();

        return response()->json([
            'message' => __('app.all_records_deleted_successfully'),
        ]);
    }
}