<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:building,open_field',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'area_sq_m' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'has_cctv' => 'boolean',
            'has_security_guard' => 'boolean',
            'guard_count' => 'nullable|integer|min:0',
            'has_labors' => 'boolean',
            'is_motorable' => 'boolean',
            'distance_from_city' => 'nullable|numeric|min:0',
            'camera_stream_url' => 'nullable|url',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'price_per_unit' => 'nullable|numeric|min:0',
            'price_unit_type' => 'nullable|in:fixed,percentage',
            'security_deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'security_deposit_fixed' => 'nullable|numeric|min:0',
        ];
    }
}