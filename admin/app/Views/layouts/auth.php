<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Auth' ?> - AdminOrder</title>
    <link rel="stylesheet" href="/admin/css/styles.css">
</head>
<body>
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" title="Switch theme">🌙</button>

    <div class="login-container">
        <?= $content ?>

        <!-- Background Decoration -->
        <div class="bg-decoration">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>
    </div>

    <script src="/admin/js/script.js"></script>
</body>
</html>
