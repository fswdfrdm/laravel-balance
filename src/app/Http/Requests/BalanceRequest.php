<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BalanceRequest extends FormRequest
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
            'user_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'comment' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID обязателен',
            'user_id.integer' => 'User ID должен быть числом',
            'amount.required' => 'Сумма обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Сумма должна быть не менее 0.01',
            'amount.max' => 'Сумма слишком большая',
        ];
    }
}
