<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AAPanel Servers</title>
</head>
<body>
    <h1>AAPanel Servers</h1>

    <div>
        <a href="/">Painel</a>
        <a href="/clients">Clientes</a>
        <a href="/subscriptions">Assinaturas</a>
        <a href="/aapanel-servers/create">Novo servidor</a>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Base URL</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servers as $s) : ?>
                <tr>
                    <td><?php echo (int)$s['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$s['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['base_url'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="/aapanel-servers/edit?id=<?php echo (int)$s['id']; ?>">Editar</a>
                        <form method="post" action="/aapanel-servers/delete" style="display:inline;">
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
