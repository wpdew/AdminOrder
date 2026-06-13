<div style="display: flex; align-items: center; justify-content: center; min-height: 70vh; background: var(--bg-primary);">
    <div style="text-align: center; max-width: 500px; padding: 40px;">
        <div style="font-size: 96px; margin-bottom: 20px;">🔒</div>
        <h1 style="color: var(--text-primary); margin-bottom: 15px;">Доступ запрещен</h1>
        <p style="color: var(--text-secondary); font-size: 16px; margin-bottom: 30px;">
            У вас нет прав для доступа к этой странице. Обратитесь к администратору системы.
        </p>
        <a href="<?= \App\Core\Auth::isAdmin() ? '/admin/' : '/admin/?route=profile' ?>" style="display: inline-block; padding: 12px 30px; background: var(--primary-color); color: white; text-decoration: none; border-radius: 8px; font-weight: 500;">
            <?= \App\Core\Auth::isAdmin() ? 'Вернуться на главную' : 'Вернуться в профиль' ?>
        </a>
    </div>
</div>
