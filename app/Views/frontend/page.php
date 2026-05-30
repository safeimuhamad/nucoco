<!DOCTYPE html>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page['meta_title'] ?: $page['title']) ?></title>
    <meta name="description" content="<?= esc($page['meta_description'] ?? '') ?>">
</head>
<body>
    <h1><?= esc($page['title']) ?></h1>
    <div>
        <?= $page['content'] ?>
    </div>
</body>
</html>