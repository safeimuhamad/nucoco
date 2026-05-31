<?php
$page = 'quotations';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../invoices/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$quote = quotation_load($id);
if (!$quote) {
    header('Location: ' . admin_url('quotations/?error=not_found'));
    exit;
}
$items = quotation_items($id);
$lead = $quote['lead_id'] ? db_select_one("SELECT * FROM leads WHERE id = ? LIMIT 1", 'i', [(int) $quote['lead_id']]) : null;
$creator = !empty($quote['created_by']) ? db_select_one("SELECT name FROM users WHERE id = ? LIMIT 1", 'i', [(int) $quote['created_by']]) : null;
$sales_name = $creator['name'] ?? '-';
$quote_date = $quote['quote_date'] ?? $quote['created_at'] ?? null;
$existing_invoice = invoice_existing_for_quotation((int) $quote['id']);
$can_create_invoice = invoice_can_create_from_quotation($quote);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <?php
    $actions = [];
    if ($can_create_invoice) {
        $actions[] = [
            'label' => 'Create Invoice',
            'url' => '#',
            'icon' => 'receipt_long',
            'class' => 'btn detail-btn detail-btn-primary',
            'attrs' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#createInvoiceModal',
            ],
        ];
    } elseif ($existing_invoice) {
        $actions[] = [
            'label' => 'View Invoice',
            'url' => admin_url('invoices/detail.php?id=' . (int) $existing_invoice['id']),
            'icon' => 'receipt_long',
            'class' => 'btn detail-btn detail-btn-outline',
        ];
    }
    $actions = array_merge($actions, [
        ['label' => 'Print', 'url' => 'print.php?id=' . (int) $quote['id'], 'icon' => 'print', 'target' => '_blank', 'class' => 'btn detail-btn detail-btn-outline'],
        ['label' => 'Edit', 'url' => 'edit.php?id=' . (int) $quote['id'], 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => 'delete.php?id=' . (int) $quote['id'], 'icon' => 'delete', 'confirm' => 'Delete this quotation?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);

    detail_page_header('Quotations', admin_url('quotations/'), 'Quotation Detail', $quote['quote_number'], $actions);
    ?>

    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Quotation <?= htmlspecialchars($_GET['success']) ?> successfully.</div><?php endif; ?>

    <?php
    detail_summary([
        ['label' => 'Customer', 'value' => $quote['customer_name'], 'icon' => 'person', 'meta' => $quote['customer_email'] ?: '-', 'url' => $lead ? admin_url('leads/detail.php?id=' . (int) $lead['id']) : '', 'link_label' => $lead ? 'View Lead' : ''],
        ['label' => 'Type', 'value' => ucfirst($quote['quote_type']), 'icon' => 'public', 'meta' => $quote['currency']],
        ['label' => 'Date', 'value' => $quote_date ?: '-', 'icon' => 'calendar_month', 'meta' => 'Valid until ' . ($quote['valid_until'] ?: '-')],
        ['label' => 'Sales', 'value' => $sales_name, 'icon' => 'badge', 'meta' => ucfirst($quote['status'])],
    ]);
    ?>

    <div class="row">
        <div class="col-lg-7">
            <?php detail_card_open('Quotation Information', 'description'); ?>
                <?php detail_fields([
                    'Quotation No.' => detail_text($quote['quote_number']),
                    'Validity Period' => detail_text(($quote_date ?: '-') . ' - ' . ($quote['valid_until'] ?: '-')),
                    'Currency' => detail_text($quote['currency']),
                    'Quotation Type' => detail_text(ucfirst($quote['quote_type'])),
                    'Sales Representative' => detail_text($sales_name),
                    'Customer Email' => detail_text($quote['customer_email']),
                    'Customer Phone' => detail_text($quote['customer_phone']),
                    'Notes' => nl2br(detail_text($quote['notes'])),
                ]); ?>
            <?php detail_card_close(); ?>
        </div>

        <div class="col-lg-5">
            <?php detail_card_open('Quotation Summary', 'receipt_long'); ?>
                <p class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong><?= quotation_format_money($quote['subtotal'], $quote['currency']) ?></strong></p>
                <p class="d-flex justify-content-between mb-2"><span>Discount</span><strong><?= quotation_format_money($quote['discount'], $quote['currency']) ?></strong></p>
                <p class="d-flex justify-content-between mb-2"><span>Tax</span><strong><?= quotation_format_money($quote['tax'], $quote['currency']) ?></strong></p>
                <hr>
                <p class="d-flex justify-content-between fs-18 mb-0"><span>Quotation Total</span><strong class="text-primary"><?= quotation_format_money($quote['grand_total'], $quote['currency']) ?></strong></p>
            <?php detail_card_close(); ?>

            <?php detail_card_open('Documents', 'text_snippet'); ?>
                <div class="detail-doc-row">
                    <div>
                        <strong><?= htmlspecialchars($quote['quote_number']) ?>.pdf</strong>
                        <div class="text-secondary">Quotation PDF</div>
                    </div>
                    <a href="print.php?id=<?= (int) $quote['id'] ?>" target="_blank" class="btn detail-btn detail-btn-outline">
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
                            <td><?= quotation_format_money($item['unit_price'], $quote['currency']) ?></td>
                            <td><?= quotation_format_money($item['total'], $quote['currency']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="detail-total-bar">
            <span>Quotation Total</span>
            <strong><?= quotation_format_money($quote['grand_total'], $quote['currency']) ?></strong>
        </div>
    </div>
</div>

<?php if ($can_create_invoice): ?>
    <div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-10 border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="createInvoiceModalLabel">Create Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Create an invoice from quotation <strong><?= htmlspecialchars($quote['quote_number']) ?></strong>? All quotation items and totals will be copied into the invoice form.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="<?= admin_url('invoices/create.php?quotation_id=' . (int) $quote['id']) ?>" class="btn btn-primary text-white">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <span>Continue</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
