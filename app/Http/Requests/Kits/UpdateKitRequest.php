<?php

namespace App\Http\Requests\Kits;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKitRequest extends FormRequest
{
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
        return [
            'name' => ['required', 'string', 'max:255', 'unique:kits,name,' . $this->route('kit')->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.location_id' => ['nullable', 'exists:locations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Name'),
            'description' => __('Description'),
            'is_active' => __('Status'),
            'items' => __('Kit Items'),
            'items.*.product_id' => __('Product'),
            'items.*.location_id' => __('Location'),
            'items.*.quantity' => __('Quantity'),
            'items.*.notes' => __('Notes'),
        ];
    }
}
