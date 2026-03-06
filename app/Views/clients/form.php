<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $client ? 'Editar cliente' : 'Novo cliente'; ?></title>
</head>
<body>
    <h1><?php echo $client ? 'Editar cliente' : 'Novo cliente'; ?></h1>

    <div>
        <a href="/clients">Voltar</a>
    </div>

    <form method="post" action="<?php echo $client ? '/clients/update' : '/clients/store'; ?>">
        <?php if ($client) : ?>
            <input type="hidden" name="id" value="<?php echo (int)$client['id']; ?>">
        <?php endif; ?>

        <div>
            <label>Nome</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars((string)($client['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars((string)($client['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <h2>Portal do Cliente</h2>

        <div>
            <label>Email do portal</label>
            <input type="email" name="portal_email" value="<?php echo htmlspecialchars((string)($client['portal_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Portal habilitado</label>
            <?php $portalEnabled = (string)($client['portal_enabled'] ?? '1'); ?>
            <select name="portal_enabled">
                <option value="1" <?php echo $portalEnabled === '1' ? 'selected' : ''; ?>>Sim</option>
                <option value="0" <?php echo $portalEnabled === '0' ? 'selected' : ''; ?>>Não</option>
            </select>
        </div>

        <div>
            <label>Senha do portal <?php echo $client ? '(deixe vazio para não alterar)' : ''; ?></label>
            <input type="password" name="portal_password" value="">
        </div>

        <div>
            <label>Documento</label>
            <input type="text" name="document" value="<?php echo htmlspecialchars((string)($client['document'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Telefone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars((string)($client['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" <?php echo (($client['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Ativo</option>
                <option value="inactive" <?php echo (($client['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inativo</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
