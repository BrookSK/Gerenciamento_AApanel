<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $plan ? 'Editar plano' : 'Novo plano'; ?></title>
</head>
<body>
    <h1><?php echo $plan ? 'Editar plano' : 'Novo plano'; ?></h1>

    <div>
        <a href="/plans">Voltar</a>
    </div>

    <form method="post" action="<?php echo $plan ? '/plans/update' : '/plans/store'; ?>">
        <?php if ($plan) : ?>
            <input type="hidden" name="id" value="<?php echo (int)$plan['id']; ?>">
        <?php endif; ?>

        <div>
            <label>Nome</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars((string)($plan['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Descrição</label>
            <input type="text" name="description" value="<?php echo htmlspecialchars((string)($plan['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Preço</label>
            <input type="text" name="price" value="<?php echo htmlspecialchars((string)($plan['price'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Ciclo</label>
            <select name="billing_cycle">
                <option value="monthly" <?php echo (($plan['billing_cycle'] ?? 'monthly') === 'monthly') ? 'selected' : ''; ?>>Mensal</option>
                <option value="yearly" <?php echo (($plan['billing_cycle'] ?? '') === 'yearly') ? 'selected' : ''; ?>>Anual</option>
            </select>
        </div>

        <div>
            <label>Emails máximo</label>
            <input type="number" name="emails_max" value="<?php echo (int)($plan['emails_max'] ?? 0); ?>">
        </div>

        <div>
            <label>Sites WordPress máximo</label>
            <input type="number" name="wordpress_sites_max" value="<?php echo (int)($plan['wordpress_sites_max'] ?? 0); ?>">
        </div>

        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" <?php echo (($plan['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Ativo</option>
                <option value="inactive" <?php echo (($plan['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inativo</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
