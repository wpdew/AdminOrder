<!-- Header -->
<header>
    <div class="header-content">
        <div>
            <h1 class="section-title" style="margin: 0; font-size: 28px;"><?= __('order_settings.page_title') ?></h1>
            <p class="section-subtitle" style="margin: 4px 0 0 0;"><?= __('order_settings.page_subtitle') ?></p>
        </div>
    </div>
</header>

<style>
    .order-settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 20px;
    }

    .settings-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }

    .settings-card h3 {
        margin: 0;
        font-size: 18px;
        color: var(--text-primary);
    }

    .settings-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .settings-field {
        margin-bottom: 14px;
    }

    .settings-field:last-child {
        margin-bottom: 0;
    }

    .settings-field label {
        display: block;
        margin-bottom: 6px;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
    }

    .settings-field input {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--input-bg);
        color: var(--text-primary);
        font-size: 14px;
    }

    .secret-field-wrap {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .secret-toggle-btn {
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-secondary);
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 12px;
        cursor: pointer;
        white-space: nowrap;
    }

    .secret-toggle-btn:hover {
        color: var(--text-primary);
        border-color: var(--primary-color);
    }

    .settings-footer {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
    }
</style>

<section class="fade-in visible">
    <div class="stat-card glow">
        <form method="POST" action="/admin/?route=settings">
            <div class="order-settings-grid">
                <?php foreach ($definition as $groupKey => $group): ?>
                    <?php
                        $groupActive = false;
                        foreach ($group['fields'] as $key => $field) {
                            if (!empty($settingsMap[$key]) && (int)$settingsMap[$key]['is_active'] === 1) {
                                $groupActive = true;
                                break;
                            }
                        }
                    ?>

                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h3><?= htmlspecialchars($group['title']) ?></h3>
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary);">
                                <input type="checkbox" id="group-<?= htmlspecialchars($groupKey) ?>" <?= $groupActive ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                                <?= __('order_settings.active_label') ?>
                            </label>
                        </div>

                        <?php foreach ($group['fields'] as $key => $field): ?>
                            <?php
                                $currentValue = $settingsMap[$key]['setting_value'] ?? ($field['default'] ?? '');
                                $isActive = !empty($settingsMap[$key]) ? ((int)$settingsMap[$key]['is_active'] === 1) : true;
                                $isSecret = str_contains($key, 'token');
                            ?>

                            <div class="settings-field">
                                <input type="hidden" name="active[<?= htmlspecialchars($key) ?>]" value="0" data-group="<?= htmlspecialchars($groupKey) ?>">

                                <label for="<?= htmlspecialchars($key) ?>" style="display: flex; align-items: center; justify-content: space-between;">
                                    <span><?= htmlspecialchars($field['label']) ?></span>
                                    <input
                                        type="checkbox"
                                        name="active[<?= htmlspecialchars($key) ?>]"
                                        value="1"
                                        data-group="<?= htmlspecialchars($groupKey) ?>"
                                        <?= $isActive ? 'checked' : '' ?>
                                        style="width: 16px; height: 16px;"
                                    >
                                </label>
                                <?php if ($isSecret): ?>
                                    <div class="secret-field-wrap">
                                        <input
                                            id="<?= htmlspecialchars($key) ?>"
                                            type="password"
                                            name="settings[<?= htmlspecialchars($key) ?>]"
                                            value="<?= htmlspecialchars((string)$currentValue) ?>"
                                            placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                            autocomplete="off"
                                            data-secret-input
                                        >
                                        <button type="button" class="secret-toggle-btn" data-toggle-secret data-target="<?= htmlspecialchars($key) ?>"><?= __('order_settings.show_secret') ?></button>
                                    </div>
                                <?php else: ?>
                                    <input
                                        id="<?= htmlspecialchars($key) ?>"
                                        type="text"
                                        name="settings[<?= htmlspecialchars($key) ?>]"
                                        value="<?= htmlspecialchars((string)$currentValue) ?>"
                                        placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="settings-footer">
                <button type="submit" class="btn" style="background: var(--primary-color); color: #fff; border: none; padding: 12px 18px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <?= __('form.save_settings') ?>
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    document.querySelectorAll('[id^="group-"]').forEach((groupToggle) => {
        groupToggle.addEventListener('change', function () {
            const group = this.id.replace('group-', '');
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="active["][data-group="' + group + '"]');

            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    });

    document.querySelectorAll('[data-toggle-secret]').forEach((btn) => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            this.textContent = isHidden ? <?= json_encode(__('order_settings.hide_secret')) ?> : <?= json_encode(__('order_settings.show_secret')) ?>;
        });
    });
</script>
