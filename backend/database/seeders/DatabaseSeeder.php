<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Service;
use App\Models\Contract;
use App\Models\ContractItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::create([
            'name' => 'Cliente Teste ERP',
            'document' => '12345678901',
            'email' => 'teste@email.com',
            'is_active' => true
        ]);

        $serviceA = Service::create(['name' => 'Suporte Técnico', 'base_price' => 100.00]);
        $serviceB = Service::create(['name' => 'Manutenção Preventiva', 'base_price' => 50.00]);

        $contract = Contract::create([
            'customer_id' => $customer->id,
            'start_date' => now(),
            'status' => 'active'
        ]);

        ContractItem::create([
            'contract_id' => $contract->id,
            'service_id' => $serviceA->id,
            'quantity' => 12,
            'unit_price' => 100.00
        ]);

        ContractItem::create([
            'contract_id' => $contract->id,
            'service_id' => $serviceB->id,
            'quantity' => 2, 
            'unit_price' => 50.00
        ]);
    }
}
