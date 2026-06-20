<?php
$statusLabels = [
    'new' => __('orders.status_new'),
    'processing' => __('orders.status_processing'),
    'done' => __('orders.status_done'),
    'cancelled' => __('orders.status_cancelled'),
    'spam' => __('orders.status_spam'),
];
?>

<!-- Header -->
<header>
    <div class="header-content">
        <div>
            <h1 class="section-title" style="margin: 0; font-size: 28px;"><?= __('orders.title') ?></h1>
            <p class="section-subtitle" style="margin: 4px 0 0 0;"><?= __('orders.subtitle') ?></p>
        </div>
    </div>
</header>

<div class="table-card fade-in">
    <div class="table-filter-bar">
        <a class="table-filter-chip <?= ($statusFilter ?? '') === '' ? 'active' : '' ?>" href="/admin/?route=table">
            <?= __('orders.filter_all') ?> <span><?= (int)($allOrdersCount ?? count($orders ?? [])) ?></span>
        </a>
        <a class="table-filter-chip <?= ($statusFilter ?? '') === 'new' ? 'active' : '' ?>" href="/admin/?route=table&status=new">
            <?= __('orders.filter_new') ?> <span><?= (int)($statusCounts['new'] ?? 0) ?></span>
        </a>
        <a class="table-filter-chip <?= ($statusFilter ?? '') === 'processing' ? 'active' : '' ?>" href="/admin/?route=table&status=processing">
            <?= __('orders.filter_processing') ?> <span><?= (int)($statusCounts['processing'] ?? 0) ?></span>
        </a>
        <a class="table-filter-chip <?= ($statusFilter ?? '') === 'done' ? 'active' : '' ?>" href="/admin/?route=table&status=done">
            <?= __('orders.filter_done') ?> <span><?= (int)($statusCounts['done'] ?? 0) ?></span>
        </a>
        <a class="table-filter-chip <?= ($statusFilter ?? '') === 'cancelled' ? 'active' : '' ?>" href="/admin/?route=table&status=cancelled">
            <?= __('orders.filter_cancelled') ?> <span><?= (int)($statusCounts['cancelled'] ?? 0) ?></span>
        </a>
        <a class="table-filter-chip <?= ($statusFilter ?? '') === 'spam' ? 'active' : '' ?>" href="/admin/?route=table&status=spam">
            <?= __('orders.filter_spam') ?> <span><?= (int)($statusCounts['spam'] ?? 0) ?></span>
        </a>
    </div>

    <div class="table-scroll">
        <table id="ordersTable" class="display responsive" style="width:100%">
            <thead>
                <tr>
                    <th><?= __('orders.column_id') ?></th>
                    <th><?= __('orders.column_date') ?></th>
                    <th><?= __('orders.column_customer') ?></th>
                    <th><?= __('orders.column_phone') ?></th>
                    <th><?= __('orders.column_product') ?></th>
                    <th><?= __('orders.column_total') ?></th>
                    <th><?= __('orders.column_status') ?></th>
                    <th><?= __('orders.column_spam') ?></th>
                    <th><?= __('orders.column_actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                        $status = trim((string)($order['status'] ?? 'new'));
                        $statusClass = 'order-status-new';
                        if ($status === 'processing') {
                            $statusClass = 'order-status-processing';
                        } elseif ($status === 'done') {
                            $statusClass = 'order-status-done';
                        } elseif ($status === 'cancelled') {
                            $statusClass = 'order-status-cancelled';
                        } elseif ($status === 'spam') {
                            $statusClass = 'order-status-spam';
                        }
                        $isSpam = (int)($order['is_spam'] ?? 0) === 1;
                    ?>
                    <tr>
                        <td><?= (int)$order['id'] ?></td>
                        <td><?= !empty($order['created_at']) ? date('d.m.Y H:i', strtotime($order['created_at'])) : '-' ?></td>
                        <td><?= htmlspecialchars($order['customer_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($order['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($order['product_title'] ?? '') ?></td>
                        <td><?= number_format((float)($order['total_sum'] ?? 0), 0, '.', ' ') ?> грн</td>
                        <td>
                            <span class="order-status <?= $statusClass ?>">
                                <?= htmlspecialchars($statusLabels[$status] ?? ($status ?: 'new')) ?>
                            </span>
                        </td>
                        <td>
                            <span class="order-spam <?= $isSpam ? 'order-spam-yes' : 'order-spam-no' ?>">
                                <?= $isSpam ? __('orders.spam_yes') : __('orders.spam_no') ?>
                            </span>
                        </td>
                        <td>
                            <button
                                class="btn-action edit-order-btn"
                                data-id="<?= (int)$order['id'] ?>"
                                data-customer-name="<?= htmlspecialchars((string)($order['customer_name'] ?? ''), ENT_QUOTES) ?>"
                                data-phone="<?= htmlspecialchars((string)($order['phone'] ?? ''), ENT_QUOTES) ?>"
                                data-product-title="<?= htmlspecialchars((string)($order['product_title'] ?? ''), ENT_QUOTES) ?>"
                                data-product-price="<?= htmlspecialchars((string)($order['product_price'] ?? ''), ENT_QUOTES) ?>"
                                data-quantity="<?= htmlspecialchars((string)($order['quantity'] ?? '1'), ENT_QUOTES) ?>"
                                data-total-sum="<?= htmlspecialchars((string)($order['total_sum'] ?? ''), ENT_QUOTES) ?>"
                                data-comment="<?= htmlspecialchars((string)($order['comment'] ?? ''), ENT_QUOTES) ?>"
                                data-payment="<?= htmlspecialchars((string)($order['payment'] ?? ''), ENT_QUOTES) ?>"
                                data-delivery="<?= htmlspecialchars((string)($order['delivery'] ?? ''), ENT_QUOTES) ?>"
                                data-delivery-address="<?= htmlspecialchars((string)($order['delivery_address'] ?? ''), ENT_QUOTES) ?>"
                                data-status="<?= htmlspecialchars((string)($order['status'] ?? 'new'), ENT_QUOTES) ?>"
                            >
                                <?= __('form.edit') ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editOrderModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background: var(--bg-card); border-radius: 12px; padding: 24px; width: min(760px, 92vw); max-height: 90vh; overflow: auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <h2 style="margin:0;"><?= __('orders.edit_title') ?></h2>
            <button class="modal-close" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-secondary);">&times;</button>
        </div>

        <form method="POST" action="/admin/?route=table" id="editOrderForm">
            <input type="hidden" name="id" id="orderId">
            <input type="hidden" name="status_filter" value="<?= htmlspecialchars((string)($statusFilter ?? '')) ?>">

            <div class="order-form-grid">
                <div class="order-form-field">
                    <label><?= __('orders.field_customer') ?></label>
                    <input type="text" name="customer_name" id="orderCustomerName" required>
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_phone') ?></label>
                    <input type="text" name="phone" id="orderPhone" required>
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_product') ?></label>
                    <input type="text" name="product_title" id="orderProductTitle">
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_price') ?></label>
                    <input type="number" step="0.01" min="0" name="product_price" id="orderProductPrice">
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_quantity') ?></label>
                    <input type="number" min="1" name="quantity" id="orderQuantity">
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_total') ?></label>
                    <input type="number" step="0.01" min="0" name="total_sum" id="orderTotalSum">
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_payment') ?></label>
                    <input type="text" name="payment" id="orderPayment">
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_delivery') ?></label>
                    <input type="text" name="delivery" id="orderDelivery">
                </div>
                <div class="order-form-field order-form-field-full">
                    <label><?= __('orders.field_delivery_address') ?></label>
                    <input type="text" name="delivery_address" id="orderDeliveryAddress">
                </div>
                <div class="order-form-field order-form-field-full">
                    <label><?= __('orders.field_comment') ?></label>
                    <textarea name="comment" id="orderComment" rows="3"></textarea>
                </div>
                <div class="order-form-field">
                    <label><?= __('orders.field_status') ?></label>
                    <select name="status" id="orderStatus">
                        <option value="new"><?= __('orders.status_new') ?></option>
                        <option value="processing"><?= __('orders.status_processing') ?></option>
                        <option value="done"><?= __('orders.status_done') ?></option>
                        <option value="cancelled"><?= __('orders.status_cancelled') ?></option>
                        <option value="spam"><?= __('orders.status_spam') ?></option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 18px;">
                <button type="button" class="modal-close btn" style="border:1px solid var(--border-color);"><?= __('form.cancel') ?></button>
                <button type="submit" class="btn" style="background:var(--primary-color); color:#fff; border:none;"><?= __('form.save') ?></button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
    .table-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 24px;
    }

    .table-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
    }

    .table-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: border-color 0.16s ease, transform 0.16s ease, color 0.16s ease, background 0.16s ease;
    }

    .table-filter-chip span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-primary);
        font-size: 12px;
    }

    .table-filter-chip:hover,
    .table-filter-chip.active {
        color: var(--text-primary);
        border-color: var(--primary-color);
        transform: translateY(-1px);
    }

    .table-filter-chip.active {
        background: rgba(59, 130, 246, 0.14);
    }

    .table-scroll {
        overflow-x: auto;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-card);
    }

    #ordersTable,
    table.dataTable,
    table.dataTable.no-footer {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
        border: 0;
        background: var(--bg-card);
    }

    #ordersTable thead th,
    table.dataTable thead th {
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-weight: 600;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        font-size: 13px;
        white-space: nowrap;
        text-transform: uppercase;
    }

    #ordersTable tbody td,
    table.dataTable tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 14px;
        vertical-align: middle;
    }

    .order-status,
    .order-spam {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .order-status-new { background: rgba(59,130,246,0.16); color: #60a5fa; border: 1px solid #60a5fa; }
    .order-status-processing { background: rgba(251,146,60,0.16); color: #fb923c; border: 1px solid #fb923c; }
    .order-status-done { background: rgba(34,197,94,0.16); color: #22c55e; border: 1px solid #22c55e; }
    .order-status-cancelled { background: rgba(239,68,68,0.16); color: #ef4444; border: 1px solid #ef4444; }
    .order-status-spam { background: rgba(168,85,247,0.16); color: #a855f7; border: 1px solid #a855f7; }

    .order-spam-yes { background: rgba(239,68,68,0.16); color: #ef4444; border: 1px solid #ef4444; }
    .order-spam-no { background: rgba(34,197,94,0.16); color: #22c55e; border: 1px solid #22c55e; }
	.edit-order-btn { 
		background: rgba(59,130,246,0.16); 
		color: #ff1010; 
		border: 1px solid #ff1010; 
		display: inline-block;
		padding: 6px 10px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
	}
	.dataTables_length, .dataTables_filter {
		padding: 5px;
		margin: 3px;
		display: block;
	}

    .order-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .order-form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .order-form-field label {
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
    }

    .order-form-field input,
    .order-form-field textarea,
    .order-form-field select {
        width: 100%;
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-primary);
        padding: 10px 12px;
        font-size: 14px;
    }

    .order-form-field-full {
        grid-column: 1 / -1;
    }

    @media (max-width: 900px) {
        .table-card {
            padding: 14px;
        }

        .order-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    (function () {
        if (typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
            console.error('DataTables dependencies are not loaded.');
            return;
        }

        const modal = document.getElementById('editOrderModal');

        document.querySelectorAll('.edit-order-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('orderId').value = btn.dataset.id || '';
                document.getElementById('orderCustomerName').value = btn.dataset.customerName || '';
                document.getElementById('orderPhone').value = btn.dataset.phone || '';
                document.getElementById('orderProductTitle').value = btn.dataset.productTitle || '';
                document.getElementById('orderProductPrice').value = btn.dataset.productPrice || '';
                document.getElementById('orderQuantity').value = btn.dataset.quantity || '1';
                document.getElementById('orderTotalSum').value = btn.dataset.totalSum || '';
                document.getElementById('orderComment').value = btn.dataset.comment || '';
                document.getElementById('orderPayment').value = btn.dataset.payment || '';
                document.getElementById('orderDelivery').value = btn.dataset.delivery || '';
                document.getElementById('orderDeliveryAddress').value = btn.dataset.deliveryAddress || '';
                document.getElementById('orderStatus').value = btn.dataset.status || 'new';

                modal.style.display = 'flex';
            });
        });

        modal?.querySelectorAll('.modal-close').forEach((btn) => {
            btn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        });

        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        jQuery(function ($) {
            $('#ordersTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[1, 'desc']],
                language: {
                    processing: <?= json_encode(__('orders.datatable_processing')) ?>,
                    search: <?= json_encode(__('orders.datatable_search')) ?>,
                    lengthMenu: <?= json_encode(__('orders.datatable_length_menu')) ?>,
                    info: <?= json_encode(__('orders.datatable_info')) ?>,
                    infoEmpty: <?= json_encode(__('orders.datatable_info_empty')) ?>,
                    infoFiltered: <?= json_encode(__('orders.datatable_info_filtered')) ?>,
                    loadingRecords: <?= json_encode(__('orders.datatable_loading_records')) ?>,
                    zeroRecords: <?= json_encode(__('orders.datatable_zero_records')) ?>,
                    emptyTable: <?= json_encode(__('orders.datatable_empty_table')) ?>,
                    paginate: {
                        first: <?= json_encode(__('orders.datatable_first')) ?>,
                        previous: <?= json_encode(__('orders.datatable_previous')) ?>,
                        next: <?= json_encode(__('orders.datatable_next')) ?>,
                        last: <?= json_encode(__('orders.datatable_last')) ?>
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [8] }
                ]
            });
        });
    })();
</script>
