<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'document' => 'required|string|unique:customers,document',
            'email'    => 'required|email|unique:customers,email',
            'status'   => 'required|in:Active,Inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Por favor, preencha o nome do cliente.',
            'document.required' => 'Por favor, preencha o CPF ou CNPJ.',
            'document.unique'   => 'Este CPF ou CNPJ já está cadastrado no sistema.',
            'email.required'    => 'Por favor, preencha o e-mail do cliente.',
            'email.email'       => 'Por favor, insira um endereço de e-mail válido.',
            'email.unique'      => 'Este endereço de e-mail já está em uso.',
            'status.required'   => 'O status é obrigatório.',
        ];
    }
}