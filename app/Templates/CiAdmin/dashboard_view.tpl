<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - <?= esc($dbName) ?></title>
</head>
<body>
    <h1>Dashboard - <?= esc($dbName) ?></h1>

    <h2>Secciones:</h2>
    <ul>
        <?php foreach ($tables as $table): ?>
            <li><a href="<?= site_url($table) ?>"><?= ucfirst($table) ?></a></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
