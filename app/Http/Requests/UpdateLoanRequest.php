<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\LoanItemType;

class UpdateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'borrower_name' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', Rule::enum(LoanItemType::class)],
            'items.*.asset_id' => [
                'nullable',
                'required_if:items.*.type,' . LoanItemType::Asset->value,
                'exists:assets,id',
            ],
            'items.*.consumable_stock_id' => [
                'nullable',
                'required_if:items.*.type,' . LoanItemType::Consumable->value,
                'exists:consumable_stocks,id'
            ],
            'items.*.quantity_borrowed' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
