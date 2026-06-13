<header>
    <div class="header-content">
        <div>
            <h1 class="section-title" style="margin: 0; font-size: 28px;">Користувачі</h1>
            <p class="section-subtitle" style="margin: 4px 0 0 0;">Управління користувачами системи</p>
        </div>
        <div class="header-controls">
            <button class="btn" id="addUserBtn" style="background: var(--primary-color); color: white;">
                + Додати користувача
            </button>
        </div>
    </div>
</header>

<section class="fade-in visible">
    <div class="stat-card glow">
        <div class="findings-table">
            <div class="table-header">
                <div>ID</div>
                <div>Ім'я</div>
                <div>Email</div>
                <div>Нікнейм</div>
                <div>Роль</div>
                <div>Створено</div>
                <div>Останній вхід</div>
                <div style="text-align: center;">Дії</div>
            </div>
            
            <?php if (empty($users)): ?>
            <div class="table-row">
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-secondary);">
                    Користувачів не знайдено
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                <div class="table-row">
                    <div><?= $user['id'] ?></div>
                    <div>
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="" style="width: 24px; height: 24px; border-radius: 50%; vertical-align: middle; margin-right: 8px;">
                        <?php endif; ?>
                        <?= htmlspecialchars($user['name'] ?? 'N/A') ?>
                        <?php if (!empty($user['oauth_provider'])): ?>
                            <span style="font-size: 11px; background: var(--input-bg); padding: 2px 6px; border-radius: 4px; margin-left: 6px;">
                                <?= $user['oauth_provider'] === 'google' ? '🔵 Google' : '⚫ GitHub' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div><?= htmlspecialchars($user['email']) ?></div>
                    <div><?= htmlspecialchars($user['nickname']) ?></div>
                    <div>
                        <select 
                            class="role-select" 
                            data-user-id="<?= $user['id'] ?>"
                            style="padding: 6px 10px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 13px; cursor: pointer;"
                            <?= $user['id'] === \App\Core\Auth::id() ? 'disabled' : '' ?>
                        >
                            <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>👤 Користувач</option>
                            <option value="admin" <?= ($user['role'] ?? 'user') === 'admin' ? 'selected' : '' ?>>👑 Адмін</option>
                        </select>
                    </div>
                    <div><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></div>
                    <div>
                        <?php if ($user['last_login']): ?>
                            <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?>
                        <?php else: ?>
                            <span style="color: var(--text-secondary);">Ніколи</span>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: center;">
                        <button 
                            class="btn-icon edit-user-btn" 
                            data-id="<?= $user['id'] ?>"
                            data-name="<?= htmlspecialchars($user['name']) ?>"
                            data-email="<?= htmlspecialchars($user['email']) ?>"
                            data-nickname="<?= htmlspecialchars($user['nickname']) ?>"
                            data-language="<?= htmlspecialchars($user['interface_lang'] ?? 'en') ?>"
                            title="Редактировать"
                            style="background: var(--primary-color); color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; margin-right: 4px;"
                        >
                            ✏️
                        </button>
                        <button 
                            class="btn-icon delete-user-btn" 
                            data-id="<?= $user['id'] ?>"
                            data-name="<?= htmlspecialchars($user['name']) ?>"
                            title="Удалить"
                            style="background: #ff4444; color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer;"
                            <?= $user['id'] === \App\Core\Auth::id() ? 'disabled' : '' ?>
                        >
                            🗑️
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Модальное окно добавления пользователя -->
<div id="addUserModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: var(--card-bg); border-radius: 12px; padding: 30px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="margin: 0; font-size: 24px;">Додати користувача</h2>
            <button class="modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary);">&times;</button>
        </div>
        
        <form id="addUserForm">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Ім'я <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Email <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Нікнейм <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="text" 
                    name="nickname" 
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Пароль <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
                <small style="color: var(--text-secondary); font-size: 12px;">Мінімум 6 символів</small>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Мова інтерфейсу <span style="color: #ff4444;">*</span>
                </label>
                <select
                    name="interface_lang"
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
                    <option value="en" selected>English</option>
                    <option value="uk">Українська</option>
                    <option value="ru">Русский</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button 
                    type="button" 
                    class="modal-close btn"
                    style="background: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color); padding: 12px 24px; border-radius: 8px; cursor: pointer;"
                >
                    Відміна
                </button>
                <button 
                    type="submit" 
                    class="btn"
                    style="background: var(--primary-color); color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer;"
                >
                    Створити
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно редактирования пользователя -->
<div id="editUserModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: var(--card-bg); border-radius: 12px; padding: 30px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="margin: 0; font-size: 24px;">Редагувати користувача</h2>
            <button class="modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary);">&times;</button>
        </div>
        
        <form id="editUserForm">
            <input type="hidden" name="id" id="editUserId">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Ім'я <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="editUserName"
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Email <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="editUserEmail"
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Нікнейм <span style="color: #ff4444;">*</span>
                </label>
                <input 
                    type="text" 
                    name="nickname" 
                    id="editUserNickname"
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Новий пароль
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="editUserPassword"
                    placeholder="Залиште порожнім, якщо не хочете змінювати"
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
                <small style="color: var(--text-secondary); font-size: 12px;">Мінімум 6 символів (якщо заповнено)</small>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 500;">
                    Мова інтерфейсу <span style="color: #ff4444;">*</span>
                </label>
                <select
                    name="interface_lang"
                    id="editUserLanguage"
                    required
                    style="width: 100%; padding: 12px; background: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px;"
                >
                    <option value="en">English</option>
                    <option value="uk">Українська</option>
                    <option value="ru">Русский</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button 
                    type="button" 
                    class="modal-close btn"
                    style="background: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color); padding: 12px 24px; border-radius: 8px; cursor: pointer;"
                >
                    Відміна
                </button>
                <button 
                    type="submit" 
                    class="btn"
                    style="background: var(--primary-color); color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer;"
                >
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальне вікно підтвердження видалення -->
<div id="deleteUserModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: var(--card-bg); border-radius: 12px; padding: 30px; max-width: 480px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h2 style="margin: 0; font-size: 24px;">Підтвердьте видалення</h2>
            <button class="modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary);">&times;</button>
        </div>

        <p id="deleteUserMessage" style="margin: 0 0 24px 0; color: var(--text-secondary); font-size: 16px; line-height: 1.45;">
            Ви впевнені, що хочете видалити користувача?
        </p>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button 
                type="button" 
                class="modal-close btn"
                style="background: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color); padding: 12px 24px; border-radius: 8px; cursor: pointer;"
            >
                Відміна
            </button>
            <button 
                type="button" 
                id="confirmDeleteUserBtn"
                class="btn"
                style="background: #ef4444; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer;"
            >
                Видалити
            </button>
        </div>
    </div>
