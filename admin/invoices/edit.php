<?php
$page = 'invoices';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/helpers.php';

$error = '';
$mode = 'edit';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$invoice = invoice_load($id);
if (!$invoice) {
    header('Location: ' . admin_url('invoices/?error=not_found'));
    exit;
}

$source_quote = !empty($invoice['quotation_id']) ? quotation_load((int) $invoice['quotation_id']) : null;
$items = $_POST['items'] ?? invoice_items($id);
if (!$items) {
    $items = [
        ['item_type' => 'product', 'reference_id' => null, 'item_name' => '', 'description' => '', 'quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $invoice['customer_name'] = trim($_POST['customer_name'] ?? '');
    $invoice['customer_email'] = trim($_POST['customer_email'] ?? '');
    $invoice['customer_phone'] = trim($_POST['customer_phone'] ?? '');
    $invoice['customer_company'] = trim($_POST['customer_company'] ?? '');
    $invoice['customer_address'] = trim($_POST['customer_address'] ?? '');
    $invoice['currency'] = trim($_POST['currency'] ?? $invoice['currency']);
    $invoice['status'] = $_POST['status'] ?? 'draft';
    $invoice['invoice_date'] = $_POST['invoice_date'] ?? null;
    $invoice['due_date'] = $_POST['due_date'] ?? null;
    $invoice['bank_account_number'] = trim($_POST['bank_account_number'] ?? '');
    $invoice['bank_account_name'] = trim($_POST['bank_account_name'] ?? '');
    $invoice['bank_branch'] = trim($_POST['bank_branch'] ?? '');
    $invoice['notes'] = trim($_POST['notes'] ?? '');
    $invoice['discount'] = quotation_parse_number($_POST['discount'] ?? 0);
    $invoice['tax'] = quotation_parse_number($_POST['tax'] ?? 0);
    $allowed_status = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

    if ($invoice['customer_name'] === '') {
        $error = 'Customer name is required.';
    } elseif ($invoice['customer_email'] !== '' && !filter_var($invoice['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid customer email.';
    } elseif (!in_array($invoice['status'], $allowed_status, true)) {
        $error = 'Invalid invoice status.';
    } else {
        db_update(
            "UPDATE invoices
             SET customer_name = ?, customer_email = ?, customer_phone = ?, customer_company = ?, customer_address = ?, currency = ?, status = ?, invoice_date = ?, due_date = ?, bank_account_number = ?, bank_account_name = ?, bank_branch = ?, notes = ?, discount = ?, tax = ?, updated_by = ?
             WHERE id = ?",
            'sssssssssssssddii',
            [
                $invoice['customer_name'],
                $invoice['customer_email'],
                $invoice['customer_phone'],
                $invoice['customer_company'],
                $invoice['customer_address'],
                $invoice['currency'],
                $invoice['status'],
                $invoice['invoice_date'] ?: null,
                $invoice['due_date'] ?: null,
                $invoice['bank_account_number'],
                $invoice['bank_account_name'],
                $invoice['bank_branch'],
                $invoice['notes'],
                $invoice['discount'],
                $invoice['tax'],
                (int) current_user_id(),
                $id,
            ]
        );

        $normalized_items = normalize_invoice_items_from_post();
        $subtotal = sync_invoice_items($id, $normalized_items);
        $grand_total = max(0, $subtotal - $invoice['discount'] + $invoice['tax']);
        db_update(
            "UPDATE invoices SET subtotal = ?, grand_total = ? WHERE id = ?",
            'ddi',
            [$subtotal, $grand_total, $id]
        );

        header('Location: ' . admin_url('invoices/detail.php?id=' . $id . '&success=updated'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/form.php';
include __DIR__ . '/../includes/footer.php';
