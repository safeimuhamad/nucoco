<?php

if (!function_exists('quotation_next_number')) {
    function quotation_next_number($conn)
    {
        $prefix = 'QTN-' . date('Ym') . '-';
        $row = db_select_one(
            "SELECT quote_number FROM quotations WHERE quote_number LIKE ? ORDER BY id DESC LIMIT 1",
            's',
            [$prefix . '%']
        );

        $next = 1;
        if (!empty($row['quote_number'])) {
            $last = (int) substr($row['quote_number'], -4);
            $next = $last + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('quotation_currency')) {
    function quotation_currency($quote_type)
    {
        return $quote_type === 'international' ? 'USD' : 'IDR';
    }
}

if (!function_exists('quotation_product_language')) {
    function quotation_product_language($quote_type)
    {
        return $quote_type === 'international' ? 'en' : 'id';
    }
}

if (!function_exists('quotation_format_plain_number')) {
    function quotation_format_plain_number($amount)
    {
        $formatted = number_format((float) $amount, 2, '.', ',');
        return rtrim(rtrim($formatted, '0'), '.');
    }
}

if (!function_exists('quotation_format_money')) {
    function quotation_format_money($amount, $currency)
    {
        $prefix = $currency === 'USD' ? 'USD ' : 'Rp ';
        return $prefix . quotation_format_plain_number($amount);
    }
}

if (!function_exists('quotation_format_quantity')) {
    function quotation_format_quantity($amount)
    {
        return quotation_format_plain_number($amount);
    }
}

if (!function_exists('quotation_parse_number')) {
    function quotation_parse_number($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.\-]/', '', $value);
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value);
        } elseif (str_contains($value, '.')) {
            $parts = explode('.', $value);
            if (strlen(end($parts)) === 3) {
                $value = str_replace('.', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }
}

if (!function_exists('quotation_load')) {
    function quotation_load($id)
    {
        return db_select_one("SELECT * FROM quotations WHERE id = ? LIMIT 1", 'i', [(int) $id]);
    }
}

if (!function_exists('quotation_items')) {
    function quotation_items($quotation_id)
    {
        return db_select_all(
            "SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY sort_order ASC, id ASC",
            'i',
            [(int) $quotation_id]
        );
    }
}

if (!function_exists('ensure_lead_from_inquiry')) {
    function ensure_lead_from_inquiry($conn, $inquiry, $user_id = null)
    {
        if (empty($inquiry)) {
            return null;
        }

        $lead = null;
        if (!empty($inquiry['id'])) {
            $lead = db_select_one("SELECT * FROM leads WHERE inquiry_id = ? LIMIT 1", 'i', [(int) $inquiry['id']]);
        }

        if (!$lead && !empty($inquiry['email'])) {
            $lead = db_select_one("SELECT * FROM leads WHERE email = ? ORDER BY id DESC LIMIT 1", 's', [$inquiry['email']]);
        }

        if ($lead) {
            if (!empty($inquiry['id']) && empty($lead['inquiry_id'])) {
                db_update("UPDATE leads SET inquiry_id = ?, updated_by = ? WHERE id = ?", 'iii', [(int) $inquiry['id'], (int) $user_id, (int) $lead['id']]);
            }

            return (int) $lead['id'];
        }

        return db_insert(
            "INSERT INTO leads (inquiry_id, name, email, phone, source, interest_type, message, status, created_by)
             VALUES (?, ?, ?, ?, 'inquiry', ?, ?, 'proposal', ?)",
            'isssssi',
            [
                (int) $inquiry['id'],
                $inquiry['name'] ?? '',
                $inquiry['email'] ?? '',
                $inquiry['phone'] ?? '',
                $inquiry['service_type'] ?? '',
                $inquiry['message'] ?? '',
                (int) $user_id,
            ]
        );
    }
}

if (!function_exists('sync_quotation_items')) {
    function sync_quotation_items($quotation_id, array $items)
    {
        db_delete("DELETE FROM quotation_items WHERE quotation_id = ?", 'i', [(int) $quotation_id]);

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
                "INSERT INTO quotation_items
                 (quotation_id, item_type, reference_id, item_name, description, quantity, unit, unit_price, total, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                'isissdsddi',
                [
                    (int) $quotation_id,
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
}

if (!function_exists('normalize_quotation_items_from_post')) {
    function normalize_quotation_items_from_post()
    {
        $items = [];
        $posted = $_POST['items'] ?? [];

        foreach ($posted as $item) {
            $catalog_key = trim($item['catalog_key'] ?? '');
            $item_type = in_array($item['item_type'] ?? '', ['product', 'service'], true) ? $item['item_type'] : 'product';
            $reference_id = (int) ($item['reference_id'] ?? 0);

            if ($catalog_key !== '' && str_contains($catalog_key, ':')) {
                [$catalog_type, $catalog_id] = explode(':', $catalog_key, 2);
                if (in_array($catalog_type, ['product', 'service'], true)) {
                    $item_type = $catalog_type;
                    $reference_id = (int) $catalog_id;
                }
            }

            $items[] = [
                'item_type' => $item_type,
                'reference_id' => $reference_id,
                'item_name' => trim($item['item_name'] ?? ''),
                'description' => trim($item['description'] ?? ''),
                'quantity' => quotation_parse_number($item['quantity'] ?? 1),
                'unit' => trim($item['unit'] ?? ''),
                'unit_price' => quotation_parse_number($item['unit_price'] ?? 0),
            ];
        }

        return $items;
    }
}
