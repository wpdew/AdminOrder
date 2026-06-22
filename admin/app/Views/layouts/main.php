<?php
use App\Core\Auth;
use App\Models\Settings;
$currentUser = Auth::user();

$showOrdersMenu = true;
if (Auth::isAdmin()) {
    try {
        $showOrdersMenu = (new Settings())->isInternalOrdersDbEnabled();
    } catch (\Throwable $e) {
        $showOrdersMenu = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - AdminOrder</title>
    <link rel="stylesheet" href="/admin/css/styles.css">
    <style>
        .kg-toast-container {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 2200;
            width: min(380px, calc(100vw - 24px));
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .kg-toast {
            pointer-events: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-primary);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
            overflow: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .kg-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .kg-toast.hide {
            opacity: 0;
            transform: translateY(-8px);
        }

        .kg-toast-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 13px;
            font-weight: 600;
        }

        .kg-toast-body {
            padding: 10px 12px 12px;
            font-size: 14px;
            line-height: 1.45;
            color: var(--text-primary);
        }

        .kg-toast-close {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            opacity: 0.72;
            padding: 0;
        }

        .kg-toast-close:hover {
            opacity: 1;
        }

        .kg-toast-success .kg-toast-header {
            background: rgba(34, 197, 94, 0.16);
            color: #22c55e;
        }

        .kg-toast-error .kg-toast-header {
            background: rgba(239, 68, 68, 0.18);
            color: #ef4444;
        }

        .kg-toast-warning .kg-toast-header {
            background: rgba(245, 158, 11, 0.18);
            color: #f59e0b;
        }

        .kg-toast-info .kg-toast-header {
            background: rgba(59, 130, 246, 0.18);
            color: #60a5fa;
        }
    </style>
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn" title="Toggle Menu">
        ☰
    </button>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
				
                <div class="logo-icon">📊</div>
				<a href="/admin/" style="text-decoration: none;color: var(--text-primary);">
                <span>AdminOrder</span>
				</a>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                ◀
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <?php if (Auth::isAdmin()): ?>
                <a href="/admin/" class="nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="icon">🏠</span>
                    <span><?= __('nav.dashboard') ?></span>
                </a>
                <?php if ($showOrdersMenu): ?>
                <a href="/admin/?route=table" class="nav-item <?= ($activeMenu ?? '') === 'table' ? 'active' : '' ?>">
                    <span class="icon">📋</span>
                    <span><?= __('nav.orders') ?> </span> <span id="newOrders" class="badge position-absolute top-0 start-100 translate-middle"></span> 
                </a>
                <?php endif; ?>
                <a href="/admin/?route=blocked-ips" class="nav-item <?= ($activeMenu ?? '') === 'blocked-ips' ? 'active' : '' ?>">
                    <span class="icon">⛔</span>
                    <span><?= __('nav.blocked_ips') ?></span>
                </a>
                <a href="/admin/?route=users" class="nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>">
                    <span class="icon">👥</span>
                    <span><?= __('nav.users') ?></span>
                </a>
                <?php endif; ?>
                <a href="/admin/?route=profile" class="nav-item <?= ($activeMenu ?? '') === 'profile' ? 'active' : '' ?>">
                    <span class="icon">⚙️</span>
                    <span><?= __('nav.profile') ?></span>
                </a>
                <?php if (Auth::isAdmin()): ?>
                <a href="/admin/?route=settings" class="nav-item <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>">
                    <span class="icon">🔌</span>
                    <span><?= __('nav.integrations') ?></span>
                </a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="sidebar-footer">
            <button class="theme-toggle sidebar-theme-toggle" id="themeToggle" title="Toggle theme">
                <span class="theme-icon">🌙</span>
            </button>
            <div class="user-profile" id="userProfile">
                <div class="user-avatar">
                    <?= strtoupper(mb_substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
                    <div class="user-email"><?= htmlspecialchars(mb_substr($currentUser['email'] ?? '', 0, 15)) ?>...</div>
                </div>
                <div class="user-dropdown-menu" id="userDropdown">
                    <a href="/admin/?route=profile" class="dropdown-item">
                        <span class="dropdown-icon">👤</span>
                        <span><?= __('nav.profile') ?></span>
                    </a>
                    <a href="/admin/?route=logout" class="dropdown-item logout">
                        <span class="dropdown-icon">🚪</span>
                        <span><?= __('auth.logout') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <?php
    $toastLabels = [
        'success' => __('common.success'),
        'error' => __('common.error'),
        'warning' => __('common.warning'),
        'info' => __('common.info'),
    ];

    $toasts = [];
    foreach ($toastLabels as $type => $label) {
        $sessionKey = 'flash_' . $type;
        if (!empty($_SESSION[$sessionKey])) {
            $toasts[] = [
                'type' => $type,
                'label' => $label,
                'message' => (string)$_SESSION[$sessionKey],
            ];
            unset($_SESSION[$sessionKey]);
        }
    }
    ?>

    <?php if (!empty($toasts)): ?>
    <div class="kg-toast-container" aria-live="polite" aria-atomic="true">
        <?php foreach ($toasts as $toast): ?>
        <div class="kg-toast kg-toast-<?= htmlspecialchars($toast['type']) ?>" role="status" data-kg-toast>
            <div class="kg-toast-header">
                <span><?= htmlspecialchars($toast['label']) ?></span>
                <button type="button" class="kg-toast-close" aria-label="<?= __('form.close') ?>" data-kg-toast-close>&times;</button>
            </div>
            <div class="kg-toast-body">
                <?= htmlspecialchars($toast['message']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container">

        <?= $content ?>
    </div>

    <script src="/admin/js/script.js"></script>
    <script>
        (function () {
            const toasts = document.querySelectorAll('[data-kg-toast]');
            if (!toasts.length) {
                return;
            }

            const hideToast = (toast) => {
                if (!toast || toast.classList.contains('hide')) {
                    return;
                }

                toast.classList.add('hide');
                window.setTimeout(() => toast.remove(), 220);
            };

            toasts.forEach((toast, index) => {
                window.setTimeout(() => toast.classList.add('show'), index * 90);

                const closeBtn = toast.querySelector('[data-kg-toast-close]');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => hideToast(toast));
                }

                window.setTimeout(() => hideToast(toast), 4200 + index * 250);
            });
        })();
    </script>
</body>
</html>
