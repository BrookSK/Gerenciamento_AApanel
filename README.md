# Gerenciamento_AApanel

Aplicação PHP (MVC) para gerenciamento de **assinaturas** e **hospedagens**, com integrações:

- ASAAS (pagamentos/links de cobrança e webhooks)
- AAPanel (provisionamento/gestão de hospedagem)
- Sistema de **Tickets (Chamados)** com mensagens e **múltiplos anexos**

## Requisitos

- PHP 8.2+
- MySQL/MariaDB
- Extensões comuns do PHP para MySQL (ex.: `pdo_mysql`)
- Servidor web (Apache/Nginx) ou PHP built-in server

## Instalação

1. Instale dependências (autoload PSR-4):

```bash
composer install
```

2. Ajuste as configurações do banco em `config/config.php`.

- **Importante**: não exponha credenciais em repositórios públicos.

3. Crie o banco de dados e execute as migrations (arquivos em `migrations/`).

Este projeto usa migrations SQL **append-only** (não edite migrations antigas; crie novas).

4. Aponte o DocumentRoot para a pasta `public/`.

- `public/index.php` é o front controller.
- Há regras de rewrite via `.htaccess` (Apache).

## Executando

- Entrada padrão: `public/index.php`
- Também existe `index.php` na raiz que redireciona para `public/index.php`.

## Configurações

### URL base

Em `config/config.php`:

- `APP_BASE_URL` (opcional) pode sobrescrever a URL base.

### Banco de dados

Em `config/config.php`:

- `db.host`
- `db.port`
- `db.database`
- `db.username`
- `db.password`

### Emails de notificação de tickets

No painel admin existe uma configuração em **Configurações** para definir os emails que receberão notificação quando um novo ticket for aberto.

- Chave: `ticket_notify_emails`
- Formato: lista separada por vírgula (ex.: `suporte@empresa.com, dev@empresa.com`)

## Sistema de Tickets (Chamados)

- Um ticket possui várias mensagens.
- Cada mensagem pode possuir **múltiplos anexos**.
- Os anexos são armazenados fora da pasta pública e baixados via controllers (download protegido).

Rotas de download de anexos:

- Portal: `/portal/tickets/attachment?id=...`
- Admin/Dev: `/tickets/attachment?id=...`

## Integração ASAAS

- Webhook ASAAS: `/webhooks/asaas`
- Upgrade de planos cria cobranças no ASAAS e registra `externalReference` para conciliação.
- Confirmações de pagamento via webhook podem aplicar upgrades automaticamente.

## CLI

Existe um script simples em `cli.php` para ajudar a criar migrations:

```bash
php cli.php make:migration nome_da_migration
```

## Estrutura (visão geral)

- `app/Controllers` Controllers (portal/admin)
- `app/Models` Models
- `app/Services` Serviços (ASAAS, tickets, storage, etc.)
- `app/Views` Views
- `app/Core` Kernel/Router/App
- `public/` Front controller e arquivos públicos
- `migrations/` Migrations SQL

## Observações

- Recomenda-se configurar o envio de email corretamente no servidor (o envio atual usa `mail()` via `MailService`).
