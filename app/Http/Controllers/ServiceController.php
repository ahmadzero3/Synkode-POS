<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\ServiceRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Service;
use App\Models\Tax;


class ServiceController extends Controller
{
    /**
     * Create a new service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {

        return view('service.create');
    }

    /**
     * Edit a service.
     *
     * @param int $id The ID of the service to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {

        $service = Service::find($id);

        return view('service.edit', compact('service'));
    }

    /**
     * Return JsonResponse
     * */
    public function store(ServiceRequest $request)
    {

        $filename = null;

        // Get the validated data from the ServiceRequest
        $validatedData = $request->validated();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $filename = $this->uploadImage($request->file('image'));
        }

        $validatedData['image_path'] = $filename;

        // Create a new service record using Eloquent and save it
        $newService = Service::create($validatedData);

        return response()->json([
            'status'    => true,
            'message' => __('app.record_saved_successfully'),
            'data'  => [
                'id' => $newService->id,
                'name' => $newService->name,
            ]
        ]);
    }

    /**
     * Return JsonResponse
     * */
    public function update(ServiceRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $validatedData['image_path']   = $this->uploadImage($request->file('image'));
        }

        // Save the service details
        Service::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    private function uploadImage($image): String
    {
        // Generate a unique filename for the image
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        $directory = 'public/images/services';

        // Create the directory if it doesn't exist
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // Store the file in the 'service' directory with the specified filename
        Storage::putFileAs($directory, $image, $filename);

        return $filename;
    }

    public function list(): View
    {
        return view('service.list');
    }

    public function datatableList(Request $request)
    {

        $data = Service::with('tax');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('tax_name', function ($row) {
                return $row->tax->name;
            })
            ->addColumn('tax_type', function ($row) {
                return ucfirst($row->tax_type);
            })
            ->addColumn('username', function ($row) {
                return $row->user->username ?? '';
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('service.edit', ['id' => $id]);
                $deleteUrl = route('service.delete', ['id' => $id]);


                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> ' . __('app.edit') . '</a>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id=' . $id . '><i class="bx bx-trash"></i> ' . __('app.delete') . '</button>
                                </li>
                            </ul>
                        </div>';
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function delete(Request $request): JsonResponse
    {

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = Service::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id', ['record_id' => $recordId]),
                ]);
            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */


        try {
            // Attempt deletion (as in previous responses)
            Service::whereIn('id', $selectedRecordIds)->delete();
            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status'    => false,
                    'message' => __('app.cannot_delete_records'),
                ], 409);
            } else {
                // Handle other types of database exceptions
                return response()->json([
                    'status'    => false,
                    'message' => __('app.unexpected_error_occurred'),
                ], 500);
            }
        }
    }

    /**
     * Get Service Records
     * @return JsonResponse
     * */
    function getRecords(Request $request): JsonResponse
    {
        $selectedRecordId = $request->input('service_id');

        $record = Service::where('id', $selectedRecordId)
            ->select('id', 'name', 'description', 'unit_price', 'tax_id', 'tax_type', 'status')
            ->first();
        /**
         * If no records
         * @return JsonResponse 
         * */
        if ($record->count() == 0) {
            return response()->json([
                'status'    => false,
                'message' => __('app.record_not_found'),
            ]);
        }
        /**
         * Return JsonResponse with Actual Records
         * */

        $preparedData = [
            'id'                => $record->id,
            'name'              => $record->name,
            'description'       => $record->description ?? '',
            'quantity'          => 1,
            'unit_price'        => $record->unit_price,
            'total_price'       => $record->total_price,
            'discount'          => 0,
            'discount_type'     => 'percentage',
            'discount_amount'   => 0,
            'total_price_after_discount'   => 0,
            'start_at'          => null,
            'end_at'            => null,
            'tax_id'            => $record->tax_id,
            'tax_type'          => $record->tax_type,
            'tax_amount'        => 0,
            'status'            => $record->status,
            'assigned_user_id'  => $record->assigned_user_id ?? '',
            'assigned_user_note' => $record->assigned_user_note ?? '',
            'taxList'           => Tax::all(),
        ];

        return response()->json([
            'status'    => true,
            'message' => null,
            'data' => $preparedData,
        ]);
    }
}
