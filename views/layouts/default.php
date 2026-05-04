<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'App') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php include __DIR__ . '/../templates/footer.php'; ?>

    <script src="/assets/js/app.js" defer></script>
</body>
</html>
