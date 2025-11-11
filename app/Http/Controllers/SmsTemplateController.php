<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\SmsTemplateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\SmsTemplate;
use Illuminate\Database\QueryException;

class SmsTemplateController extends Controller
{
    /**
     * Create a new sms-template.
     *
     * @return \Illuminate\View\View
     */
    public function create() : View {
        return view('sms-template.create');
    }

    /**
     * Edit a sms-template.
     *
     * @param int $id The ID of the sms-template to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) : View {

        $template = SmsTemplate::find($id);

        return view('sms-template.edit', compact('template'));
    }
    /**
     * Return JsonResponse
     * */
    public function store(SmsTemplateRequest $request) : JsonResponse {

        $filename = null;

        // Get the validated data from the SmsTemplateRequest
        $validatedData = $request->validated();

        // Create a new tax record using Eloquent and save it
        SmsTemplate::create($validatedData);

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    /**
     * Return JsonResponse
     * */
    public function update(SmsTemplateRequest $request) : JsonResponse {
        $validatedData = $request->validated();

        // Save the tax details
        SmsTemplate::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function list() : View {
        return view('sms-template.list');
    }

    public function datatableList(Request $request){
        
        $data = SmsTemplate::get();

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                            $editUrl = route('sms.template.edit', ['id' => $id]);
                            $deleteUrl = route('sms.template.delete', ['id' => $id]);


                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">';
                                $actionBtn .='<li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                </li>';
                                $actionBtn .= ($row->delete_flag ===0) ?'<li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>':'';
                                $actionBtn .='</li>
                            </ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function delete(Request $request) : JsonResponse {

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = SmsTemplate::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id',['record_id' => $recordId]),
                ],409);

            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */
        try{
            /**
             * Validate
             * @return false if default template
             * */
            if($record->delete_flag === 1){
                return response()->json([
                    'status'    => false,
                    'message' => __('message.cannot_delete_default_tempate'),
                ],409);
            }

            SmsTemplate::whereIn('id', $selectedRecordIds)->where('delete_flag','=','0')->delete();

        }catch (QueryException $e){

            return response()->json(['message' => __('app.cannot_delete_records')], 409);

        }


        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }
}