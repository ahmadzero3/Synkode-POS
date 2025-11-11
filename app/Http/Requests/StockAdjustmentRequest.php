<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FormatsDateInputs;

class StockAdjustmentRequest extends FormRequest
{

    use FormatsDateInputs;

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rulesArray = [

            'adjustment_date'        => ['required', 'date_format:'.implode(',', $this->getDateFormats())],
            'prefix_code'          => ['nullable', 'string','max:250'],
            'count_id'             => ['required', 'numeric'],
            'adjustment_code'           => ['required', 'string','max:50'],
            'reference_no'           => ['nullable', 'string','max:50'],
            'note'                 => ['nullable', 'string','max:250'],
            'row_count'            => ['required', 'integer', 'min:1'],
        ];

        //For Update Operation
        if ($this->isMethod('PUT')) {
            $rulesArray['adjustment_id']          = ['required'];
        }
        return $rulesArray;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        /**
         * @method formatDateInput
         * Defined in Trait FormatsDateInputs
         * */
        $adjustmentDate  = $this->input('adjustment_date');
        $this->merge([
            'adjustment_date' => $this->toSystemDateFormat($adjustmentDate),
            'adjustment_code' => $this->getAdjustmentCode(),
        ]);
    }

    /**
     *
     * @return string
     */
    protected function getAdjustmentCode()
    {
        $prefixCode = $this->input('prefix_code');
        $countId = $this->input('count_id');

        return $prefixCode . $countId;
    }

    public function messages(): array
    {
        $responseMessages = [
            'row_count.min'     => __('item.please_select_items'),
        ];

        if ($this->isMethod('PUT')) {
            $responseMessages['id.required']    = 'ID Not found to update record';
        }
        return $responseMessages;
    }

}
