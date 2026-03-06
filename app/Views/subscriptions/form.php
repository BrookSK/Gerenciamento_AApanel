<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $subscription ? 'Editar assinatura' : 'Nova assinatura'; ?></title>
</head>
<body>
    <h1><?php echo $subscription ? 'Editar assinatura' : 'Nova assinatura'; ?></h1>

    <div>
        <a href="/subscriptions">Voltar</a>
        <?php if ($subscription) : ?>
            <a href="/provisioning/wordpress?subscription_id=<?php echo (int)$subscription['id']; ?>">Provisionar WordPress</a>
            <form method="post" action="/subscriptions/resources/suspend" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo (int)$subscription['id']; ?>">
                <button type="submit">Suspender recursos</button>
            </form>
            <form method="post" action="/subscriptions/resources/activate" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo (int)$subscription['id']; ?>">
                <button type="submit">Reativar recursos</button>
            </form>
        <?php endif; ?>
    </div>

    <form method="post" action="<?php echo $subscription ? '/subscriptions/update' : '/subscriptions/store'; ?>">
        <?php if ($subscription) : ?>
            <input type="hidden" name="id" value="<?php echo (int)$subscription['id']; ?>">
        <?php endif; ?>

        <div>
            <label>Cliente</label>
            <select name="client_id" required>
                <option value="">Selecione</option>
                <?php foreach ($clients as $c) : ?>
                    <?php $selected = $subscription && ((int)$subscription['client_id'] === (int)$c['id']); ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string)$c['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Plano</label>
            <select name="plan_id">
                <option value="">Sem plano</option>
                <?php foreach ($plans as $p) : ?>
                    <?php $selectedPlan = $subscription && ((string)($subscription['plan_id'] ?? '') === (string)$p['id']); ?>
                    <option value="<?php echo (int)$p['id']; ?>" <?php echo $selectedPlan ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Título</label>
            <input type="text" name="title" required value="<?php echo htmlspecialchars((string)($subscription['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Tipo (site/email/sistema/combos)</label>
            <input type="text" name="plan_type" required value="<?php echo htmlspecialchars((string)($subscription['plan_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Preço</label>
            <input type="text" name="price" value="<?php echo htmlspecialchars((string)($subscription['price'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Ciclo</label>
            <select name="billing_cycle">
                <option value="monthly" <?php echo (($subscription['billing_cycle'] ?? 'monthly') === 'monthly') ? 'selected' : ''; ?>>Mensal</option>
                <option value="yearly" <?php echo (($subscription['billing_cycle'] ?? '') === 'yearly') ? 'selected' : ''; ?>>Anual</option>
            </select>
        </div>

        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" <?php echo (($subscription['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Ativa</option>
                <option value="inactive" <?php echo (($subscription['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inativa</option>
                <option value="overdue" <?php echo (($subscription['status'] ?? '') === 'overdue') ? 'selected' : ''; ?>>Atrasada</option>
            </select>
        </div>

        <div>
            <label>Início</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars((string)($subscription['start_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
            <label>Próx. vencimento</label>
            <input type="date" name="next_due_date" value="<?php echo htmlspecialchars((string)($subscription['next_due_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
