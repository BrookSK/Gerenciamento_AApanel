<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planos</title>
</head>
<body>
    <h1>Planos</h1>

    <div>
        <a href="/portal">Voltar</a>
    </div>

    <?php if ((string)($_GET['error'] ?? '') === 'pending_exists') : ?>
        <div style="margin: 10px 0; padding: 10px; border: 1px solid #cc0000;">
            Já existe uma solicitação de upgrade pendente para esta assinatura.
        </div>
    <?php endif; ?>

    <h2>Minhas assinaturas</h2>

    <?php foreach ($subscriptions as $s) : ?>
        <div style="border:1px solid #ccc; padding:10px; margin: 10px 0;">
            <div><strong><?php echo htmlspecialchars((string)$s['title'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
            <div>Status: <?php echo htmlspecialchars((string)$s['status'], ENT_QUOTES, 'UTF-8'); ?></div>

            <?php $pending = $pendingBySub[(int)$s['id']] ?? []; ?>
            <?php if (count($pending) > 0) : ?>
                <div style="margin-top: 8px;">
                    <strong>Solicitações pendentes</strong>
                    <?php foreach ($pending as $pr) : ?>
                        <div>
                            #<?php echo (int)$pr['id']; ?>
                            <?php if (!empty($pr['last_asaas_status'])) : ?>
                                (ASAAS: <?php echo htmlspecialchars((string)$pr['last_asaas_status'], ENT_QUOTES, 'UTF-8'); ?>)
                            <?php endif; ?>
                            <?php if (!empty($pr['asaas_invoice_url'])) : ?>
                                <a href="<?php echo htmlspecialchars((string)$pr['asaas_invoice_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Pagar</a>
                            <?php endif; ?>

                            <form method="post" action="/portal/plans/regenerate-payment" style="display:inline;">
                                <input type="hidden" name="upgrade_request_id" value="<?php echo (int)$pr['id']; ?>">
                                <button type="submit">Regerar link</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/portal/plans/upgrade">
                <input type="hidden" name="subscription_id" value="<?php echo (int)$s['id']; ?>">
                <div>
                    <label>Solicitar upgrade para:</label>
                    <select name="target_plan_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($plans as $p) : ?>
                            <option value="<?php echo (int)$p['id']; ?>">
                                <?php echo htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8'); ?>
                                (emails: <?php echo (int)$p['emails_max']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Solicitar</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>
