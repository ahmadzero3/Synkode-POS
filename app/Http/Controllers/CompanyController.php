<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\App;
use App\Models\Company;
use App\Models\Prefix;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\CompanyGeneralRequest;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    protected $companyId;

    public function __construct()
    {
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
    }

    public function index()
    {
        $company = Company::findOrNew($this->companyId);
        $prefix = Prefix::findOrNew($this->companyId);
        return view('company.edit', compact('company', 'prefix'));
    }

    public function update(CompanyRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Save the application settings
        $settings = Company::findOrNew($this->companyId);
        $settings->name = $validatedData['name'];
        $settings->email = $validatedData['email'];
        $settings->mobile = $validatedData['mobile'];
        $settings->address = $validatedData['address'];
        $settings->state_id = $validatedData['state_id'];
        $settings->tax_number = $validatedData['tax_number'];

        if ($request->hasFile('colored_logo') && $request->file('colored_logo')->isValid()) {
            $filename = $this->uploadImage($request->file('colored_logo'));
            $settings->colored_logo = $filename;
        } else {
            $settings->colored_logo = null;
        }

        $settings->save();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function generalUpdate(CompanyGeneralRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $settings = Company::findOrNew($this->companyId);
        $settings->number_precision = $validatedData['number_precision'];
        $settings->quantity_precision = $validatedData['quantity_precision'];

        $settings->show_discount = (bool) $request->has('show_discount');
        $settings->allow_negative_stock_billing = (bool) $request->has('allow_negative_stock_billing');
        $settings->is_enable_secondary_currency = (bool) $request->has('is_enable_secondary_currency');
        $settings->is_enable_carrier_charge = (bool) $request->has('is_enable_carrier_charge');

        $settings->enable_minimum_stock_qty = $request->has('enable_minimum_stock_qty') ? 1 : 0;
        $settings->minimum_stock_qty = $request->input('minimum_stock_qty') ?? 0;

        $settings->save();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
        ]);
    }



    public function itemUpdate(Request $request): JsonResponse
    {
        // Save the company general settings
        $settings = Company::findOrNew($this->companyId);
        $settings->tax_type = $request['tax_type'];
        $settings->show_sku = $request->has('show_sku') ? 1 : 0;
        $settings->show_hsn = $request->has('show_hsn') ? 1 : 0;

        $settings->show_mrp = $request->has('show_mrp') ? 1 : 0;
        $settings->restrict_to_sell_above_mrp = $request->has('restrict_to_sell_above_mrp') ? 1 : 0;
        $settings->restrict_to_sell_below_msp = $request->has('restrict_to_sell_below_msp') ? 1 : 0;
        $settings->auto_update_sale_price = $request->has('auto_update_sale_price') ? 1 : 0;
        $settings->auto_update_purchase_price = $request->has('auto_update_purchase_price') ? 1 : 0;
        $settings->auto_update_average_purchase_price = $request->input('auto_update_average_purchase_price') == "yes" ? 1 : 0;

        $settings->is_item_name_unique = $request->has('is_item_name_unique') ? 1 : 0;
        $settings->enable_serial_tracking = $request->has('enable_serial_tracking') ? 1 : 0;
        $settings->enable_batch_tracking = $request->has('enable_batch_tracking') ? 1 : 0;
        $settings->is_batch_compulsory = $request->input('is_batch_compulsory') == "yes" ? 1 : 0;
        $settings->enable_mfg_date = $request->has('enable_mfg_date') ? 1 : 0;
        $settings->enable_exp_date = $request->has('enable_exp_date') ? 1 : 0;
        $settings->enable_color = $request->has('enable_color') ? 1 : 0;
        $settings->enable_size = $request->has('enable_size') ? 1 : 0;
        $settings->enable_model = $request->has('enable_model') ? 1 : 0;

        $settings->save();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    private function uploadSignature($image)
    {
        // Generate a unique filename for the image
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Save the image to the storage disk
        Storage::putFileAs('public/images/signature/', $image, $filename);

        return $filename;
    }

    private function uploadImage($image)
    {
        // Generate a unique filename for the image
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Save the image to the storage disk
        Storage::putFileAs('public/images/company/', $image, $filename);

        return $filename;
    }

    public function printUpdate(Request $request): JsonResponse
    {
        $settings = Company::findOrNew($this->companyId);

        $settings->show_tax_summary = $request->has('show_tax_summary') ? 1 : 0;
        $settings->show_terms_and_conditions_on_invoice = $request->has('show_terms_and_conditions_on_invoice') ? 1 : 0;
        $settings->show_signature_on_invoice = $request->has('show_signature_on_invoice') ? 1 : 0;
        $settings->show_party_due_payment = $request->has('show_party_due_payment') ? 1 : 0;
        $settings->show_brand_on_invoice = $request->has('show_brand_on_invoice') ? 1 : 0;
        $settings->show_tax_number_on_invoice = $request->has('show_tax_number_on_invoice') ? 1 : 0;

        $settings->enable_print_tax = $request->has('enable_print_tax') ? 1 : 0;
        $settings->enable_print_discount = $request->has('enable_print_discount') ? 1 : 0;

        $settings->terms_and_conditions = $request->has('terms_and_conditions')
            ? $request['terms_and_conditions']
            : null;

        if ($request->hasFile('signature') && $request->file('signature')->isValid()) {
            $filename = $this->uploadSignature($request->file('signature'));
            $settings->signature = $filename;
        }

        $settings->save();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function moduleUpdate(Request $request): JsonResponse
    {
        // Save the company print invoice settings
        $settings = Company::findOrNew($this->companyId);
        $settings->is_enable_crm = $request->has('is_enable_crm') ? 1 : 0;
        $settings->is_enable_carrier = $request->has('is_enable_carrier') ? 1 : 0;
        $settings->save();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
        ]);
    }
}
