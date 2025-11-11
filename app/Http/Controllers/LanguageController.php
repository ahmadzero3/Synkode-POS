<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\Language;
use App\Http\Requests\LanguageRequest;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use App\Enums\Language as LanguageEnum;
use Illuminate\Database\QueryException;
use InvalidArgumentException;


class LanguageController extends Controller
{
    private $excludedIdsToDelete;

    public function __construct()
    {
        $this->excludedIdsToDelete = array_map(fn(LanguageEnum $language) => $language->value, LanguageEnum::cases());
    }

    /**
     * Create a new payment-types.
     *
     * @return \Illuminate\View\View
     */
    public function create() : View {
        session(['record' => [
                                    'type' => 'info',
                                    'status' => "Information",
                                    'message' => "Used to add a new language. After adding this, you need to create a new language folder and convert the English language files to your new language, for example, French.
                                    Example: Source-Code/lang/fr/* — Convert all files in this folder from English to the new language.
                                    ",
                                ]]);
        return view('language.create');
    }

    /**
     * Edit a payment-types.
     *
     * @param int $id The ID of the payment-types to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) : View {

        $language = Language::find($id);

        return view('language.edit', compact('language'));
    }
    /**
     * Return JsonResponse
     * */
    public function store(LanguageRequest $request) : JsonResponse {

        $filename = null;

        // Get the validated data from the PaymentTypesRequest
        $validatedData = $request->validated();

        // Create a new tax record using Eloquent and save it
        $language = Language::create($validatedData);

        return response()->json([
            'status'  => true,
            'message' => __('app.record_saved_successfully'),
            'data'  => [
                'id' => $language->id,
                'name' => $language->name,
            ]
        ]);
    }

    /**
     * Return JsonResponse
     * */
    public function update(LanguageRequest $request) : JsonResponse {
        $validatedData = $request->validated();

        // Save the tax details
        Language::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function list() : View {
        return view('language.list');
    }

    public function datatableList(Request $request){
        
        $data = Language::query();

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                            $editUrl = route('language.edit', ['id' => $id]);
                            $deleteUrl = route('language.delete', ['id' => $id]);


                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                </li>';

                            $actionBtn .= (!in_array($id, $this->excludedIdsToDelete)) ? '<li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                                </li>' : '';

                            $actionBtn .='</ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function delete(Request $request) : JsonResponse{

        $selectedRecordIds = $request->input('record_ids');

        /**
         * Filter Restricted Language' Id's
         * */
        $selectedRecordIds = array_diff($selectedRecordIds, $this->excludedIdsToDelete);

        if(empty($selectedRecordIds)){
            return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record'),
                ],409);
        }

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {

            $record = Language::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id',['record_id' => $recordId]),
                ]);

            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */
        try{
            Language::whereIn('id', $selectedRecordIds)->delete();
        }catch (QueryException $e){
            return response()->json(['message' => __('app.cannot_delete_records')], 422);
        }


        return response()->json([
            'status'    => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

    public function switchTheme(string $themeMode = 'light-theme')
    {
        // Validate theme mode
        if (!in_array($themeMode, ['dark-theme', 'light-theme'])) {
            throw new InvalidArgumentException("Invalid theme mode: $themeMode");
        }

        // Set the cookie with the theme mode
        Cookie::queue('theme_mode', $themeMode, 60 * 60 * 24 * 30); // Expires in 30 days

        return response()->json([
            'status'    => true,
            'message' => "Theme Set",
            'theme' => $themeMode,
        ]);
    }

    public function setLanguageCookie(array $languageData)
    {
        // Encode array to json  
        $jsonEncoded = json_encode($languageData);

        // Set cookie with json data
        Cookie::queue('language_data', $jsonEncoded, 60 * 60 * 24 * 30);

        //Set Cookie
        App::setLocale($languageData['language_code']);
    }

    public function switchLanguage($id)
    {
        $language = Language::find($id);
        if($language){
            
            $languageData = [
                          'language_code' => $language->code,
                          'language_flag' => $language->emoji,
                          'direction' => $language->direction,
                          'emoji' => $language->emoji,
                        ];

            $this->setLanguageCookie($languageData);

            return redirect()->back();
        }
    }

    public function setDefaultLanguage(){
        $languageData = [
                          'language_code' => config('app.locales'),
                          'language_flag' => 'flag-icon-us',//It's a Emoji Code, which shows Flag on browser
                          'direction' => 'ltr',
                          'emoji' => 'flag-icon-us',
                        ];
        $this->setLanguageCookie($languageData);
    }
}