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
        <a href="/">Painel</a>
        <a href="/plans/create">Novo plano</a>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Ciclo</th>
                <th>Emails max</th>
                <th>WP sites max</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $p) : ?>
                <tr>
                    <td><?php echo (int)$p['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$p['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$p['billing_cycle'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)$p['emails_max']; ?></td>
                    <td><?php echo (int)$p['wordpress_sites_max']; ?></td>
                    <td><?php echo htmlspecialchars((string)$p['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="/plans/edit?id=<?php echo (int)$p['id']; ?>">Editar</a>
                        <form method="post" action="/plans/delete" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
