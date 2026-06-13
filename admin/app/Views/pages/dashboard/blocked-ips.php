<header>
    <div class="header-content">
        <div>
            <h1 class="section-title" style="margin: 0; font-size: 28px;"><?= __('blocked_ips.page_title') ?></h1>
            <p class="section-subtitle" style="margin: 4px 0 0 0;"><?= __('blocked_ips.page_subtitle') ?></p>
        </div>
    </div>
</header>

<section class="fade-in visible">
    <style>
        .blocked-ips-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .blocked-ips-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 18px;
        }

        .blocked-ips-card h3 {
            margin: 0 0 14px;
            font-size: 16px;
            color: var(--text-primary);
        }

        .blocked-ips-label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
        }

        .blocked-ips-input,
        .blocked-ips-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 14px;
        }

        .blocked-ips-textarea {
            min-height: 130px;
            resize: vertical;
            line-height: 1.4;
        }

        .blocked-ips-help {
            display: block;
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 12px;
        }

        .blocked-ips-form-row {
            margin-bottom: 12px;
        }

        .blocked-ips-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: var(--bg-card);
        }

        .blocked-ips-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        .blocked-ips-table th,
        .blocked-ips-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
            vertical-align: middle;
        }

        .blocked-ips-table th {
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .blocked-ips-table td {
            color: var(--text-primary);
            font-size: 14px;
        }

        .blocked-ips-ip {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 13px;
        }

        .blocked-ips-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .blocked-ips-status.active {
            background: rgba(34, 197, 94, 0.16);
            color: #22c55e;
            border: 1px solid #22c55e;
        }

        .blocked-ips-status.inactive {
            background: rgba(239, 68, 68, 0.16);
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .blocked-ips-actions {
            display: flex;
            gap: 8px;
        }

        .blocked-ips-btn {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 10px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .blocked-ips-btn.primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .blocked-ips-btn.danger {
            background: rgba(239, 68, 68, 0.16);
            border-color: #ef4444;
            color: #ef4444;
        }

        @media (max-width: 980px) {
            .blocked-ips-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="blocked-ips-grid">
        <div class="blocked-ips-card">
            <h3><?= __('blocked_ips.add_ip') ?></h3>
            <form method="POST" action="/admin/?route=blocked-ips">
                <input type="hidden" name="action" value="create">

                <div class="blocked-ips-form-row">
                    <label class="blocked-ips-label" for="ip_address"><?= __('blocked_ips.ip_address') ?> *</label>
                    <input id="ip_address" class="blocked-ips-input" name="ip_address" placeholder="192.168.0.1" required>
                </div>

                <div class="blocked-ips-form-row">
                    <label class="blocked-ips-label" for="reason"><?= __('blocked_ips.reason') ?></label>
                    <input id="reason" class="blocked-ips-input" name="reason" placeholder="<?= __('blocked_ips.reason_placeholder') ?>">
                </div>

                <button type="submit" class="blocked-ips-btn primary">+ <?= __('blocked_ips.add_ip') ?></button>
            </form>
        </div>

        <div class="blocked-ips-card">
            <h3><?= __('blocked_ips.bulk_add') ?></h3>
            <form method="POST" action="/admin/?route=blocked-ips">
                <input type="hidden" name="action" value="bulk_add">

                <div class="blocked-ips-form-row">
                    <label class="blocked-ips-label" for="ip_list"><?= __('blocked_ips.bulk_list_label') ?></label>
                    <textarea id="ip_list" class="blocked-ips-textarea" name="ip_list" placeholder="134.249.248.30&#10;2a03:2260:10:22:19c2:7b5e:5809:a0cc"></textarea>
                    <small class="blocked-ips-help"><?= __('blocked_ips.bulk_list_help') ?>. <?= __('blocked_ips.ipv4_ipv6_support') ?>.</small>
                </div>

                <div class="blocked-ips-form-row">
                    <label class="blocked-ips-label" for="bulk_reason"><?= __('blocked_ips.bulk_reason_label') ?></label>
                    <input id="bulk_reason" class="blocked-ips-input" name="bulk_reason" placeholder="<?= __('blocked_ips.bulk_reason_placeholder') ?>">
                </div>

                <button type="submit" class="blocked-ips-btn primary">+ <?= __('blocked_ips.bulk_add') ?></button>
            </form>
        </div>
    </div>

    <div class="blocked-ips-card">
        <h3><?= __('blocked_ips.page_title') ?></h3>

        <?php if (empty($ips)): ?>
            <p style="margin: 0; color: var(--text-secondary);"><?= __('blocked_ips.no_ips') ?></p>
        <?php else: ?>
            <div class="blocked-ips-table-wrap">
                <table class="blocked-ips-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= __('blocked_ips.ip_address') ?></th>
                            <th><?= __('blocked_ips.reason') ?></th>
                            <th><?= __('blocked_ips.added') ?></th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ips as $ip): ?>
                            <tr>
                                <td><?= (int)$ip['id'] ?></td>
                                <td class="blocked-ips-ip"><?= htmlspecialchars($ip['ip_address']) ?></td>
                                <td><?= htmlspecialchars($ip['reason'] ?? '') ?></td>
                                <td><?= !empty($ip['created_at']) ? date('d.m.Y H:i', strtotime($ip['created_at'])) : '-' ?></td>
                                <td>
                                    <span class="blocked-ips-status <?= (int)$ip['is_active'] === 1 ? 'active' : 'inactive' ?>">
                                        <?= (int)$ip['is_active'] === 1 ? 'Active' : 'Disabled' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="blocked-ips-actions">
                                        <form method="POST" action="/admin/?route=blocked-ips">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= (int)$ip['id'] ?>">
                                            <button type="submit" class="blocked-ips-btn">
                                                <?= (int)$ip['is_active'] === 1 ? 'Disable' : 'Enable' ?>
                                            </button>
                                        </form>

                                        <form method="POST" action="/admin/?route=blocked-ips" onsubmit="return confirm('Delete this IP?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$ip['id'] ?>">
                                            <button type="submit" class="blocked-ips-btn danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
