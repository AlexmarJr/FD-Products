<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Product;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        $uniqueNameRule = function ($attribute, $value, $fail) use ($product) {
            $query = Product::where('user_id', Auth::id())
                ->whereRaw('LOWER(name) = LOWER(?)', [$value]);

            if ($product) {
                $query->where('id', '!=', $product->id);
            }

            if ($query->exists()) {
                $fail('Já existe um produto com este nome.');
            }
        };
        return [
            'name'        => ['required', 'string', 'max:255', $uniqueNameRule],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'string', 'max:100'],
            'quantity'    => ['required', 'integer', 'gt:0'],
            'cost_price'  => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'sale_price'  => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'supplier'    => ['nullable', 'string', 'max:255'],
        ];
    }
}