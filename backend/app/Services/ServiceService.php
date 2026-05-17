<?php

namespace App\Services;

use App\Models\Service;
use InvalidArgumentException;

class ServiceService
{
    /**
     * Cria um novo serviço no sistema aplicando validações de negócio.
     */
    public function createService(array $data): Service
    {
        if (isset($data['default_price']) && $data['default_price'] < 0) {
            throw new InvalidArgumentException("O valor base mensal não pode ser negativo.");
        }

        return Service::create([
            'name' => $data['name'],
            'default_price' => $data['default_price'],
        ]);
    }

    /**
     * Atualiza um serviço existente.
     */
    public function updateService(Service $service, array $data): Service
    {
        if (isset($data['default_price']) && $data['default_price'] < 0) {
            throw new InvalidArgumentException("O valor base mensal não pode ser negativo.");
        }

        $service->update($data);
        return $service;
    }
}