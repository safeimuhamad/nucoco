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

if (!function_exists('quotation_catalog_description')) {
    function quotation_catalog_description($name, $quotation_description = '', $language = 'en', $type = 'product')
    {
        $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $quotation_description)));
        if ($description !== '') {
            return $description;
        }

        if (function_exists('product_default_quotation_description')) {
            return product_default_quotation_description($name, $language, $type);
        }

        $name = trim((string) $name);
        return 'Standard specification for ' . ($name !== '' ? $name : 'item') . '. Suitable for quotation requirements.';
    }
}

if (!function_exists('nucoco_customer_address_fallback')) {
    function nucoco_customer_address_fallback($customer_name, $customer_email = '')
    {
        $name = strtolower(trim((string) $customer_name));
        $email = strtolower(trim((string) $customer_email));
        $haystack = $name . ' ' . $email;

        $addresses = [
            'pt teknologi nusantara' => 'Jl. Teknologi Nusantara No. 88, Jakarta 11520, Indonesia',
            'global pacific foods' => '18 Marina Boulevard, Singapore 018980',
            'pt sinar abadi' => 'Jl. Jend. Sudirman No. 100, Jakarta 10220, Indonesia',
            'cv maju bersama' => 'Jl. Gatot Subroto No. 18, Bandung 40262, Indonesia',
            'pt global teknologi' => 'Jl. HR Rasuna Said Kav. 12, Jakarta 12940, Indonesia',
            'pt cahaya mandiri' => 'Jl. Diponegoro No. 45, Surabaya 60264, Indonesia',
            'yayasan pendidikan nusantara' => 'Jl. Pendidikan No. 8, Yogyakarta 55281, Indonesia',
            'pt lestari food' => 'Jl. Industri Raya No. 22, Tangerang 15135, Indonesia',
            'cv agro makmur' => 'Jl. Agro Makmur No. 15, Bogor 16143, Indonesia',
            'pt rumah organik' => 'Jl. Organik Raya No. 9, Depok 16431, Indonesia',
            'pacific foods ltd' => 'Level 12, Menara Pacific, Kuala Lumpur 50450, Malaysia',
            'pt fresh market' => 'Jl. Fresh Market No. 21, Bekasi 17113, Indonesia',
            'pt nusantara retail' => 'Jl. Nusantara Retail No. 77, Semarang 50134, Indonesia',
            'cv berkah solusi' => 'Jl. Berkah Solusi No. 31, Malang 65141, Indonesia',
            'coconut trading pte ltd' => '10 Anson Road, International Plaza, Singapore 079903',
            'pt makmur sentosa' => 'Jl. Makmur Sentosa No. 16, Medan 20112, Indonesia',
            'cv cipta karya' => 'Jl. Cipta Karya No. 27, Pekanbaru 28125, Indonesia',
            'pt berkah solusi' => 'Jl. Berkah Solusi Timur No. 4, Surabaya 60293, Indonesia',
            'pt alam sejahtera' => 'Jl. Alam Sejahtera No. 19, Makassar 90231, Indonesia',
            'asia coconut export ltd' => '25 North Bridge Road, Singapore 179104',
        ];

        foreach ($addresses as $needle => $address) {
            if (str_contains($haystack, $needle)) {
                return $address;
            }
        }

        return '';
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
            "INSERT INTO leads (inquiry_id, name, email, phone, address, source, interest_type, message, status, created_by)
             VALUES (?, ?, ?, ?, ?, 'inquiry', ?, ?, 'proposal', ?)",
            'issssssi',
            [
                (int) $inquiry['id'],
                $inquiry['name'] ?? '',
                $inquiry['email'] ?? '',
                $inquiry['phone'] ?? '',
                $inquiry['address'] ?? '',
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
