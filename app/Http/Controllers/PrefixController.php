<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\App;
use App\Models\Prefix;

class PrefixController extends Controller
{
    protected $companyId;

    public function __construct()
    {
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
    }

    public function update(Request $request) : JsonResponse{
        // Save the application settings
        $settings = Prefix::findOrNew($this->companyId);
        $settings->company_id = $this->companyId;
        //$settings->order = $request->order;
        //$settings->job_code = $request->job_code;
        $settings->expense = $request->expense;
        $settings->purchase_order = $request->purchase_order;
        $settings->purchase_bill = $request->purchase_bill;
        $settings->purchase_return = $request->purchase_return;
        $settings->sale_order = $request->sale_order;
        $settings->sale = $request->sale;
        $settings->sale_return = $request->sale_return;
        $settings->stock_transfer = $request->stock_transfer;
        $settings->stock_adjustment = $request->stock_adjustment;
        $settings->quotation = $request->quotation;

        $settings->save();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
        ]);
    }
}
