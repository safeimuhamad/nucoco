<?php

require_once __DIR__ . '/../quotations/helpers.php';

function invoice_next_number($conn)
{
    $prefix = 'INV-' . date('Ym') . '-';
    $row = db_select_one(
        "SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1",
        's',
        [$prefix . '%']
    );

    $next = 1;
    if (!empty($row['invoice_number'])) {
        $last = (int) substr($row['invoice_number'], -4);
        $next = $last + 1;
    }

    return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function invoice_load($id)
{
    return db_select_one(
        "SELECT i.*, q.quote_number, q.quote_type
         FROM invoices i
         LEFT JOIN quotations q ON q.id = i.quotation_id
         WHERE i.id = ? LIMIT 1",
        'i',
        [(int) $id]
    );
}

function invoice_items($invoice_id)
{
    return db_select_all(
        "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order ASC, id ASC",
        'i',
        [(int) $invoice_id]
    );
}

function invoice_existing_for_quotation($quotation_id)
{
    return db_select_one(
        "SELECT id, invoice_number FROM invoices WHERE quotation_id = ? ORDER BY id DESC LIMIT 1",
        'i',
        [(int) $quotation_id]
    );
}

function invoice_can_create_from_quotation(array $quote): bool
{
    if (empty($quote['id'])) {
        return false;
    }

    if (invoice_existing_for_quotation((int) $quote['id'])) {
        return false;
    }

    return in_array($quote['status'] ?? '', ['draft', 'sent', 'accepted'], true);
}

function normalize_invoice_items_from_post()
{
    $items = [];
    $posted = $_POST['items'] ?? [];

    foreach ($posted as $item) {
        $name = trim($item['item_name'] ?? '');
        if ($name === '') {
            continue;
        }

        $items[] = [
            'item_type' => in_array($item['item_type'] ?? '', ['product', 'service'], true) ? $item['item_type'] : 'product',
            'reference_id' => !empty($item['reference_id']) ? (int) $item['reference_id'] : null,
            'item_name' => $name,
            'description' => trim($item['description'] ?? ''),
            'quantity' => quotation_parse_number($item['quantity'] ?? 1),
            'unit' => trim($item['unit'] ?? ''),
            'unit_price' => quotation_parse_number($item['unit_price'] ?? 0),
        ];
    }

    return $items;
}

function sync_invoice_items($invoice_id, array $items)
{
    db_delete("DELETE FROM invoice_items WHERE invoice_id = ?", 'i', [(int) $invoice_id]);

    $sort = 1;
    $subtotal = 0;

    foreach ($items as $item) {
        $name = trim($item['item_name'] ?? '');
        if ($name === '') {
            continue;
        }

        $quantity = max(0, quotation_parse_number($item['quantity'] ?? 1));
        $unit_price = max(0, quotation_parse_number($item['unit_price'] ?? 0));
        $total = $quantity * $unit_price;
        $subtotal += $total;

        db_insert(
            "INSERT INTO invoice_items
             (invoice_id, item_type, reference_id, item_name, description, quantity, unit, unit_price, total, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'isissdsddi',
            [
                (int) $invoice_id,
                $item['item_type'] ?? 'product',
                !empty($item['reference_id']) ? (int) $item['reference_id'] : null,
                $name,
                trim($item['description'] ?? ''),
                $quantity,
                trim($item['unit'] ?? ''),
                $unit_price,
                $total,
                $sort++,
            ]
        );
    }

    return $subtotal;
}

function invoice_status_label($status)
{
    $labels = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
    ];

    return $labels[$status] ?? ucfirst((string) $status);
}
