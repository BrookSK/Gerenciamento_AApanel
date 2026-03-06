<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $server ? 'Editar servidor' : 'Novo servidor'; ?></title>
</head>
<body>
    <h1><?php echo $server ? 'Editar servidor' : 'Novo servidor'; ?></h1>

    <div>
        <a href="/aapanel-servers">Voltar</a>
    </div>

    <form method="post" action="<?php echo $server ? '/aapanel-servers/update' : '/aapanel-servers/store'; ?>">
        <?php if ($server) : ?>
            <input type="hidden" name="id" value="<?php echo (int)$server['id']; ?>">
        <?php endif; ?>

        <div>
            <label>Nome</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars((string)($server['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Base URL</label>
            <input type="text" name="base_url" required placeholder="http://seu-servidor:7800" value="<?php echo htmlspecialchars((string)($server['base_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>API Key</label>
            <input type="text" name="api_key" required value="<?php echo htmlspecialchars((string)($server['api_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" <?php echo (($server['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Ativo</option>
                <option value="inactive" <?php echo (($server['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inativo</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
