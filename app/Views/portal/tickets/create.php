<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Abrir Chamado</title>
</head>
<body>
    <h1>Abrir Chamado</h1>

    <div>
        <a href="/portal/tickets">Voltar</a>
    </div>

    <?php if (!empty($error)) : ?>
        <div style="margin: 10px 0; padding: 10px; border: 1px solid #cc0000;">
            <?php echo htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/portal/tickets/store" enctype="multipart/form-data">
        <div>
            <label>Assunto</label>
            <input type="text" name="subject" required>
        </div>

        <div>
            <label>Tipo</label>
            <select name="type" required>
                <option value="duvida">Dúvida</option>
                <option value="instabilidade">Instabilidade</option>
                <option value="erro">Erro</option>
                <option value="outro">Outro</option>
            </select>
        </div>

        <div>
            <label>Mensagem</label>
            <textarea name="message" rows="6" cols="60" required></textarea>
        </div>

        <div>
            <label>Anexo (opcional)</label>
            <input type="file" name="files[]" multiple>
        </div>

        <button type="submit">Abrir chamado</button>
    </form>
</body>
</html>
