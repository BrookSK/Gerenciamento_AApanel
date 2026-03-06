<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurações</title>
</head>
<body>
    <h1>Configurações</h1>

    <form method="post" action="/settings/save">
        <h2>AAPanel</h2>

        <div>
            <label>Servidor padrão</label>
            <select name="aapanel_default_server_id">
                <option value="">Selecione</option>
                <?php foreach ($servers as $s) : ?>
                    <?php $sel = ((string)($settings['aapanel_default_server_id'] ?? '') === (string)$s['id']); ?>
                    <option value="<?php echo (int)$s['id']; ?>" <?php echo $sel ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string)$s['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>wwwroot base (AAPanel)</label>
            <input type="text" name="aapanel_wwwroot_base" value="<?php echo htmlspecialchars((string)($settings['aapanel_wwwroot_base'] ?? '/www/wwwroot'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <h2>WordPress</h2>

        <div>
            <label>Pasta base</label>
            <input type="text" name="wp_base_path" value="<?php echo htmlspecialchars((string)($settings['wp_base_path'] ?? '/desenvolvimento'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <h2>ASAAS</h2>

        <div>
            <label>Ambiente</label>
            <?php $env = (string)($settings['asaas_environment'] ?? 'sandbox'); ?>
            <select name="asaas_environment">
                <option value="sandbox" <?php echo $env === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                <option value="prod" <?php echo $env === 'prod' ? 'selected' : ''; ?>>Produção</option>
            </select>
        </div>

        <div>
            <label>Token (sandbox)</label>
            <input type="text" name="asaas_token_sandbox" value="<?php echo htmlspecialchars((string)($settings['asaas_token_sandbox'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Token (produção)</label>
            <input type="text" name="asaas_token_prod" value="<?php echo htmlspecialchars((string)($settings['asaas_token_prod'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Tipo de cobrança (billingType)</label>
            <?php $bt = (string)($settings['asaas_billing_type'] ?? 'PIX'); ?>
            <select name="asaas_billing_type">
                <option value="PIX" <?php echo $bt === 'PIX' ? 'selected' : ''; ?>>PIX</option>
                <option value="BOLETO" <?php echo $bt === 'BOLETO' ? 'selected' : ''; ?>>BOLETO</option>
                <option value="CREDIT_CARD" <?php echo $bt === 'CREDIT_CARD' ? 'selected' : ''; ?>>Cartão</option>
            </select>
        </div>

        <div>
            <label>Descrição do pagamento</label>
            <input type="text" name="asaas_payment_description" value="<?php echo htmlspecialchars((string)($settings['asaas_payment_description'] ?? 'Upgrade de plano'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Webhook URL (configure no ASAAS)</label>
            <input type="text" name="asaas_webhook_url" value="<?php echo htmlspecialchars((string)($settings['asaas_webhook_url'] ?? ($baseUrl . '/webhooks/asaas')), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Token de autenticação do Webhook (asaas-access-token)</label>
            <input type="text" name="asaas_webhook_access_token" value="<?php echo htmlspecialchars((string)($settings['asaas_webhook_access_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <h2>Chamados</h2>

        <div>
            <label>E-mails para notificação (separados por vírgula)</label>
            <input type="text" name="ticket_notify_emails" value="<?php echo htmlspecialchars((string)($settings['ticket_notify_emails'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
