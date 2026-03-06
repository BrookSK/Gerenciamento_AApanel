<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novo site (aaPanel)</title>
</head>
<body>
    <h1>Novo site (aaPanel)</h1>

    <div>
        <a href="/aapanel-sites">Voltar</a>
    </div>

    <?php if (!empty($error)) : ?>
        <div style="color: #b00020;">
            <?php echo htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/aapanel-sites/store">
        <div>
            <label>Assinatura (opcional)</label>
            <select name="subscription_id">
                <option value="">Sem vínculo</option>
                <?php foreach (($subscriptions ?? []) as $sub) : ?>
                    <option value="<?php echo (int)$sub['id']; ?>">
                        <?php echo htmlspecialchars((string)$sub['client_name'], ENT_QUOTES, 'UTF-8'); ?>
                        #<?php echo (int)$sub['id']; ?>
                        - <?php echo htmlspecialchars((string)$sub['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Domínio</label>
            <input type="text" name="domain" required placeholder="ex: cliente.com.br">
        </div>

        <div>
            <label>Path (opcional)</label>
            <input type="text" name="path" placeholder="ex: /www/wwwroot/cliente.com.br">
        </div>

        <div>
            <label>Versão do PHP (opcional)</label>
            <input type="text" name="php_version" placeholder="ex: 74">
        </div>

        <div>
            <label>
                <input type="checkbox" name="install_wp" value="1">
                Instalar WordPress (requer selecionar assinatura)
            </label>
        </div>

        <button type="submit">Criar</button>
    </form>
</body>
</html>
