<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\EmailTemplateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\EmailTemplate;
use Illuminate\Database\QueryException;

class EmailTemplateController extends Controller
{
    /**
     * Create a new email-template.
     *
     * @return \Illuminate\View\View
     */
    public function create() : View {
        return view('email-template.create');
    }

    /**
     * Edit a email-template.
     *
     * @param int $id The ID of the email-template to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) : View {

        $template = EmailTemplate::find($id);

        return view('email-template.edit', compact('template'));
    }

    /**
     * Return JsonResponse
     * */
    public function store(EmailTemplateRequest $request) : JsonResponse {

        // Get the validated data from the EmailTemplateRequest
        $validatedData = $request->validated();

        // Create a new tax record using Eloquent and save it
        EmailTemplate::create($validatedData);

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    /**
     * @return JsonResponse
     * */
    public function update(EmailTemplateRequest $request) : JsonResponse {
        $validatedData = $request->validated();

        // Save the tax details
        EmailTemplate::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    /**
     * Create a new email-template.
     *
     * @return \Illuminate\View\View
     */
    public function list() : View {
        return view('email-template.list');
    }

    /**
     * Create a new email-template.
     *
     * @return DataTables
     */
    public function datatableList(Request $request){
        
        $data = EmailTemplate::query();

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                            $editUrl = route('email.template.edit', ['id' => $id]);
                            $deleteUrl = route('email.template.delete', ['id' => $id]);


                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">';
                                $actionBtn .= '<li>
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
            $record = EmailTemplate::find($recordId);
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

            EmailTemplate::whereIn('id', $selectedRecordIds)->where('delete_flag','=','0')->delete();

        }catch (QueryException $e){

            return response()->json(['message' => __('app.cannot_delete_records')], 409);

        }


        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }
}