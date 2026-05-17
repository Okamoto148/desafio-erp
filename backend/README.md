# Desafio ERP - Sistema de Gestão de Contratos

Este projeto consiste em um sistema simplificado de ERP voltado para a gestão de clientes, serviços e contratos. A solução foi desenvolvida utilizando uma abordagem desacoplada, com uma API RESTful no backend e uma interface SPA no frontend.

---

## Tecnologias Utilizadas

### Frontend
* **Vue.js 3:** Utilizado como framework progressivo para a construção da interface do usuário.
* **TypeScript:** Adicionado para garantir tipagem estática, reduzindo erros em tempo de desenvolvimento e melhorando a manutenibilidade do código.
* **Axios:** Cliente HTTP baseado em promessas para a realização de requisições à API do backend.
* **Vite:** Ferramenta de build rápida para o ecossistema frontend.

### Backend
* **Laravel 13 / PHP 8.x:** Framework robusto utilizado para a construção da API RESTful.
* **MySQL:** Motores de banco de dados para acesso e persistência estruturada de dados.
* **Docker:** Ambiente de desenvolvimento isolado e containerizado.

---

## Arquitetura do Sistema e Design de Código

O backend foi desenhado seguindo padrões arquiteturais que visam separar rigorosamente as responsabilidades de infraestrutura, protocolo de comunicação e regras de negócio. A estrutura afasta-se do antipadrão de controladores inflados (*Fat Controllers*) ou lógica de negócio acoplada aos modelos (*Fat Models*).

A arquitetura está dividida nas seguintes camadas:

### 1. Camada de Apresentação e Protocolo (Controllers)
Os controladores são estritamente magros (*Skinny Controllers*). Sua única responsabilidade é receber a requisição HTTP, acionar as camadas de validação e delegar a execução da lógica para a camada de serviço. O retorno é sempre padronizado em formato JSON, utilizando os códigos de status HTTP corretos (ex: 201 para criação, 422 para erros de validação, 204 para remoções bem-sucedidas).

### 2. Camada de Validação Isolada (Form Requests)
A validação de entrada de dados não polui os controladores. Foram implementadas classes dedicadas de `FormRequest` (como `StoreCustomerRequest`, `UpdateContractRequest`, entre outras).
* **Segurança na Borda:** Os dados são sanitizados e validados antes mesmo de tocarem a lógica de negócio ou o banco de dados.
* **Isolamento HTTP:** Regras de obrigatoriedade, formatos de e-mail e checagens de unicidade estrutural ficam restritas a esta camada.

### 3. Camada de Domínio e Negócio (Services)
Toda a lógica central do ERP reside em classes de Serviço (ex: `CustomerService`, `ContractService`).
* **Agnosticismo de Protocolo:** As services recebem apenas arrays de dados já validados. Olhando sob a ótica de design, elas não possuem conhecimento sobre requisições HTTP, cabeçalhos ou sessões. Isso permite que a mesma lógica de negócio seja invocada por um controlador de API, por um comando de console (CLI) ou por um consumidor de filas.
* **Centralização de Regras:** Cálculos complexos, como o subtotal líquido de itens de contrato e aplicação de regras de desconto por volume (ex: descontos automáticos para quantidades superiores a um limite), são processados exclusivamente aqui.

### 4. Camada de Persistência (Models e Eloquent ORM)
Os modelos do Eloquent atuam estritamente como a representação da estrutura de dados e mapeamento de relacionamentos (como `hasMany`, `belongsTo`). O uso de queries complexas dentro dos modelos foi evitado para manter a legibilidade.

### 5. Considerações sobre o Frontend e Abordagem Pragmática
Dado que o escopo e o critério principal de avaliação deste desafio técnico concentram-se na maturidade arquitetural e isolamento de camadas do backend, foi adotada uma abordagem pragmática no desenvolvimento da interface com Vue.js. 

