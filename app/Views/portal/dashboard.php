<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal do Cliente</title>
</head>
<body>
    <h1>Olá, <?php echo htmlspecialchars((string)$client['name'], ENT_QUOTES, 'UTF-8'); ?></h1>

    <form method="post" action="/portal/logout">
        <button type="submit">Sair</button>
    </form>

    <div>
        <a href="/portal/emails">Meus e-mails</a>
        <a href="/portal/plans">Planos / Upgrade</a>
        <a href="/portal/tickets">Chamados</a>
    </div>

    <h2>Assinaturas</h2>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Título</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Próx. venc.</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $s) : ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$s['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['plan_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['next_due_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div>
        Em breve: renovar, upgrade, cancelamento, e gerenciamento de emails.
    </div>
</body>
</html>
