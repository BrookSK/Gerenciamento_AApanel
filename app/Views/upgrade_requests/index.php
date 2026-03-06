<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitações de Upgrade</title>
</head>
<body>
    <h1>Solicitações de Upgrade</h1>

    <div>
        <a href="/">Painel</a>
        <a href="/plans">Planos</a>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Assinatura</th>
                <th>Plano destino</th>
                <th>Status</th>
                <th>ASAAS</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['items'] as $row) : ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$row['client_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)$row['subscription_id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$row['target_plan_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if (!empty($row['last_asaas_status'])) : ?>
                            <?php echo htmlspecialchars((string)$row['last_asaas_status'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                        <?php if (!empty($row['amount'])) : ?>
                            - R$ <?php echo htmlspecialchars((string)$row['amount'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                        <?php if (!empty($row['asaas_invoice_url'])) : ?>
                            <a href="<?php echo htmlspecialchars((string)$row['asaas_invoice_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Link</a>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ((string)$row['status'] === 'pending') : ?>
                            <form method="post" action="/upgrade-requests/apply" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button type="submit">Aplicar</button>
                            </form>
                            <form method="post" action="/upgrade-requests/regenerate-payment" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button type="submit">Regerar pagamento</button>
                            </form>
                            <form method="post" action="/upgrade-requests/cancel" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button type="submit">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 12px;">
        <?php $pages = (int)($data['pages'] ?? 1); ?>
        <?php $page = (int)($data['page'] ?? 1); ?>
        <?php if ($page > 1) : ?>
            <a href="/upgrade-requests?page=<?php echo $page - 1; ?>">Anterior</a>
        <?php endif; ?>
        Página <?php echo $page; ?> de <?php echo $pages; ?>
        <?php if ($page < $pages) : ?>
            <a href="/upgrade-requests?page=<?php echo $page + 1; ?>">Próxima</a>
        <?php endif; ?>
    </div>
</body>
</html>
