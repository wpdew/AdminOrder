<div class="login-card fade-in">
    <div class="login-header">
        <h1>Регистрация</h1>
        <p>Создайте новый аккаунт</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error" style="margin-bottom: 20px; padding: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; border-radius: 8px; color: #ef4444;">
        ✗ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/?route=register" class="login-form">
        <div class="form-group">
            <label for="name">Имя</label>
            <div class="input-wrapper">
                <span class="input-icon">👤</span>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Ваше имя"
                    value="<?= htmlspecialchars($name ?? '') ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="nickname">Nickname</label>
            <div class="input-wrapper">
                <span class="input-icon">@</span>
                <input 
                    type="text" 
                    id="nickname" 
                    name="nickname" 
                    placeholder="username"
                    value="<?= htmlspecialchars($nickname ?? '') ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
                <span class="input-icon">📧</span>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="name@company.com"
                    value="<?= htmlspecialchars($email ?? '') ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <div class="input-wrapper">
                <span class="input-icon">🔒</span>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Минимум 6 символов"
                    required
                >
            </div>
        </div>

        <button type="submit" class="btn-primary">
            <span>Зарегистрироваться</span>
            <span class="btn-arrow">→</span>
        </button>
    </form>

    <div class="signup-link">
        Уже есть аккаунт? <a href="/admin/?route=login">Войти</a>
    </div>
</div>
