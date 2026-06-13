<!-- Header -->
<header>
    <div class="header-content">
        <div>
            <h1 class="section-title" style="margin: 0; font-size: 28px;"><?= __('dashboard.title') ?></h1>
            <p class="section-subtitle" style="margin: 4px 0 0 0;"><?= __('dashboard.subtitle') ?></p>
        </div>
    </div>
</header>

<!-- Demo Stage Section -->
<section class="kg-demo-stage fade-in visible">
    <style>
        .dashboard-group {
            margin-bottom: 26px;
        }

        .dashboard-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .dashboard-group-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-secondary);
        }

        .dashboard-group-note {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .dashboard-grid-compact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .dashboard-grid-orders {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .dashboard-grid-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
        }

        .dashboard-stat-compact {
            padding: 14px 16px;
            min-height: 144px;
        }

        .dashboard-stat-link {
            display: block;
            color: inherit;
            text-decoration: none;
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
            cursor: pointer;
        }

        .dashboard-stat-link:hover,
        .dashboard-stat-link:focus-visible {
            transform: translateY(-2px);
            border-color: var(--primary-color);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
            outline: none;
        }

        .dashboard-stat-link:focus-visible {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), 0 12px 28px rgba(0, 0, 0, 0.18);
        }

        .dashboard-stat-link .stat-value,
        .dashboard-stat-link .stat-change,
        .dashboard-stat-link .stat-label,
        .dashboard-stat-link .stat-trend {
            pointer-events: none;
        }

        .dashboard-stat-compact .stat-header {
            margin-bottom: 12px;
            gap: 8px;
        }

        .dashboard-stat-compact .stat-icon {
            width: 30px;
            height: 30px;
            font-size: 15px;
            border-radius: 7px;
        }

        .dashboard-stat-compact .stat-label {
            font-size: 10px;
            letter-spacing: 0.09em;
        }

        .dashboard-stat-compact .stat-value {
            font-size: 26px;
            letter-spacing: -0.04em;
            margin-bottom: 8px;
        }

        .dashboard-stat-compact .stat-change {
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
            font-size: 11px;
        }

        .dashboard-stat-compact .stat-trend {
            font-size: 11px;
        }
    </style>

    <div class="dashboard-group">
        <div class="dashboard-group-header">
            <h2 class="dashboard-group-title"><?= __('dashboard_overview.system_group_title') ?></h2>
            <span class="dashboard-group-note"><?= __('dashboard_overview.system_group_note') ?></span>
        </div>

        <div class="dashboard-grid-compact">
            <?php foreach (($systemStats ?? []) as $stat): ?>
                <a
                    class="stat-card dashboard-stat-compact dashboard-stat-link <?= htmlspecialchars($stat['class']) ?> glow"
                    href="<?= htmlspecialchars($stat['url'] ?? '/admin/') ?>"
                    aria-label="<?= htmlspecialchars($stat['label']) ?>"
                >
                    <div class="stat-header">
                        <div class="stat-icon"><?= htmlspecialchars($stat['icon']) ?></div>
                        <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
                    </div>
                    <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                    <div class="stat-change">
                        <span class="stat-trend <?= htmlspecialchars($stat['trendClass']) ?>"><?= htmlspecialchars($stat['trend']) ?></span>
                        <span><?= htmlspecialchars($stat['meta']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($showOrdersStats)): ?>
    <div class="dashboard-group">
        <div class="dashboard-group-header">
            <h2 class="dashboard-group-title"><?= __('dashboard_overview.orders_group_title') ?></h2>
            <span class="dashboard-group-note"><?= __('dashboard_overview.orders_group_note') ?></span>
        </div>

        <div class="dashboard-grid-orders">
            <?php foreach (($orderSummaryStats ?? []) as $stat): ?>
                <a
                    class="stat-card dashboard-stat-compact dashboard-stat-link <?= htmlspecialchars($stat['class']) ?> glow"
                    href="<?= htmlspecialchars($stat['url'] ?? '/admin/?route=table') ?>"
                    aria-label="<?= htmlspecialchars($stat['label']) ?>"
                >
                    <div class="stat-header">
                        <div class="stat-icon"><?= htmlspecialchars($stat['icon']) ?></div>
                        <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
                    </div>
                    <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                    <div class="stat-change">
                        <span class="stat-trend <?= htmlspecialchars($stat['trendClass']) ?>"><?= htmlspecialchars($stat['trend']) ?></span>
                        <span><?= htmlspecialchars($stat['meta']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="dashboard-grid-status">
            <?php foreach (($orderStatusStats ?? []) as $stat): ?>
                <a
                    class="stat-card dashboard-stat-compact dashboard-stat-link <?= htmlspecialchars($stat['class']) ?> glow"
                    href="<?= htmlspecialchars($stat['url'] ?? '/admin/?route=table') ?>"
                    aria-label="<?= htmlspecialchars($stat['label']) ?>"
                >
                    <div class="stat-header">
                        <div class="stat-icon"><?= htmlspecialchars($stat['icon']) ?></div>
                        <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
                    </div>
                    <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                    <div class="stat-change">
                        <span class="stat-trend <?= htmlspecialchars($stat['trendClass']) ?>"><?= htmlspecialchars($stat['trend']) ?></span>
                        <span><?= htmlspecialchars($stat['meta']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Findings Table
    <div class="findings-section">
        <div class="findings-header">
            <h2 class="findings-title">Recent Findings</h2>
            <button class="btn">View All</button>
        </div>
        <div class="findings-table">
            <div class="table-header">
                <div>Finding</div>
                <div>Severity</div>
                <div>Status</div>
                <div>Detected</div>
                <div>Action</div>
            </div>
            <div class="table-row">
                <div>
                    <div class="finding-title">SQL Injection in user authentication</div>
                    <div class="finding-path">src/auth/login.js:42</div>
                </div>
                <div><span class="severity-badge severity-critical">Critical</span></div>
                <div><span class="status-badge status-open">Open</span></div>
                <div>2 hours ago</div>
                <div><button class="btn">Review</button></div>
            </div>
            <div class="table-row">
                <div>
                    <div class="finding-title">XSS vulnerability in comment section</div>
                    <div class="finding-path">src/components/Comments.tsx:128</div>
                </div>
                <div><span class="severity-badge severity-high">High</span></div>
                <div><span class="status-badge status-open">Open</span></div>
                <div>5 hours ago</div>
                <div><button class="btn">Review</button></div>
            </div>
            <div class="table-row">
                <div>
                    <div class="finding-title">Insecure direct object reference</div>
                    <div class="finding-path">src/api/users.js:76</div>
                </div>
                <div><span class="severity-badge severity-medium">Medium</span></div>
                <div><span class="status-badge status-resolved">Resolved</span></div>
                <div>1 day ago</div>
                <div><button class="btn">Details</button></div>
            </div>
        </div>
    </div>
	-->
</section>
