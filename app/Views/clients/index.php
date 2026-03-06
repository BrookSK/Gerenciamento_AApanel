<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes</title>
</head>
<body>
    <h1>Clientes</h1>

    <div>
        <a href="/clients/create">Novo cliente</a>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Email (portal)</th>
                <th>Portal</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $c) : ?>
                <tr>
                    <td><?php echo (int)$c['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($c['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($c['portal_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo ((int)($c['portal_enabled'] ?? 1) === 1) ? 'Sim' : 'Não'; ?></td>
                    <td><?php echo htmlspecialchars((string)$c['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="/clients/edit?id=<?php echo (int)$c['id']; ?>">Editar</a>
                        <form method="post" action="/clients/delete" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
