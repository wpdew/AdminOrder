<div class="login-card fade-in">
    <div class="login-header">
        <h1>Ласкаво просимо</h1>
        <p>Увійдіть у свій акаунт для продовження</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error" style="margin-bottom: 20px; padding: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; border-radius: 8px; color: #ef4444;">
        ✗ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/?route=login" class="login-form">
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
                    autocomplete="email"
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
                    placeholder="Введите пароль"
                    required
                    autocomplete="current-password"
                >
                <button type="button" class="toggle-password" id="togglePassword" title="Показать пароль">
                    👁️
                </button>
            </div>
        </div>

        <div class="form-options">
            <label class="checkbox-label">
                <input type="checkbox" id="remember" name="remember">
                <span>Запам'ятати мене</span>
            </label>
            <a href="#" class="forgot-password">Забули пароль?</a>
        </div>

        <button type="submit" class="btn-primary">
            <span>Увійти</span>
            <span class="btn-arrow">→</span>
        </button>

        <?php 
        $hasOAuth = \App\Config\OAuth::isGoogleConfigured() || \App\Config\OAuth::isGithubConfigured();
        if ($hasOAuth): 
        ?>
        <div class="divider">
            <span>або</span>
        </div>

        <div class="social-login">
            <?php if (\App\Config\OAuth::isGoogleConfigured()): ?>
            <a href="/admin/oauth/google/" class="btn-social btn-google">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9.003 18c2.43 0 4.467-.806 5.956-2.18l-2.909-2.259c-.806.54-1.836.86-3.047.86-2.344 0-4.328-1.584-5.036-3.711H.96v2.332C2.44 15.983 5.485 18 9.003 18z" fill="#34A853"/>
                    <path d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9.003 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.464.891 11.426 0 9.003 0 5.485 0 2.44 2.017.96 4.958L3.967 7.29c.708-2.127 2.692-3.71 5.036-3.71z" fill="#EA4335"/>
                </svg>
                <span>Google</span>
            </a>
            <?php endif; ?>
            
            <?php if (\App\Config\OAuth::isGithubConfigured()): ?>
            <a href="/admin/oauth/github/" class="btn-social btn-github">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 0C4.477 0 0 4.477 0 10c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0110 4.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C17.137 18.163 20 14.418 20 10c0-5.523-4.477-10-10-10z"/>
                </svg>
                <span>GitHub</span>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </form>

    <div class="signup-link">
        Немає акаунта? <a href="/admin/?route=register">Зареєструватися</a>
    </div>
</div>
