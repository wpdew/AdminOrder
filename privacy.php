<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Політика конфіденційності - AdminOrder Dashboard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .last-updated { color: #7f8c8d; font-size: 0.9em; }
        a { color: #3498db; }
    </style>
</head>
<body>
    <h1>Політика конфіденційності</h1>
    <p class="last-updated">Останнє оновлення: <?= date('d.m.Y') ?></p>

    <h2>1. Про застосунок</h2>
    <p><strong>AdminOrder Dashboard</strong> — адміністративна панель для управління даними та користувачами. Ми використовуємо OAuth авторизацію через Google та GitHub для спрощення входу в систему.</p>

    <h2>2. Які дані ми збираємо</h2>
    <p>При авторизації через Google або GitHub ми отримуємо та зберігаємо:</p>
    <ul>
        <li>Вашу електронну адресу (email)</li>
        <li>Ваше ім'я (або нікнейм з GitHub)</li>
        <li>URL вашої аватарки (з Google/GitHub)</li>
        <li>Унікальний ідентифікатор від провайдера OAuth</li>
    </ul>

    <h2>3. Як ми використовуємо дані</h2>
    <p>Зібрані дані використовуються виключно для:</p>
    <ul>
        <li>Ідентифікації користувача в системі</li>
        <li>Відображення профілю користувача</li>
        <li>Забезпечення доступу до адміністративної панелі</li>
    </ul>

    <h2>4. Зберігання даних</h2>
    <p>Ваші дані зберігаються в захищеній базі даних SQLite на нашому сервері. Ми не передаємо ваші особисті дані третім особам.</p>

    <h2>5. Видалення даних</h2>
    <p>Ви можете в будь-який момент попросити видалити ваші дані, зв'язавшись з адміністратором системи.</p>

    <h2>6. OAuth провайдери</h2>
    <p>Ми використовуємо OAuth авторизацію через:</p>
    <ul>
        <li><strong>Google</strong> — <a href="https://policies.google.com/privacy" target="_blank">Політика конфіденційності Google</a></li>
        <li><strong>GitHub</strong> — <a href="https://docs.github.com/privacy" target="_blank">Політика конфіденційності GitHub</a></li>
    </ul>

    <h2>7. Контакти</h2>
    <p>Якщо у вас є запитання щодо цієї політики конфіденційності, зв'яжіться з нами:</p>
    <p>Email: <a href="mailto:support@adminorder.e-bash.men">support@adminorder.e-bash.men</a></p>

    <hr style="margin-top: 40px;">
    <p style="text-align: center;">
        <a href="/">← Повернутися на головну</a> | 
        <a href="/admin/">Адмін-панель</a>
    </p>
</body>
</html>
