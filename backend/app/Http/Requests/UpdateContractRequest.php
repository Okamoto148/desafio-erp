<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'sometimes|in:Active,Canceled',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date'           => 'Insira uma data de início válida.',
            'end_date.date'             => 'Insira uma data de término válida.',
            'end_date.after_or_equal'   => 'A data de término não pode ser anterior à data de início.',
            'status.in'                 => 'O status informado é inválido. Escolha entre Ativo ou Cancelado.',
        ];
    }
}