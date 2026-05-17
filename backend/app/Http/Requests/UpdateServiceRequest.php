<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')?->id;

        return [
            'name' => 'sometimes|string|max:255|unique:services,name,' . $serviceId,
            'default_price' => 'sometimes|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Este nome de serviço já está em uso.',
            'default_price.numeric' => 'O preço base mensal deve ser um valor numérico.',
        ];
    }
}