Para otimizar o tempo de entrega sem comprometer a usabilidade e as validações em tempo real, optou-se pela centralização da interface em uma estrutura de componente único auto-contido. É importante ressaltar que, em cenários reais de produção e sistemas de larga escala, adota-se rigorosamente o princípio **DRY (Don't Repeat Yourself)** através da quebra da interface em componentes atômicos altamente reutilizáveis (como botões, modais, formulários e tabelas isoladas), facilitando a manutenção e a criação de testes de componentes.

---

## Motivação para a Escolha da Arquitetura

A escolha por uma arquitetura baseada em Services e Form Requests, isolada do fluxo tradicional de Views do framework, justifica-se pelos seguintes critérios de engenharia:

* **Princípio da Responsabilidade Única (SRP):** Cada classe possui apenas uma razão para mudar. Se o formato de validação de um documento mudar, altera-se o Request. Se a regra de cálculo de desconto mudar, altera-se a Service. Se o formato de resposta mudar, altera-se o Controller.
* **Testabilidade:** Como as regras de negócio estão isoladas nas Services e não dependem do ciclo de vida HTTP, a criação de testes unitários torna-se direta, rápida e dispensa o mocking complexo de requisições ou sessões de usuário.
* **Diminuição do Acoplamento:** O frontend consome a aplicação puramente como um provedor de dados. O backend pode ter sua infraestrutura alterada sem que o cliente SPA sofra impactos diretos.

---

## Engenharia de Qualidade: Escalabilidade, Manutenção e Performance

Sob a perspectiva de liderança técnica, o projeto foi concebido prevendo o crescimento da base de código e do volume de acessos.

### Manutenibilidade
A padronização dos Requests e das Services remove a ambiguidade do projeto. Desenvolvedores recém-chegados à equipe conseguem localizar regras de validação ou de negócio imediatamente, pois a estrutura de pastas reflete estritamente a função de cada componente de software. O uso de TypeScript no frontend e tipagem estática de retorno (como `JsonResponse`) no backend garante previsibilidade ao fluxo de dados.

### Escalabilidade
* **Desacoplamento Completamente Stateless:** Por operar de forma stateless, o backend pode ser escalado horizontalmente por meio de balanceadores de carga em múltiplos containers, uma vez que não depende de sessões locais em disco.
* **Prontidão para Filas:** A arquitetura em Services facilita a migração de tarefas pesadas (como geração de relatórios de contratos ou disparos de e-mails de cobrança) para processamento assíncrono via mensageria (Redis/RabbitMQ) com alterações mínimas de código.

### Performance
* **Carregamento Otimizado (Eager Loading):** No gerenciamento de contratos, o sistema mitiga o problema comum de performance conhecido como "Query N+1". Ao buscar os dados de um contrato, os relacionamentos necessários são carregados preventivamente (`$contract->load(['customer', 'items.service'])`), consolidando a busca em poucas instruções estruturadas ao banco de dados.
* **Paginação Nativa:** Endpoints que listam volumes potencialmente altos de dados, como clientes e serviços, utilizam paginação nativa na API (`paginate(10)`). Isso impede a sobrecarga de memória no servidor e reduz o payload trafegado na rede, mantendo o tempo de resposta estável independentemente do tamanho do banco de dados.
* **Validação em Dois Níveis:** O frontend realiza validações de integridade antes do disparo da requisição, poupando processamento desnecessário do servidor para erros simples de digitação ou campos vazios.

---

## Como Executar o Projeto com Docker

O ambiente de desenvolvimento foi totalmente configurado e automatizado utilizando o **Docker Compose**, gerenciando tanto os serviços de backend (Nginx, PHP-FPM e MySQL) quanto o ambiente de desenvolvimento do frontend (Node.js/Vite) em containers isolados. Isso garante a paridade absoluta do ambiente e elimina a necessidade de instalar qualquer dependência diretamente na máquina hospedeira.

### Pré-requisitos
* **Docker** e **Docker Compose** instalados e em execução.

---

### Passos para Instalação

#### 1. Clonar o repositório do projeto
```bash
git clone <url-do-repositorio>
cd desafio-erp
```

#### 2. Configurar as Variáveis de Ambiente
Crie o arquivo de configuração do backend a partir do modelo existente:
```bash
cp backend/.env.example backend/.env
```

#### 3. Inicializar os Containers
Suba toda a infraestrutura da aplicação (Backend, Banco de Dados e Frontend) com um único comando na raiz do projeto:
```bash
docker compose up -d
```
Nota: O container do frontend (erp-vue) executará automaticamente a instalação dos pacotes do Node e inicializará o servidor de desenvolvimento do Vite em background.

#### 4. Finalizar as Configurações do Backend
Com os containers operacionais, execute os comandos internos no container da aplicação PHP (erp-app) para instalar as dependências e preparar o banco de dados:
```bash
docker exec -it erp-app composer install
docker exec -it erp-app php artisan key:generate
docker exec -it erp-app php artisan migrate --seed
```
---

### Links de Acesso

* **Interface Frontend (Vue 3 / Vite):** [http://localhost:5173](http://localhost:5173)
* **API Backend (Nginx / Laravel):** [http://localhost:8000](http://localhost:8000)

---
