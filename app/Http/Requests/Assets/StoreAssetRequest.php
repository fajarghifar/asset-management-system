<?php

namespace App\Http\Requests\Assets;

use App\Enums\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'asset_tag' => ['nullable', 'string', 'max:50', 'unique:assets,asset_tag'],
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:assets,serial_number'],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'purchase_date' => ['nullable', 'date'],
            'image_path' => ['nullable', 'image', 'max:2048'], // Max 2MB
            'notes' => ['nullable', 'string'],
        ];
    }
}
