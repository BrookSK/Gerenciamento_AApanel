<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chamado #<?php echo (int)$ticket['id']; ?></title>
</head>
<body>
    <h1>Chamado #<?php echo (int)$ticket['id']; ?></h1>

    <div>
        <a href="/portal/tickets">Voltar</a>
    </div>

    <div>
        <strong>Assunto:</strong> <?php echo htmlspecialchars((string)$ticket['subject'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div>
        <strong>Tipo:</strong> <?php echo htmlspecialchars((string)$ticket['type'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div>
        <strong>Status:</strong> <?php echo htmlspecialchars((string)$ticket['status'], ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <h2>Mensagens</h2>

    <?php foreach ($messages as $m) : ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <div>
                <strong><?php echo htmlspecialchars((string)$m['sender_type'], ENT_QUOTES, 'UTF-8'); ?></strong>
                em <?php echo htmlspecialchars((string)$m['created_at'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div>
                <?php echo nl2br(htmlspecialchars((string)$m['message_text'], ENT_QUOTES, 'UTF-8')); ?>
            </div>

            <?php $atts = $attachmentsByMessageId[(int)$m['id']] ?? []; ?>
            <?php if (count($atts) > 0) : ?>
                <div style="margin-top: 8px;">
                    <strong>Anexos desta mensagem</strong>
                    <?php foreach ($atts as $a) : ?>
                        <div>
                            <a href="/portal/tickets/attachment?id=<?php echo (int)$a['id']; ?>">
                                <?php echo htmlspecialchars((string)$a['original_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <h2>Anexos</h2>
    <?php foreach ($attachments as $a) : ?>
        <div>
            <a href="/portal/tickets/attachment?id=<?php echo (int)$a['id']; ?>">
                <?php echo htmlspecialchars((string)$a['original_name'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </div>
    <?php endforeach; ?>

    <h2>Responder</h2>

    <form method="post" action="/portal/tickets/reply">
        <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
        <div>
            <textarea name="message" rows="5" cols="60" required></textarea>
        </div>
        <button type="submit">Enviar</button>
    </form>

    <h2>Responder com anexo</h2>

    <form method="post" action="/portal/tickets/upload" enctype="multipart/form-data">
        <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
        <div>
            <textarea name="message" rows="5" cols="60" required></textarea>
        </div>
        <div>
            <input type="file" name="files[]" multiple required>
        </div>
        <button type="submit">Enviar com anexo</button>
    </form>
</body>
</html>
