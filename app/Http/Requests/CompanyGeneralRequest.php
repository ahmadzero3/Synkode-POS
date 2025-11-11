<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyGeneralRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number_precision'       => ['required', 'numeric'],
            'quantity_precision'     => ['required', 'numeric'],
            'enable_minimum_stock_qty' => ['nullable'],
            'minimum_stock_qty'        => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
