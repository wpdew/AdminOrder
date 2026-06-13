<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Про AdminOrder Dashboard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #667eea; 
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        h2 { 
            color: #764ba2; 
            margin-top: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .tagline {
            color: #7f8c8d;
            font-size: 1.2em;
            margin-bottom: 30px;
        }
        .feature {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .cta {
            text-align: center;
            margin-top: 40px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #764ba2;
        }
        footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 0.9em;
            color: #7f8c8d;
        }
        footer a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>AdminOrder Dashboard</h1>
        <p class="tagline">Сучасна адміністративна панель для управління вашим бізнесом</p>

        <h2>Що таке AdminOrder Dashboard?</h2>
        <p>AdminOrder Dashboard — це потужна веб-платформа для управління користувачами, даними та налаштуваннями вашої системи. Побудована на сучасних технологіях (PHP 8, SQLite, MVC архітектура) з акцентом на безпеку та зручність використання.</p>

        <h2>Основні можливості</h2>
        
        <div class="feature">
            <h3>🔐 OAuth Авторизація</h3>
            <p>Швидкий та безпечний вхід через Google або GitHub. Ніяких паролів для запам'ятовування!</p>
        </div>

        <div class="feature">
            <h3>👥 Управління користувачами</h3>
            <p>Повний CRUD для користувачів: створення, редагування, видалення, перегляд профілів.</p>
        </div>

        <div class="feature">
            <h3>🌍 Мультимовність</h3>
            <p>Підтримка української, російської та англійської мов інтерфейсу.</p>
        </div>

        <div class="feature">
            <h3>🎨 Сучасний UI</h3>
            <p>Адаптивний дизайн з темною темою, працює на всіх пристроях.</p>
        </div>

        <div class="feature">
            <h3>⚡ Швидкість</h3>
            <p>SQLite база даних, мінімальні залежності, блискавична швидкість роботи.</p>
        </div>

        <div class="feature">
            <h3>🔒 Безпека</h3>
            <p>Session-based авторизація, CSRF захист, prepared statements для SQL.</p>
        </div>

        <h2>Для кого?</h2>
        <p>AdminOrder Dashboard ідеально підходить для:</p>
        <ul>
            <li>SaaS платформ</li>
            <li>E-commerce проектів</li>
            <li>CRM систем</li>
            <li>Будь-яких веб-додатків, що потребують адмін-панелі</li>
        </ul>

        <div class="cta">
            <a href="/admin/" class="btn">Увійти в систему</a>
        </div>

        <footer>
            <a href="/privacy.php">Політика конфіденційності</a> |
            <a href="/terms.php">Умови використання</a> |
            <a href="mailto:support@adminorder.e-bash.men">Контакти</a>
        </footer>
    </div>
</body>
</html>
