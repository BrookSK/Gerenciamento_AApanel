<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logs de Integração</title>
</head>
<body>
    <h1>Logs de Integração</h1>

    <div>
        Total: <?php echo (int)($data['total'] ?? 0); ?>
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Provider</th>
                <th>Ação</th>
                <th>Ref</th>
                <th>Status</th>
                <th>Mensagem</th>
                <th>Request</th>
                <th>Response</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($data['items'] ?? []) as $row) : ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['provider'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['action'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php echo htmlspecialchars((string)($row['reference_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo htmlspecialchars((string)($row['reference_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td><?php echo htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($row['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><pre style="max-width:420px; overflow:auto; white-space:pre-wrap;"><?php echo htmlspecialchars((string)($row['request_json'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></pre></td>
                    <td><pre style="max-width:420px; overflow:auto; white-space:pre-wrap;"><?php echo htmlspecialchars((string)($row['response_json'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></pre></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 12px;">
        <?php $pages = (int)($data['pages'] ?? 1); ?>
        <?php $page = (int)($data['page'] ?? 1); ?>
        <?php if ($page > 1) : ?>
            <a href="/integration-logs?page=<?php echo $page - 1; ?>">Anterior</a>
        <?php endif; ?>
        Página <?php echo $page; ?> de <?php echo $pages; ?>
        <?php if ($page < $pages) : ?>
            <a href="/integration-logs?page=<?php echo $page + 1; ?>">Próxima</a>
        <?php endif; ?>
    </div>
</body>
</html>
