<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const API_URL = 'http://localhost:8000/api'

const activeTab = ref<'customers' | 'services' | 'contracts'>('customers')
const loading = ref(false)
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)

const customers = ref<any[]>([])
const services = ref<any[]>([])
const contracts = ref<any[]>([])

const isEditingCustomer = ref(false)
const editingCustomerId = ref<number | null>(null)
const customerForm = ref({ name: '', document: '', email: '', status: 'Active' })

const isEditingService = ref(false)
const editingServiceId = ref<number | null>(null)
const serviceForm = ref({ name: '', default_price: null })

const contractForm = ref({ customer_id: '', start_date: '', status: 'Active' })
const selectedContractForItems = ref<any>(null)
const itemForm = ref({ service_id: '', quantity: 1, unit_price: 0 })

const clearAlerts = () => {
  errorMessage.value = null
  successMessage.value = null
}

const translateStatus = (status: string) => {
  if (!status) return ''
  const s = status.toLowerCase()
  if (s === 'active' || s === 'ativo') return 'Ativo'
  if (s === 'inactive' || s === 'inativo') return 'Inativo'
  if (s === 'canceled' || s === 'cancelado') return 'Cancelado'
  return status
}

const fetchCustomers = async () => {
  try {
    const res = await axios.get(`${API_URL}/customers`)
    customers.value = res.data.data || res.data
  } catch (err: any) { console.error(err) }
}

const saveCustomer = async () => {
  clearAlerts()

  if (!customerForm.value.name || !customerForm.value.name.trim()) {
    errorMessage.value = 'Por favor, preencha o nome do cliente.'
    return
  }
  if (!customerForm.value.document || !customerForm.value.document.trim()) {
    errorMessage.value = 'Por favor, preencha o CPF ou CNPJ.'
    return
  }
  if (!customerForm.value.email || !customerForm.value.email.trim()) {
    errorMessage.value = 'Por favor, preencha o e-mail do cliente.'
    return
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(customerForm.value.email)) {
    errorMessage.value = 'Por favor, insira um endereço de e-mail válido.'
    return
  }

  try {
    if (isEditingCustomer.value && editingCustomerId.value) {
      await axios.put(`${API_URL}/customers/${editingCustomerId.value}`, customerForm.value)
      successMessage.value = 'Cliente atualizado com sucesso!'
    } else {
      await axios.post(`${API_URL}/customers`, customerForm.value)
      successMessage.value = 'Cliente cadastrado com sucesso!'
    }
    cancelCustomerEdit()
    fetchCustomers()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao salvar cliente.'
  }
}

const editCustomer = (c: any) => {
  isEditingCustomer.value = true
  editingCustomerId.value = c.id
  customerForm.value = { name: c.name, document: c.document, email: c.email, status: c.status }
}

const cancelCustomerEdit = () => {
  isEditingCustomer.value = false
  editingCustomerId.value = null
  customerForm.value = { name: '', document: '', email: '', status: 'Active' }
}

const deleteCustomer = async (id: number) => {
  if (!confirm('Deseja realmente excluir este cliente?')) return
  try {
    clearAlerts()
    await axios.delete(`${API_URL}/customers/${id}`)
    successMessage.value = 'Cliente removido com sucesso!'
    fetchCustomers()
    fetchContracts()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao excluir cliente. Verifique se possui contratos associados.'
  }
}

const fetchServices = async () => {
  try {
    const res = await axios.get(`${API_URL}/services`)
    services.value = res.data.data || res.data
  } catch (err: any) { console.error(err) }
}

