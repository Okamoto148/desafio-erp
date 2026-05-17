<?php

namespace App\Services;

use App\Models\Customer;
use InvalidArgumentException;

class CustomerService
{
    /**
     * Cria um novo cliente aplicando validações de negócio.
     */
    public function createCustomer(array $data): Customer
    {
        $this->validateDocument($data['document'] ?? '');

        return Customer::create([
            'name' => $data['name'],
            'document' => $this->sanitize($data['document']),
            'email' => $data['email'],
            'status' => $data['status'] ?? 'Active',
        ]);
    }

    /**
     * Atualiza um cliente existente.
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        if (isset($data['document'])) {
            $this->validateDocument($data['document']);
            $data['document'] = $this->sanitize($data['document']);
        }

        $customer->update($data);
        return $customer;
    }

    /**
     * Valida de forma simplificada/robusta CPF ou CNPJ.
     */
    private function validateDocument(string $document): void
    {
        $doc = $this->sanitize($document);
        $len = strlen($doc);

        if ($len !== 11 && $len !== 14) {
            throw new InvalidArgumentException("O documento deve ser um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.");
        }
        
        // Aqui você pode expandir para algoritmos complexos de dígitos verificadores se sobrar tempo
    }

    private function sanitize(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}