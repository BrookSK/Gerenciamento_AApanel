<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meus E-mails</title>
</head>
<body>
    <h1>Meus E-mails</h1>

    <div>
        <a href="/portal">Voltar</a>
        <a href="/portal/emails/create">Criar e-mail</a>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>E-mail</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($emails as $e) : ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)($e['resource_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($e['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="post" action="/portal/emails/delete" style="display:inline;">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars((string)($e['resource_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit">Excluir</button>
                        </form>

                        <form method="post" action="/portal/emails/password" style="display:inline;">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars((string)($e['resource_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="password" name="password" placeholder="Nova senha" required>
                            <button type="submit">Alterar senha</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
