# 🎂 My Birthday

Sistema de lista de presentes de aniversário, permitindo visualizar produtos disponíveis, reservar itens e gerenciar reservas.

---

## 🚀 Tecnologias

- PHP 8.3
- Laravel 13
- Livewire 4
- Flux UI
- TailwindCSS

---

## 📦 Instalação

Siga os passos abaixo para rodar o projeto localmente:

### 1. Clonar o repositório

```bash
git clone https://github.com/Welen1911/my_birthday.git
cd my_birthday
```

### 2. Instalar dependências

```bash
composer install
npm install
```
### 3.Configurar ambiente
Copie o arquivo .env:
```bash
cp .env.example .env
```
Gere a chave da aplicação:
```bash
php artisan key:generate
```
### 4.Rodar migrations
```bash
php artisan migrate
```
### 5.Rodar o projeto
```bash
php artisan serve
npm run dev
```
## ⚙️ Funcionalidades
- Listagem de produtos disponíveis
- Produtos indisponíveis (reservados)
- Reserva de presentes
- Interface dinâmica com Livewire
- UI moderna com Flux UI
## Testes
Para rodar os testes:
```bash
php artisan test
```
## 📁 Estrutura
- app/Models → Models do sistema
- app/Livewire → Componentes Livewire
- resources/views → Views Blade
- database/migrations → Estrutura do banco
## 📌 Observações
- Caso alterações no frontend não apareçam, rode:
```bash
npm run build
```
- Certifique-se de que o Node.js e o Composer estão instalados corretamente.
## 🤝 Contribuição
Pull requests são bem-vindos! Para mudanças maiores, abra uma issue primeiro.
