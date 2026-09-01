<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01|max:9999999999.99',
        ];
    }
}