const saveService = async () => {
  clearAlerts()

  if (!serviceForm.value.name || !serviceForm.value.name.trim()) {
    errorMessage.value = 'Por favor, preencha o nome do serviço.'
    return
  }
  if (serviceForm.value.default_price === undefined || serviceForm.value.default_price === null || serviceForm.value.default_price <= 0) {
    errorMessage.value = 'O preço base mensal deve ser um valor maior que zero.'
    return
  }

  try {
    if (isEditingService.value && editingServiceId.value) {
      await axios.put(`${API_URL}/services/${editingServiceId.value}`, serviceForm.value)
      successMessage.value = 'Serviço atualizado com sucesso!'
    } else {
      await axios.post(`${API_URL}/services`, serviceForm.value)
      successMessage.value = 'Serviço cadastrado com sucesso!'
    }
    cancelServiceEdit()
    fetchServices()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao salvar serviço.'
  }
}

const editService = (s: any) => {
  isEditingService.value = true
  editingServiceId.value = s.id
  serviceForm.value = { name: s.name, default_price: s.default_price }
}

const cancelServiceEdit = () => {
  isEditingService.value = false
  editingServiceId.value = null
  serviceForm.value = { name: '', default_price: 0 }
}

const deleteService = async (id: number) => {
  if (!confirm('Deseja realmente excluir este serviço?')) return
  try {
    clearAlerts()
    await axios.delete(`${API_URL}/services/${id}`)
    successMessage.value = 'Serviço removido com sucesso!'
    fetchServices()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao excluir serviço.'
  }
}

const fetchContracts = async () => {
  try {
    loading.value = true
    const res = await axios.get(`${API_URL}/contracts`)
    contracts.value = res.data.data || res.data
  } catch (err: any) {
    errorMessage.value = 'Erro ao carregar contratos do backend.'
  } finally {
    loading.value = false
  }
}

const saveContract = async () => {
  clearAlerts()

  if (!contractForm.value.customer_id) {
    errorMessage.value = 'Por favor, selecione um cliente para abrir o contrato.'
    return
  }
  if (!contractForm.value.start_date) {
    errorMessage.value = 'Por favor, defina a data de início do contrato.'
    return
  }

  try {
    await axios.post(`${API_URL}/contracts`, contractForm.value)
    successMessage.value = 'Contrato aberto com sucesso!'
    contractForm.value = { customer_id: '', start_date: '', status: 'Active' }
    fetchContracts()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao abrir contrato.'
  }
}

const deleteContract = async (id: number) => {
  if (!confirm('Deseja realmente remover este contrato?')) return
  try {
    clearAlerts()
    await axios.delete(`${API_URL}/contracts/${id}`)
    successMessage.value = 'Contrato removido do sistema!'
    fetchContracts()
    if (selectedContractForItems.value?.id === id) selectedContractForItems.value = null
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao excluir contrato.'
  }
}

const openItemsManager = (contract: any) => {
  selectedContractForItems.value = contract
  itemForm.value = { service_id: '', quantity: 1, unit_price: 0 }
}

const updateUnitPrice = () => {
  const service = services.value.find(s => s.id === parseInt(itemForm.value.service_id))
  if (service) {
    itemForm.value.unit_price = service.default_price
  }
}

const addServiceToContract = async () => {
  clearAlerts()

  if (!itemForm.value.service_id) {
    errorMessage.value = 'Por favor, escolha um serviço para vincular.'
    return
  }
  if (!itemForm.value.quantity || itemForm.value.quantity < 1) {
    errorMessage.value = 'A quantidade do serviço deve ser de no mínimo 1.'
    return
  }
  if (itemForm.value.unit_price === undefined || itemForm.value.unit_price === null || itemForm.value.unit_price < 0) {
    errorMessage.value = 'O preço unitário não pode ser um valor negativo.'
    return
  }

  try {
    await axios.post(`${API_URL}/contracts/${selectedContractForItems.value.id}/items`, itemForm.value)
    successMessage.value = 'Serviço adicionado ao contrato!'
    
    await fetchContracts()
    selectedContractForItems.value = contracts.value.find(c => c.id === selectedContractForItems.value.id)
    itemForm.value = { service_id: '', quantity: 1, unit_price: 0 }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao vincular serviço.'
  }
}

