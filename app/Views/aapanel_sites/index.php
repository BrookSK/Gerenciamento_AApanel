<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sites (aaPanel)</title>
</head>
<body>
    <h1>Sites (aaPanel)</h1>

    <div>
        <a href="/aapanel-sites/create">Novo site</a>
    </div>

    <?php if (!empty($error)) : ?>
        <div style="color: #b00020;">
            <?php echo htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Path</th>
                <th>Status</th>
                <th>Assinatura</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($sites ?? []) as $s) : ?>
                <?php
                $id = (string)($s['id'] ?? ($s['siteId'] ?? ''));
                $name = (string)($s['name'] ?? ($s['siteName'] ?? ($s['domain'] ?? '')));
                $path = (string)($s['path'] ?? ($s['sitePath'] ?? ''));
                $status = (string)($s['status'] ?? ($s['runStatus'] ?? ($s['state'] ?? '')));
                $link = ($linksByResourceName ?? [])[$name] ?? null;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($path, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if (is_array($link)) : ?>
                            <?php echo htmlspecialchars((string)($link['client_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            #<?php echo (int)($link['subscription_id'] ?? 0); ?>
                            (<?php echo htmlspecialchars((string)($link['subscription_title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)

                            <form method="post" action="/provisioning/wordpress/install-linked" style="display:inline; margin-left:6px;">
                                <input type="hidden" name="subscription_id" value="<?php echo (int)($link['subscription_id'] ?? 0); ?>">
                                <input type="hidden" name="domain" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit">Instalar WP</button>
                            </form>

                            <form method="post" action="/aapanel-sites/unlink" style="display:inline; margin-left:6px;">
                                <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit">Desvincular</button>
                            </form>
                        <?php else : ?>
                            <form method="post" action="/aapanel-sites/link" style="display:inline;">
                                <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="aapanel_id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                                <select name="subscription_id" required>
                                    <option value="">Vincular...</option>
                                    <?php foreach (($subscriptions ?? []) as $sub) : ?>
                                        <option value="<?php echo (int)$sub['id']; ?>">
                                            <?php echo htmlspecialchars((string)$sub['client_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            #<?php echo (int)$sub['id']; ?>
                                            - <?php echo htmlspecialchars((string)$sub['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Vincular</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="/aapanel-sites/start" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit">Iniciar</button>
                        </form>
                        <form method="post" action="/aapanel-sites/stop" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit">Parar</button>
                        </form>
                        <form method="post" action="/aapanel-sites/delete" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                            <label style="display:inline; margin-left:6px;">
                                <input type="checkbox" name="delete_path" value="1"> remover arquivos
                            </label>
                            <label style="display:inline; margin-left:6px;">
                                <input type="checkbox" name="delete_db" value="1"> remover DB
                            </label>
                            <label style="display:inline; margin-left:6px;">
                                <input type="checkbox" name="delete_ftp" value="1"> remover FTP
                            </label>
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
