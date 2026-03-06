<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-mails (aaPanel)</title>
</head>
<body>
    <h1>E-mails (aaPanel)</h1>

    <div>
        <a href="/aapanel-emails/create">Criar e-mail</a>
    </div>

    <?php if (!empty($error)) : ?>
        <div style="color: #b00020;">
            <?php echo htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>E-mail</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($emails ?? []) as $e) : ?>
                <?php
                $email = (string)($e['username'] ?? ($e['name'] ?? ($e['email'] ?? ($e['address'] ?? ''))));
                $status = (string)($e['status'] ?? ($e['active'] ?? ($e['state'] ?? '')));
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="post" action="/aapanel-emails/delete" style="display:inline;">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit">Excluir</button>
                        </form>

                        <form method="post" action="/aapanel-emails/password" style="display:inline;">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
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
