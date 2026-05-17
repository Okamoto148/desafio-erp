<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddContractItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'quantity'   => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Por favor, escolha um serviço para vincular.',
            'service_id.exists'   => 'O serviço selecionado não existe no sistema.',
            'quantity.required'   => 'A quantidade é obrigatória.',
            'quantity.integer'    => 'A quantidade deve ser um número inteiro.',
            'quantity.min'        => 'A quantidade mínima para o serviço é 1.',
            'unit_price.required' => 'O preço unitário é obrigatório.',
            'unit_price.numeric'  => 'O preço unitário deve ser um valor numérico.',
            'unit_price.min'      => 'O preço unitário não pode ser menor que zero.',
        ];
    }
}