const removeServiceFromContract = async (serviceId: number) => {
  if (!confirm('Deseja remover este serviço do contrato?')) return
  try {
    clearAlerts()
    await axios.delete(`${API_URL}/contracts/${selectedContractForItems.value.id}/items/${serviceId}`)
    successMessage.value = 'Serviço removido do contrato!'
    
    await fetchContracts()
    selectedContractForItems.value = contracts.value.find(c => c.id === selectedContractForItems.value.id)
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao remover serviço.'
  }
}

onMounted(() => {
  fetchCustomers()
  fetchServices()
  fetchContracts()
})
</script>

<template>
  <div class="app-container">
    <header>
      <h1>ERP Simplificado — Gestão de Contratos</h1>
      <div class="tabs">
        <button :class="{ active: activeTab === 'customers' }" @click="activeTab = 'customers'">Clientes</button>
        <button :class="{ active: activeTab === 'services' }" @click="activeTab = 'services'">Serviços</button>
        <button :class="{ active: activeTab === 'contracts' }" @click="activeTab = 'contracts'">Contratos</button>
      </div>
    </header>

    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    <div v-if="successMessage" class="alert alert-success" @click="successMessage = null">{{ successMessage }} (Clique para fechar)</div>

    <main>
      <section v-if="activeTab === 'customers'">
        <h2>{{ isEditingCustomer ? '✏️ Editar Cliente' : '👥 Cadastro de Clientes' }}</h2>
        <form @submit.prevent="saveCustomer" class="form-grid" novalidate>
          <input v-model="customerForm.name" placeholder="Nome do Cliente" />
          <input v-model="customerForm.document" placeholder="CPF ou CNPJ" />
          <input v-model="customerForm.email" placeholder="E-mail" />
          <select v-model="customerForm.status">
            <option value="Active">Ativo</option>
            <option value="Inactive">Inativo</option>
          </select>
          <button type="submit" class="btn-primary">{{ isEditingCustomer ? 'Atualizar' : 'Salvar Cliente' }}</button>
          <button v-if="isEditingCustomer" type="button" @click="cancelCustomerEdit" class="btn-secondary">Cancelar</button>
        </form>

        <h3>Clientes Cadastrados</h3>
        <table>
          <thead>
            <tr><th>ID</th><th>Nome</th><th>Documento</th><th>E-mail</th><th>Status</th><th>Ações</th></tr>
          </thead>
          <tbody>
            <tr v-for="c in customers" :key="c.id">
              <td>{{ c.id }}</td>
              <td>{{ c.name }}</td>
              <td>{{ c.document }}</td>
              <td>{{ c.email }}</td>
              <td>{{ translateStatus(c.status) }}</td>
              <td>
                <button @click="editCustomer(c)" class="btn-success-sm" style="margin-right: 6px;">Editar</button>
                <button @click="deleteCustomer(c.id)" class="btn-danger-sm">Excluir</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section v-if="activeTab === 'services'">
        <h2>{{ isEditingService ? '✏️ Editar Serviço' : '💼 Cadastro de Serviços' }}</h2>
        <form @submit.prevent="saveService" class="form-grid" novalidate>
          <input v-model="serviceForm.name" placeholder="Nome do Serviço" />
          <input v-model.number="serviceForm.default_price" type="number" step="10" placeholder="Preço Base Mensal" />
          <button type="submit" class="btn-primary">{{ isEditingService ? 'Atualizar' : 'Salvar Serviço' }}</button>
          <button v-if="isEditingService" type="button" @click="cancelServiceEdit" class="btn-secondary">Cancelar</button>
        </form>

        <h3>Serviços Disponíveis</h3>
        <table>
          <thead>
            <tr><th>ID</th><th>Nome do Serviço</th><th>Preço Base</th><th>Ações</th></tr>
          </thead>
          <tbody>
            <tr v-for="s in services" :key="s.id">
              <td>{{ s.id }}</td>
              <td>{{ s.name }}</td>
              <td>R$ {{ parseFloat(s.default_price).toFixed(2) }}</td>
              <td>
                <button @click="editService(s)" class="btn-success-sm" style="margin-right: 6px;">Editar</button>
                <button @click="deleteService(s.id)" class="btn-danger-sm">Excluir</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section v-if="activeTab === 'contracts'">
        <h2>📄 Abertura de Contratos</h2>
        <form @submit.prevent="saveContract" class="form-grid" novalidate>
          <select v-model="contractForm.customer_id">
            <option value="">Selecione um Cliente...</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <input v-model="contractForm.start_date" type="date" />
          <button type="submit" class="btn-primary">Criar Contrato</button>
        </form>

        <h3>Listagem de Contratos</h3>
        <div v-if="loading">Processando dados da Service Laravel...</div>
        <div v-else-if="contracts.length === 0" style="color: #64748b; font-style: italic;">Nenhum contrato cadastrado.</div>
        
        <div v-else class="contract-card" v-for="c in contracts" :key="c.id">
          <div class="contract-header">
            <div>
              <strong>Contrato #{{ c.id }} — Cliente: {{ c.customer?.name || 'Não Identificado' }}</strong>
              <span class="badge" :class="c.status?.toLowerCase()">{{ translateStatus(c.status) }}</span>
              <div style="font-size: 0.85rem; color: #64748b; margin-top: 3px;">Início: {{ c.start_date }}</div>
            </div>
            <div class="total-highlight">Total: R$ {{ (parseFloat(c.total_calculated) || 0).toFixed(2) }}</div>
          </div>
          
          <div class="contract-services-preview" v-if="c.items && c.items.length > 0">
            <h5>Serviços Ativos:</h5>
            <ul>
              <li v-for="item in c.items" :key="item.id">
                <strong>{{ item.service?.name || 'Serviço Excluído' }}</strong> — 
                {{ item.quantity }}x de R$ {{ parseFloat(item.unit_price).toFixed(2) }} 
                <span style="color: #2563eb; font-weight: 500;">
                  (Subtotal Líquido: R$ {{ (parseFloat(item.subtotal) || 0).toFixed(2) }})
                </span>
                <span v-if="item.quantity >= 10" class="discount-badge">✓ 10% Desconto Aplicado no Service</span>
              </li>
            </ul>
          </div>
          <div class="no-services-warn">Nenhum serviço atrelado a este contrato ainda.</div>
          
          <div class="contract-actions" style="margin-top: 12px; display: flex; gap: 8px;">
            <button @click="openItemsManager(c)" class="btn-secondary">Gerenciar Serviços Vinculados</button>
            <button @click="deleteContract(c.id)" class="btn-danger-sm" style="padding: 0.5rem 1rem;">Excluir Contrato</button>
          </div>
        </div>

        <div v-if="selectedContractForItems" class="modal-overlay">
          <div class="modal-content">
            <h3>Gerenciar Itens do Contrato #{{ selectedContractForItems.id }}</h3>
            <p><strong>Status Atual:</strong> {{ translateStatus(selectedContractForItems.status) }}</p>
            
            <form @submit.prevent="addServiceToContract" class="form-grid" v-if="selectedContractForItems.status !== 'Cancelado' && selectedContractForItems.status !== 'Canceled'" novalidate>
              <select v-model="itemForm.service_id" @change="updateUnitPrice">
                <option value="">Escolha um Serviço...</option>
                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <input v-model.number="itemForm.quantity" type="number" placeholder="Qtd" />
              <input v-model.number="itemForm.unit_price" type="number" step="0.01" placeholder="Preço Unitário" />
              <button type="submit" class="btn-success">Vincular</button>
            </form>
            <p v-else class="text-danger">⚠️ Contratos fechados ou cancelados não permitem modificações.</p>

            <h4>Itens Vinculados:</h4>
            <table>
              <thead>
                <tr><th>Serviço</th><th>Qtd</th><th>Unitário</th><th>Subtotal (Back)</th><th>Ação</th></tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedContractForItems.items" :key="item.id">
                  <td>{{ item.service?.name || 'Serviço Desconhecido' }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>R$ {{ parseFloat(item.unit_price).toFixed(2) }}</td>
                  <td style="font-weight: bold; color: #16a34a;">R$ {{ (parseFloat(item.subtotal) || 0).toFixed(2) }}</td>
                  <td>
                    <button type="button" @click="removeServiceFromContract(item.service_id)" class="btn-danger-sm" :disabled="selectedContractForItems.status === 'Cancelado' || selectedContractForItems.status === 'Canceled'">Remover</button>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <div style="text-align: right; font-weight: bold; font-size: 1.2rem; color: #1e3a8a; margin-top: 10px;">
              Total do Contrato: R$ {{ (parseFloat(selectedContractForItems.total_calculated) || 0).toFixed(2) }}
            </div>
            
            <button type="button" @click="selectedContractForItems = null" class="btn-close">Fechar Gerenciador</button>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.app-container { font-family: system-ui, sans-serif; padding: 2rem; max-width: 1000px; margin: 0 auto; color: #333; }
header { border-bottom: 2px solid #e2e8f0; padding-bottom: 1rem; margin-bottom: 2rem; }
.tabs { display: flex; gap: 1rem; margin-top: 1rem; }
.tabs button { padding: 0.5rem 1rem; border: none; background: #e2e8f0; cursor: pointer; font-weight: bold; border-radius: 4px; }
.tabs button.active { background: #2563eb; color: white; }
.form-grid { display: flex; gap: 0.5rem; margin-bottom: 2rem; flex-wrap: wrap; align-items: center; }
input, select { padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 150px; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; margin-bottom: 2rem; }
th, td { border-bottom: 1px solid #e2e8f0; padding: 0.75rem; text-align: left; }
th { background: #f8fafc; color: #475569; }
.btn-primary { background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-weight: 500; }
.btn-secondary { background: #475569; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
.btn-success { background: #16a34a; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
.btn-success-sm { background: #16a34a; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
.btn-danger-sm { background: #dc2626; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
.btn-danger-sm:disabled { background: #cbd5e1; cursor: not-allowed; }
.alert { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; font-weight: bold; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; cursor: pointer; }
.contract-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.2rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.contract-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px; margin-bottom: 10px; }
.total-highlight { font-size: 1.3rem; font-weight: bold; color: #1e3a8a; }
.contract-services-preview h5 { margin: 8px 0 6px 0; color: #475569; font-size: 0.95rem; }
.contract-services-preview ul { margin: 0; padding-left: 20px; font-size: 0.9rem; color: #334155; line-height: 1.4rem; }
.discount-badge { background: #dcfce7; color: #15803d; font-size: 0.75rem; font-weight: bold; padding: 1px 6px; border-radius: 4px; margin-left: 6px; }
.no-services-warn { font-size: 0.85rem; color: #94a3b8; font-style: italic; }
.badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; margin-left: 0.5rem; text-transform: uppercase; }
.badge.ativo, .badge.active { background: #dcfce7; color: #166534; }
.badge.inativo, .badge.inactive { background: #f1f5f9; color: #475569; }
.badge.cancelado, .badge.canceled { background: #fee2e2; color: #991b1b; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 999; }
.modal-content { background: white; padding: 2rem; border-radius: 8px; max-width: 650px; width: 100%; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow-y: auto; max-height: 85vh; }
.btn-close { width: 100%; background: #64748b; color: white; border: none; padding: 0.5rem; border-radius: 4px; cursor: pointer; margin-top: 1rem; font-weight: bold; }
.text-danger { color: #dc2626; font-weight: bold; }
</style>