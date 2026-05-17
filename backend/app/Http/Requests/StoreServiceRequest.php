<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
            'default_price' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Por favor, preencha o nome do serviço.',
            'name.unique' => 'Este nome de serviço já está cadastrado no sistema.',
            'default_price.required' => 'O preço base mensal é obrigatório.',
            'default_price.numeric' => 'O preço base mensal deve ser um valor numérico.',
        ];
    }
}