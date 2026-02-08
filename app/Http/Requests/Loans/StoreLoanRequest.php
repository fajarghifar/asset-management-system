<?php

namespace App\Http\Requests\Loans;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\LoanItemType;

class StoreLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by middleware/gates
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'borrower_name' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string'],
            'proof_image' => ['nullable', 'image', 'max:2048'], // 2MB max

            // Items Validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', Rule::enum(LoanItemType::class)],
            'items.*.asset_id' => [
                'nullable',
                'required_if:items.*.type,' . LoanItemType::Asset->value,
                'exists:assets,id'
            ],
            'items.*.consumable_stock_id' => [
                'nullable',
                'required_if:items.*.type,' . LoanItemType::Consumable->value,
                'exists:consumable_stocks,id'
            ],
            'items.*.quantity_borrowed' => ['required', 'integer', 'min:1'],
        ];
    }
    public function attributes(): array
    {
        return [
            'user_id' => __('User'),
            'borrower_name' => __('Borrower Name'),
            'purpose' => __('Purpose of Loan'),
            'loan_date' => __('Loan Date'),
            'due_date' => __('Due Date'),
            'notes' => __('Notes'),
            'proof_image' => __('Proof Image'),
            'items' => __('Loan Items'),
            'items.*.type' => __('Item Type'),
            'items.*.asset_id' => __('Asset'),
            'items.*.consumable_stock_id' => __('Consumable Stock'),
            'items.*.quantity_borrowed' => __('Quantity'),
        ];
    }
}
