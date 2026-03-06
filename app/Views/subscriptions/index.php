<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assinaturas</title>
</head>
<body>
    <h1>Assinaturas</h1>

    <div>
        <a href="/subscriptions/create">Nova assinatura</a>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Preço</th>
                <th>Status</th>
                <th>Próx. venc.</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $s) : ?>
                <tr>
                    <td><?php echo (int)$s['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$s['client_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['plan_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['next_due_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="/subscriptions/edit?id=<?php echo (int)$s['id']; ?>">Editar</a>
                        <form method="post" action="/subscriptions/delete" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
