<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_paginated_customers(): void
    {
        Customer::factory()->count(15)->create();

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total'
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_can_create_customer_with_valid_data(): void
    {
        $data = [
            'name' => 'Empresa de Teste LTDA',
            'document' => '12345678000199',
            'email' => 'contato@empresateste.com',
            'status' => 'Active'
        ];

        $response = $this->postJson('/api/customers', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Empresa de Teste LTDA']);

        $this->assertDatabaseHas('customers', ['document' => '12345678000199']);
    }

    public function test_can_show_specific_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $customer->id, 'name' => $customer->name]);
    }

    public function test_can_update_customer_with_valid_data(): void
    {
        $customer = Customer::factory()->create();
        
        $data = [
            'name' => 'Nome Atualizado LTDA',
            'document' => $customer->document,
            'email' => 'novoemail@teste.com',
            'status' => 'Active'
        ];

        $response = $this->putJson("/api/customers/{$customer->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nome Atualizado LTDA']);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nome Atualizado LTDA'
        ]);
    }

    public function test_can_delete_customer_without_contracts(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_cannot_delete_customer_with_active_contracts(): void
    {
        $customer = Customer::factory()->create();
        
        Contract::factory()->create(['customer_id' => $customer->id]);

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Não é possível excluir um cliente com contratos ativos.'
            ]);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}