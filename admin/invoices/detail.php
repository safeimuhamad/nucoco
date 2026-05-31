<?php
$page = 'invoices';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$invoice = invoice_load($id);
if (!$invoice) {
    header('Location: ' . admin_url('invoices/?error=not_found'));
    exit;
}

$items = invoice_items($id);
$creator = !empty($invoice['created_by']) ? db_select_one("SELECT name FROM users WHERE id = ? LIMIT 1", 'i', [(int) $invoice['created_by']]) : null;
$sales_name = $creator['name'] ?? '-';
$settings = db_select_one("SELECT company_name FROM web_config LIMIT 1") ?: [];
$payment_account_name = trim($settings['company_name'] ?? '') ?: ($invoice['bank_account_name'] ?: '-');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Invoices', admin_url('invoices/'), 'Invoice Detail', $invoice['invoice_number'], [
        ['label' => 'Print', 'url' => 'print.php?id=' . (int) $invoice['id'], 'icon' => 'print', 'target' => '_blank', 'class' => 'btn detail-btn detail-btn-outline'],
        ['label' => 'Edit', 'url' => 'edit.php?id=' . (int) $invoice['id'], 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => 'delete.php?id=' . (int) $invoice['id'], 'icon' => 'delete', 'confirm' => 'Delete this invoice?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    ?>

    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Invoice <?= htmlspecialchars($_GET['success']) ?> successfully.</div><?php endif; ?>

    <?php
    detail_summary([
        ['label' => 'Customer', 'value' => $invoice['customer_name'], 'icon' => 'person', 'meta' => $invoice['customer_email'] ?: '-'],
        ['label' => 'Quotation', 'value' => $invoice['quote_number'] ?: '-', 'icon' => 'request_quote', 'meta' => $invoice['quote_type'] ? ucfirst($invoice['quote_type']) : '-', 'url' => !empty($invoice['quotation_id']) ? admin_url('quotations/detail.php?id=' . (int) $invoice['quotation_id']) : '', 'link_label' => 'View Quotation'],
        ['label' => 'Invoice Date', 'value' => $invoice['invoice_date'] ?: '-', 'icon' => 'calendar_month', 'meta' => 'Due ' . ($invoice['due_date'] ?: '-')],
        ['label' => 'Sales', 'value' => $sales_name, 'icon' => 'badge', 'meta' => invoice_status_label($invoice['status'])],
    ]);
    ?>

    <div class="row">
        <div class="col-lg-7">
            <?php detail_card_open('Invoice Information', 'description'); ?>
                <?php detail_fields([
                    'Invoice No.' => detail_text($invoice['invoice_number']),
                    'Quotation No.' => detail_text($invoice['quote_number']),
                    'Invoice Date' => detail_text($invoice['invoice_date']),
                    'Due Date' => detail_text($invoice['due_date']),
                    'Currency' => detail_text($invoice['currency']),
                    'Bank Account No.' => detail_text($invoice['bank_account_number']),
                    'Account Holder' => detail_text($payment_account_name),
                    'Bank Branch' => detail_text($invoice['bank_branch']),
                    'Sales Representative' => detail_text($sales_name),
                    'Customer Phone' => detail_text($invoice['customer_phone']),
                    'Notes' => nl2br(detail_text($invoice['notes'])),
                ]); ?>
            <?php detail_card_close(); ?>
        </div>

        <div class="col-lg-5">
            <?php detail_card_open('Invoice Summary', 'receipt_long'); ?>
                <p class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong><?= quotation_format_money($invoice['subtotal'], $invoice['currency']) ?></strong></p>
                <p class="d-flex justify-content-between mb-2"><span>Discount</span><strong><?= quotation_format_money($invoice['discount'], $invoice['currency']) ?></strong></p>
                <p class="d-flex justify-content-between mb-2"><span>Tax</span><strong><?= quotation_format_money($invoice['tax'], $invoice['currency']) ?></strong></p>
                <hr>
                <p class="d-flex justify-content-between fs-18 mb-0"><span>Invoice Total</span><strong class="text-primary"><?= quotation_format_money($invoice['grand_total'], $invoice['currency']) ?></strong></p>
            <?php detail_card_close(); ?>

            <?php detail_card_open('Documents', 'text_snippet'); ?>
                <div class="detail-doc-row">
                    <div>
                        <strong><?= htmlspecialchars($invoice['invoice_number']) ?>.pdf</strong>
                        <div class="text-secondary">Invoice PDF</div>
                    </div>
                    <a href="print.php?id=<?= (int) $invoice['id'] ?>" target="_blank" class="btn detail-btn detail-btn-outline">
                        <span class="material-symbols-outlined">download</span>
                    </a>
                </div>
            <?php detail_card_close(); ?>
        </div>
    </div>

    <div class="detail-card">
        <h4 class="fs-17 fw-bold mb-3">Products / Services</h4>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>No.</th><th>Product / Service</th><th>Description</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?><br><span class="text-secondary"><?= htmlspecialchars(ucfirst($item['item_type'])) ?></span></td>
                            <td><?= htmlspecialchars($item['description'] ?: '-') ?></td>
                            <td><?= quotation_format_quantity($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td><?= quotation_format_money($item['unit_price'], $invoice['currency']) ?></td>
                            <td><?= quotation_format_money($item['total'], $invoice['currency']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="detail-total-bar">
            <span>Invoice Total</span>
            <strong><?= quotation_format_money($invoice['grand_total'], $invoice['currency']) ?></strong>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
