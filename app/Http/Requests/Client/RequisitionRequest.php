<?php

namespace App\Http\Requests\Client;

use App\Enums\PrPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequisitionRequest extends FormRequest
{
    /** Who may do what is enforced in the controller through policies. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'description' => ['nullable', 'string', 'max:5000'],
            'needed_by_date' => ['nullable', 'date', 'after_or_equal:today'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'priority' => ['required', Rule::enum(PrPriority::class)],

            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.specifications' => ['nullable', 'string', 'max:2000'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.estimated_unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one item to your request.',
            'items.min' => 'Add at least one item to your request.',
            'items.*.description.required' => 'Each item needs a description.',
            'items.*.quantity.required' => 'Each item needs a quantity.',
            'items.*.quantity.gt' => 'Quantity must be greater than zero.',
            'needed_by_date.after_or_equal' => 'The needed-by date cannot be in the past.',
        ];
    }
}
