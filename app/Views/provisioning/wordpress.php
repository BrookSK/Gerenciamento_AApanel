<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provisionar WordPress</title>
</head>
<body>
    <h1>Provisionar WordPress</h1>

    <div>
        <a href="/subscriptions/edit?id=<?php echo (int)$subscription['id']; ?>">Voltar</a>
    </div>

    <form method="post" action="/provisioning/wordpress">
        <input type="hidden" name="subscription_id" value="<?php echo (int)$subscription['id']; ?>">

        <div>
            <label>Domínio</label>
            <input type="text" name="domain" required placeholder="cliente.com.br">
        </div>

        <button type="submit">Provisionar</button>
    </form>
</body>
</html>