</div>

<script>
// Ждем полной загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    function showToast(type, message) {
        let container = document.querySelector('.kg-toast-container');

        if (!container) {
            container = document.createElement('div');
            container.className = 'kg-toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }

        const labels = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Info'
        };

        const toast = document.createElement('div');
        toast.className = `kg-toast kg-toast-${type}`;
        toast.setAttribute('role', 'status');
        toast.innerHTML = `
            <div class="kg-toast-header">
                <span>${labels[type] || 'Info'}</span>
                <button type="button" class="kg-toast-close" aria-label="Close">&times;</button>
            </div>
            <div class="kg-toast-body">${message}</div>
        `;

        container.appendChild(toast);

        const hideToast = () => {
            if (toast.classList.contains('hide')) {
                return;
            }
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 220);
        };

        const closeBtn = toast.querySelector('.kg-toast-close');
        closeBtn?.addEventListener('click', hideToast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(hideToast, 3800);
    }

    // Модальные окна
    const addUserModal = document.getElementById('addUserModal');
    const editUserModal = document.getElementById('editUserModal');
    const deleteUserModal = document.getElementById('deleteUserModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');
    const confirmDeleteUserBtn = document.getElementById('confirmDeleteUserBtn');
    const deleteUserMessage = document.getElementById('deleteUserMessage');

    let pendingDeleteUser = null;

    // Открытие модального окна добавления
    addUserBtn?.addEventListener('click', () => {
        addUserModal.style.display = 'flex';
        addUserForm.reset();
    });

    // Закрытие модальных окон
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.modal').style.display = 'none';
        });
    });

    // Закрытие по клику вне модального окна
    [addUserModal, editUserModal, deleteUserModal].forEach(modal => {
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                if (modal === deleteUserModal) {
                    pendingDeleteUser = null;
                }
            }
        });
    });

    deleteUserModal?.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            pendingDeleteUser = null;
        });
    });

    // Добавление пользователя
    addUserForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(addUserForm);
        
        try {
            const response = await fetch('/admin/api/users/create.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('success', result.message || 'Користувач успішно створений');
                addUserModal.style.display = 'none';
                setTimeout(() => location.reload(), 700);
            } else {
                showToast('error', 'Помилка: ' + (result.error || 'Невідома помилка'));
            }
        } catch (error) {
            showToast('error', 'Помилка при створенні користувача');
            console.error(error);
        }
    });

    // Відкриття модального вікна редагування
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const email = btn.dataset.email;
            const nickname = btn.dataset.nickname;
            const language = btn.dataset.language || 'en';
            
            document.getElementById('editUserId').value = id;
            document.getElementById('editUserName').value = name;
            document.getElementById('editUserEmail').value = email;
            document.getElementById('editUserNickname').value = nickname;
            document.getElementById('editUserLanguage').value = language;
            document.getElementById('editUserPassword').value = '';
            
            editUserModal.style.display = 'flex';
        });
    });

    // Редактирование пользователя
    editUserForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(editUserForm);
        
        try {
            const response = await fetch('/admin/api/users/update.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('success', result.message || 'Користувач успішно оновлений');
                editUserModal.style.display = 'none';
                setTimeout(() => location.reload(), 700);
            } else {
                showToast('error', 'Помилка: ' + (result.error || 'Невідома помилка'));
            }
        } catch (error) {
            showToast('error', 'Помилка при оновленні користувача');
            console.error(error);
        }
    });

    // Видалення користувача
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;

            if (!deleteUserModal || !confirmDeleteUserBtn || !deleteUserMessage) {
                showToast('error', 'Не вдалося відкрити вікно підтвердження');
                return;
            }

            pendingDeleteUser = { id, name };
            deleteUserMessage.textContent = `Ви впевнені, що хочете видалити користувача "${name}"?`;
            deleteUserModal.style.display = 'flex';
        });
    });

    confirmDeleteUserBtn?.addEventListener('click', async () => {
        if (!pendingDeleteUser) {
            return;
        }

        const { id } = pendingDeleteUser;
        confirmDeleteUserBtn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch('/admin/api/users/delete.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    deleteUserModal.style.display = 'none';
                    showToast('success', result.message || 'Користувач успішно видалений');
                    setTimeout(() => location.reload(), 700);
                } else {
                    showToast('error', 'Помилка: ' + (result.error || 'Невідома помилка'));
                }
            } catch (error) {
                showToast('error', 'Помилка при видаленні користувача');
                console.error(error);
            } finally {
                confirmDeleteUserBtn.disabled = false;
                pendingDeleteUser = null;
            }
    });

    // Зміна ролі користувача
    document.querySelectorAll('.role-select').forEach(select => {
        // Зберігаємо початкове значення
        select.dataset.previousValue = select.value;
        
        select.addEventListener('change', async (e) => {
            const userId = e.target.dataset.userId;
            const newRole = e.target.value;
            const previousValue = e.target.dataset.previousValue;

            e.target.disabled = true;
            //showToast('info', `Змінюємо роль на "${newRole === 'admin' ? 'Адмін' : 'Користувач'}"...`);
            
            try {
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('role', newRole);
                
                const response = await fetch('/admin/api/users/update-role.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Оновлюємо збережене значення
                    e.target.dataset.previousValue = newRole;
                    showToast('success', 'Роль успішно оновлена');
                } else {
                    showToast('error', 'Помилка: ' + (result.error || 'Невідома помилка'));
                    e.target.value = previousValue;
                }
            } catch (error) {
                showToast('error', 'Помилка при зміні ролі');
                console.error(error);
                e.target.value = previousValue;
            } finally {
                e.target.disabled = false;
            }
        });
    });
});
</script>
