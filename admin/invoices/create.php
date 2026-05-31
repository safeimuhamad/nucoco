<?php
$page = 'invoices';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/helpers.php';

$error = '';
$mode = 'create';
$quotation_id = isset($_GET['quotation_id']) ? (int) $_GET['quotation_id'] : (int) ($_POST['quotation_id'] ?? 0);
$source_quote = $quotation_id > 0 ? quotation_load($quotation_id) : null;

if (!$source_quote) {
    header('Location: ' . admin_url('quotations/?error=quotation_required'));
    exit;
}

$existing_invoice = invoice_existing_for_quotation($quotation_id);
if ($existing_invoice) {
    header('Location: ' . admin_url('invoices/detail.php?id=' . (int) $existing_invoice['id']));
    exit;
}

$quote_items = quotation_items($quotation_id);
$settings = db_select_one("SELECT company_name FROM web_config LIMIT 1") ?: [];
$default_account_name = trim($settings['company_name'] ?? '');
$invoice = [
    'quotation_id' => $source_quote['id'],
    'lead_id' => $source_quote['lead_id'],
    'customer_name' => $_POST['customer_name'] ?? $source_quote['customer_name'],
    'customer_email' => $_POST['customer_email'] ?? $source_quote['customer_email'],
    'customer_phone' => $_POST['customer_phone'] ?? $source_quote['customer_phone'],
    'customer_company' => $_POST['customer_company'] ?? $source_quote['customer_company'],
    'currency' => $_POST['currency'] ?? $source_quote['currency'],
    'status' => $_POST['status'] ?? 'draft',
    'invoice_date' => $_POST['invoice_date'] ?? date('Y-m-d'),
    'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
    'bank_account_number' => $_POST['bank_account_number'] ?? '',
    'bank_account_name' => $_POST['bank_account_name'] ?? $default_account_name,
    'bank_branch' => $_POST['bank_branch'] ?? '',
    'notes' => $_POST['notes'] ?? $source_quote['notes'],
    'discount' => $_POST['discount'] ?? $source_quote['discount'],
    'tax' => $_POST['tax'] ?? $source_quote['tax'],
];
$items = $_POST['items'] ?? $quote_items;
if (!$items) {
    $items = [
        ['item_type' => 'product', 'reference_id' => null, 'item_name' => '', 'description' => '', 'quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $invoice['customer_name'] = trim($invoice['customer_name']);
    $invoice['customer_email'] = trim($invoice['customer_email']);
    $allowed_status = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

    if ($invoice['customer_name'] === '') {
        $error = 'Customer name is required.';
    } elseif ($invoice['customer_email'] !== '' && !filter_var($invoice['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid customer email.';
    } elseif (!in_array($invoice['status'], $allowed_status, true)) {
        $error = 'Invalid invoice status.';
    } else {
        $invoice_number = invoice_next_number($conn);
        $invoice_id = db_insert(
            "INSERT INTO invoices
             (invoice_number, quotation_id, lead_id, customer_name, customer_email, customer_phone, customer_company, currency, status, invoice_date, due_date, bank_account_number, bank_account_name, bank_branch, notes, discount, tax, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'siissssssssssssddi',
            [
                $invoice_number,
                $quotation_id,
                !empty($_POST['lead_id']) ? (int) $_POST['lead_id'] : null,
                $invoice['customer_name'],
                $invoice['customer_email'],
                trim($invoice['customer_phone'] ?? ''),
                trim($invoice['customer_company'] ?? ''),
                $invoice['currency'],
                $invoice['status'],
                $invoice['invoice_date'] ?: null,
                $invoice['due_date'] ?: null,
                trim($invoice['bank_account_number'] ?? ''),
                trim($invoice['bank_account_name'] ?? ''),
                trim($invoice['bank_branch'] ?? ''),
                trim($invoice['notes'] ?? ''),
                quotation_parse_number($invoice['discount'] ?? 0),
                quotation_parse_number($invoice['tax'] ?? 0),
                (int) current_user_id(),
            ]
        );

        $normalized_items = normalize_invoice_items_from_post();
        $subtotal = sync_invoice_items($invoice_id, $normalized_items);
        $discount = quotation_parse_number($invoice['discount'] ?? 0);
        $tax = quotation_parse_number($invoice['tax'] ?? 0);
        $grand_total = max(0, $subtotal - $discount + $tax);

        db_update(
            "UPDATE invoices SET subtotal = ?, grand_total = ? WHERE id = ?",
            'ddi',
            [$subtotal, $grand_total, $invoice_id]
        );

        header('Location: ' . admin_url('invoices/detail.php?id=' . $invoice_id . '&success=created'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/form.php';
include __DIR__ . '/../includes/footer.php';
