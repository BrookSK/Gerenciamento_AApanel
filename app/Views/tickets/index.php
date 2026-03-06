<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chamados</title>
</head>
<body>
    <h1>Chamados</h1>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Assunto</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Data</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['items'] as $t) : ?>
                <tr>
                    <td><?php echo (int)$t['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$t['client_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$t['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$t['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$t['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$t['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><a href="/tickets/view?id=<?php echo (int)$t['id']; ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
