<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $uniqueRule = Rule::unique('products')
            ->where(function ($query) {
                return $query
                    ->where('user_id', Auth::id())
                    ->whereRaw('LOWER(name) = LOWER(?)', [$this->input('name')]);
            });

        if ($product = $this->route('product')) {
            $uniqueRule->ignore($product->id);
        }

        return [
            'name'        => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'string', 'max:100'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'cost_price'  => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'sale_price'  => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'supplier'    => ['nullable', 'string', 'max:255'],
        ];
    }
}