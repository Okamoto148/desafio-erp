<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractItem;
use InvalidArgumentException;

class ContractService
{
    /**
     * Retorna todos os contratos calculando dinamicamente os subtotais e totais
     */
    public function getAllContracts()
    {
        // Carrega os contratos trazendo os relacionamentos do banco
        $contracts = Contract::with(['customer', 'items.service'])->get();

        // Mapeia a coleção para injetar as propriedades que o Vue espera ler
        $contracts->transform(function ($contract) {
            $totalContrato = 0;

            if ($contract->items) {
                foreach ($contract->items as $item) {
                    $subtotal = $item->quantity * $item->unit_price;

                    // Aplica a regra de negócio dos 10%
                    if ($item->quantity >= 10) {
                        $subtotal = $subtotal * 0.90;
                    }

                    // Injeta dinamicamente no objeto do item para o front-end mapear
                    $item->subtotal = $subtotal;
                    $totalContrato += $subtotal;
                }
            }

            // Injeta o total geral calculado na raiz do contrato
            $contract->total_calculated = $totalContrato;
            return $contract;
        });

        return $contracts;
    }

    /**
     * Calcula o total mensal do contrato (Mantido para compatibilidade interna se necessário).
     */
    public function calculateMonthlyTotal(Contract $contract): float
    {
        return $contract->items->reduce(function ($total, $item) {
            $subtotal = $item->quantity * $item->unit_price;
            if ($item->quantity >= 10) {
                $subtotal *= 0.90;
            }
            return $total + $subtotal;
        }, 0.0);
    }

    public function createContract(array $data): Contract
    {
        return Contract::create([
            'customer_id' => $data['customer_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'Active',
        ]);
    }

    public function updateContract(Contract $contract, array $data): Contract
    {
        if ($contract->status === 'Canceled') {
            throw new InvalidArgumentException("Não é possível alterar dados de um contrato que já está Cancelado.");
        }
        $contract->update($data);
        return $contract;
    }

    public function addItem(Contract $contract, array $itemData): ContractItem
    {
        if ($contract->status === 'Canceled') {
            throw new InvalidArgumentException("Não é possível adicionar serviços a um contrato Cancelado.");
        }
        return ContractItem::updateOrCreate(
            ['contract_id' => $contract->id, 'service_id' => $itemData['service_id']],
            ['quantity' => $itemData['quantity'], 'unit_price' => $itemData['unit_price']]
        );
    }

    public function removeItem(Contract $contract, $serviceId): void
    {
        if ($contract->status === 'Canceled') {
            throw new InvalidArgumentException("Não é possível remover serviços de um contrato Cancelado.");
        }
        $contract->items()->where('service_id', $serviceId)->delete();
    }
}