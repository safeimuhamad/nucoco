<?php
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$quote = quotation_load($id);
if (!$quote) {
    exit('Quotation not found.');
}

$items = quotation_items($id);
$base_url = site_base_url();
$is_international = ($quote['quote_type'] ?? 'local') === 'international';
$creator = !empty($quote['created_by'])
    ? db_select_one("SELECT name FROM users WHERE id = ? LIMIT 1", 'i', [(int) $quote['created_by']])
    : null;
$web_config = db_select_one("SELECT company_name, email, phone, whatsapp, address, instagram_url FROM web_config LIMIT 1") ?: [];

function print_money($amount, $currency, $english = false)
{
    if ($english) {
        $prefix = $currency === 'USD' ? 'USD ' : $currency . ' ';
        return $prefix . number_format((float) $amount, 0, '.', ',');
    }

    $prefix = $currency === 'USD' ? 'USD ' : 'Rp ';
    return $prefix . number_format((float) $amount, 0, ',', '.');
}

function print_date_id($date)
{
    if (!$date) {
        return '-';
    }

    $months = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
    ];
    $time = strtotime($date);
    return date('d', $time) . ' ' . $months[date('m', $time)] . ' ' . date('Y', $time);
}

function print_date_en($date)
{
    return $date ? date('d F Y', strtotime($date)) : '-';
}

function print_number_words_id($number)
{
    $number = (int) round($number);
    $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    if ($number < 12) {
        return $words[$number];
    }
    if ($number < 20) {
        return $words[$number - 10] . ' belas';
    }
    if ($number < 100) {
        return trim($words[(int) floor($number / 10)] . ' puluh ' . $words[$number % 10]);
    }
    if ($number < 200) {
        return trim('seratus ' . print_number_words_id($number - 100));
    }
    if ($number < 1000) {
        return trim($words[(int) floor($number / 100)] . ' ratus ' . print_number_words_id($number % 100));
    }
    if ($number < 2000) {
        return trim('seribu ' . print_number_words_id($number - 1000));
    }
    if ($number < 1000000) {
        return trim(print_number_words_id((int) floor($number / 1000)) . ' ribu ' . print_number_words_id($number % 1000));
    }
    if ($number < 1000000000) {
        return trim(print_number_words_id((int) floor($number / 1000000)) . ' juta ' . print_number_words_id($number % 1000000));
    }
    return trim(print_number_words_id((int) floor($number / 1000000000)) . ' miliar ' . print_number_words_id($number % 1000000000));
}

function print_number_words_en($number)
{
    $number = (int) round($number);
    $words = [
        0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
        6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
        11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
        15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen',
    ];
    $tens = [2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'];

    if ($number < 20) {
        return $words[$number];
    }
    if ($number < 100) {
        return trim($tens[(int) floor($number / 10)] . ' ' . $words[$number % 10]);
    }
    if ($number < 1000) {
        return trim($words[(int) floor($number / 100)] . ' hundred ' . print_number_words_en($number % 100));
    }
    if ($number < 1000000) {
        return trim(print_number_words_en((int) floor($number / 1000)) . ' thousand ' . print_number_words_en($number % 1000));
    }
    if ($number < 1000000000) {
        return trim(print_number_words_en((int) floor($number / 1000000)) . ' million ' . print_number_words_en($number % 1000000));
    }
    return trim(print_number_words_en((int) floor($number / 1000000000)) . ' billion ' . print_number_words_en($number % 1000000000));
}

$quote_date = $quote['quote_date'] ?? $quote['created_at'] ?? date('Y-m-d');
$valid_until = $quote['valid_until'] ?? null;
$valid_days = $valid_until ? max(1, (int) ceil((strtotime($valid_until) - strtotime($quote_date)) / 86400)) : 30;
$sales_name = $creator['name'] ?? ($_SESSION['name'] ?? 'Sales Nucoco');
$project = $quote['customer_company'] ?: ($quote['notes'] ? strtok($quote['notes'], "\n") : ($is_international ? 'Product / Service Supply' : 'Penawaran Produk / Layanan'));
$customer_title = $quote['customer_name'];
$customer_address = trim($quote['customer_address'] ?? '');
if ($customer_address === '' && !empty($quote['lead_id'])) {
    $lead_address = db_select_one("SELECT address FROM leads WHERE id = ? LIMIT 1", 'i', [(int) $quote['lead_id']]);
    $customer_address = trim($lead_address['address'] ?? '');
}
if ($customer_address === '') {
    $customer_address = nucoco_customer_address_fallback($quote['customer_name'] ?? '', $quote['customer_email'] ?? '');
}
$company_name = trim($web_config['company_name'] ?? '') ?: 'NUCOCO';
$company_email = trim($web_config['email'] ?? '') ?: 'sales@nucoco.com';
$company_phone = trim($web_config['phone'] ?? '') ?: (trim($web_config['whatsapp'] ?? '') ?: '-');
$company_address = trim($web_config['address'] ?? '') ?: '-';
$company_instagram = trim($web_config['instagram_url'] ?? '');
$company_instagram_label = $company_instagram !== '' && $company_instagram !== '-'
    ? preg_replace('#^https?://(www\.)?instagram\.com/#', '@', rtrim($company_instagram, '/'))
    : '';
$notes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($quote['notes'] ?? ''))));
if (!$notes) {
    $notes = $is_international
        ? [
            'Prices include installation services',
            'Prices exclude shipping costs outside the city',
            'This quotation is valid for ' . $valid_days . ' days from the issue date',
            'Product availability is subject to stock at the time of Purchase Order confirmation',
        ]
        : [
            'Harga sudah termasuk instalasi',
            'Harga belum termasuk biaya ekspedisi luar kota',
            'Penawaran berlaku ' . $valid_days . ' hari sejak diterbitkan',
            'Barang tersedia sesuai stok saat PO diterima',
        ];
}

$doc_title = $is_international ? 'QUOTATION' : 'PENAWARAN';
$date_text = $is_international ? print_date_en($quote_date) : print_date_id($quote_date);
$valid_text = ($is_international ? print_date_en($valid_until) : print_date_id($valid_until));
$valid_period_text = $date_text . ' - ' . $valid_text . ' (' . $valid_days . ' ' . ($is_international ? 'Days' : 'Hari') . ')';
$currency_label = $quote['currency'] . ($quote['currency'] === 'IDR' ? ($is_international ? ' - Indonesian Rupiah' : ' - Rupiah') : '');
$amount_words = $is_international
    ? ucwords(print_number_words_en($quote['grand_total'])) . ($quote['currency'] === 'USD' ? ' Dollars Only' : ' Rupiah Only')
    : ucfirst(print_number_words_id($quote['grand_total'])) . ' rupiah';

function print_quotation_item_description($item, $is_international)
{
    $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($item['description'] ?? ''))));
    if ($description !== '' && strlen($description) <= 160) {
        return $description;
    }

    return product_default_quotation_description(
        $item['item_name'] ?? '',
        $is_international ? 'en' : 'id',
        ($item['item_type'] ?? 'product') === 'service' ? 'service' : 'product'
    );
}
?>
<!doctype html>
<html lang="<?= $is_international ? 'en' : 'id' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($quote['quote_number']) ?></title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef2ef;
            color: #101828;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 10;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: #08751f;
            font-weight: 700;
            cursor: pointer;
        }
        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            overflow: hidden;
            background: #fff;
        }
        .content { position: relative; z-index: 1; }
        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12mm;
            align-items: start;
            margin-bottom: 8mm;
            padding-bottom: 4mm;
            border-bottom: 1.2px solid #08751f;
        }
        .logo { width: 52mm; max-height: 24mm; object-fit: contain; object-position: left center; }
        .doc-title { text-align: right; color: #06631c; }
        .doc-title h1 {
            margin: 0 0 3mm;
            font-size: 34pt;
            line-height: 1;
            letter-spacing: 0;
        }
        .doc-number {
            display: inline-block;
            min-width: 54mm;
            padding: 2.4mm 7mm;
            border-radius: 6px;
            color: #fff;
            background: linear-gradient(180deg, #0b8d28, #05651b);
            text-align: center;
            font-size: 16pt;
            font-weight: 800;
        }
        .intro-grid {
            display: grid;
            grid-template-columns: .9fr 1fr;
            gap: 9mm;
            margin-bottom: 9mm;
        }
        .customer-card,
        .note-card,
        .total-card,
        .info-card,
        .sign-card {
            border: 1px solid #cddfcb;
            border-radius: 8px;
            background: rgba(249, 253, 247, .82);
        }
        .customer-card {
            min-height: 48mm;
            padding: 5mm;
        }
        .label-muted { color: #475467; font-size: 11pt; font-weight: 700; }
        .customer-card h2 {
            margin: 3mm 0 4mm;
            color: #08751f;
            font-size: 18pt;
        }
        .contact-line {
            display: flex;
            gap: 4mm;
            align-items: flex-start;
            margin: 2.5mm 0;
            font-size: 10.5pt;
            line-height: 1.45;
        }
        .icon {
            width: 5mm;
            height: 5mm;
            color: #08751f;
            flex: 0 0 auto;
        }
        .quote-meta {
            display: grid;
            gap: 3.2mm;
            padding-top: 2mm;
            font-size: 11pt;
        }
        .meta-row {
            display: grid;
            grid-template-columns: 7mm 42mm 4mm 1fr;
            align-items: center;
            gap: 2mm;
        }
        .letter {
            margin-bottom: 7mm;
            font-size: 10.8pt;
            line-height: 1.55;
        }
        .letter p { margin: 0 0 3mm; }
        .items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 5mm;
            overflow: hidden;
            border: 1px solid #cddfcb;
            border-radius: 8px;
            font-size: 10pt;
        }
        .items th {
            padding: 4mm 3mm;
            color: #06701f;
            background: #f3faef;
            border-bottom: 1px solid #dbe7d7;
            font-weight: 800;
            text-align: center;
        }
        .items td {
            padding: 4mm 3mm;
            border-bottom: 1px solid #e5ebe3;
            border-right: 1px solid #e5ebe3;
            vertical-align: middle;
            line-height: 1.45;
        }
        .items tr:last-child td { border-bottom: 0; }
        .items td:last-child, .items th:last-child { border-right: 0; }
        .items .no {
            width: 11mm;
            text-align: center;
        }
        .number-pill {
            width: 8mm;
            height: 8mm;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #08751f;
            background: #eaf6e5;
            font-weight: 800;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .green-strong { color: #08751f; font-weight: 800; }
        .bottom-grid {
            display: grid;
            grid-template-columns: .86fr 1fr;
            gap: 6mm;
        }
        .note-card,
        .info-card,
        .total-card,
        .sign-card { padding: 5mm; }
        .section-title {
            display: flex;
            align-items: center;
            gap: 3mm;
            margin: 0 0 3mm;
            color: #08751f;
            font-size: 13pt;
            font-weight: 800;
        }
        .check-line {
            display: flex;
            gap: 3mm;
            align-items: flex-start;
            margin: 2mm 0;
            font-size: 9.8pt;
            line-height: 1.35;
        }
        .check {
            width: 4.5mm;
            height: 4.5mm;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            background: #08751f;
            font-size: 8pt;
            flex: 0 0 auto;
        }
        .info-card { margin-top: 5mm; }
        .info-line {
            display: grid;
            grid-template-columns: 10mm 1fr;
            gap: 3mm;
            padding: 2.5mm 0;
            border-bottom: 1px dashed #d6e3d2;
            font-size: 9.8pt;
        }
        .info-line:last-child { border-bottom: 0; }
        .info-line strong { display: block; margin-bottom: 1mm; }
        .info-icon {
            width: 8mm;
            height: 8mm;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #08751f;
            background: #eaf6e5;
            font-size: 14pt;
            font-weight: 800;
        }
        .total-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 4mm;
            margin-bottom: 3mm;
            font-size: 11pt;
        }
        .total-separator {
            margin: 4mm 0;
            border-top: 1px dashed #9fbea0;
        }
        .total-label {
            font-size: 13pt;
            font-weight: 800;
        }
        .grand-total {
            margin: 3mm 0;
            padding: 3mm;
            border-radius: 7px;
            color: #fff;
            background: linear-gradient(180deg, #0b8d28, #05651b);
            text-align: center;
            font-size: 24pt;
            font-weight: 900;
        }
        .terbilang {
            font-size: 10pt;
            line-height: 1.4;
        }
        .terbilang em {
            display: block;
            color: #08751f;
            font-style: italic;
        }
        .sign-card {
            margin-top: 5mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5mm;
            text-align: center;
            font-size: 10pt;
        }
        .sign-box:first-child { border-right: 1px solid #d6e3d2; }
        .signature {
            height: 20mm;
            display: flex;
            align-items: end;
            justify-content: center;
            color: #101828;
            font-family: "Brush Script MT", cursive;
            font-size: 26pt;
        }
        .sign-name {
            color: #08751f;
            font-weight: 800;
        }
        .sign-line {
            height: 20mm;
            display: flex;
            align-items: end;
            justify-content: center;
            letter-spacing: 1px;
        }
        .footer {
            margin-top: 5mm;
            padding-top: 4mm;
            display: grid;
            grid-template-columns: 1.65fr .85fr;
            gap: 7mm;
            border-top: 2px solid #08751f;
            font-size: 9.5pt;
            line-height: 1.45;
        }
        .footer-brand {
            display: grid;
            grid-template-columns: 28mm 1fr;
            gap: 4mm;
            align-items: center;
        }
        .footer-logo {
            width: 26mm;
            max-height: 12mm;
            object-fit: contain;
            object-position: left center;
        }
        .footer-contact {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.8mm;
            align-content: center;
        }
        .footer h3 {
            margin: 0 0 1mm;
            color: #08751f;
            font-size: 12pt;
        }
        @media print {
            body { background: #fff; }
            .no-print { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print</button>
    <main class="sheet">
        <div class="content">
            <header class="header">
                <div>
                    <img class="logo" src="<?= htmlspecialchars($base_url) ?>img/logo-nucoco.webp" alt="Nucoco">
                </div>
                <div class="doc-title">
                    <h1><?= htmlspecialchars($doc_title) ?></h1>
                    <div class="doc-number"><?= htmlspecialchars($quote['quote_number']) ?></div>
                </div>
            </header>

            <section class="intro-grid">
                <div class="customer-card">
                    <div class="label-muted"><?= $is_international ? 'To:' : 'Kepada Yth.' ?></div>
                    <h2><?= htmlspecialchars($customer_title) ?></h2>
                    <div class="contact-line">
                        <span class="icon">⌖</span>
                        <span><?= htmlspecialchars($customer_address ?: '-') ?></span>
                    </div>
                    <div class="contact-line">
                        <span class="icon">☎</span>
                        <span><?= htmlspecialchars($quote['customer_phone'] ?: '-') ?></span>
                    </div>
                    <div class="contact-line">
                        <span class="icon">✉</span>
                        <span><?= htmlspecialchars($quote['customer_email'] ?: '-') ?></span>
                    </div>
                </div>

                <div class="quote-meta">
                    <div class="meta-row"><span class="icon">▣</span><span><?= $is_international ? 'Quotation Date' : 'Tanggal Penawaran' ?></span><span>:</span><strong><?= htmlspecialchars($date_text) ?></strong></div>
                    <div class="meta-row"><span class="icon">◷</span><span><?= $is_international ? 'Valid Until' : 'Masa Berlaku' ?></span><span>:</span><strong><?= htmlspecialchars($valid_period_text) ?></strong></div>
                    <div class="meta-row"><span class="icon">♙</span><span><?= $is_international ? 'Sales Representative' : 'Sales' ?></span><span>:</span><strong><?= htmlspecialchars($sales_name) ?></strong></div>
                    <div class="meta-row"><span class="icon">▱</span><span><?= $is_international ? 'Project' : 'Proyek' ?></span><span>:</span><strong><?= htmlspecialchars($project) ?></strong></div>
                    <div class="meta-row"><span class="icon">▰</span><span><?= $is_international ? 'Delivery Method' : 'Metode Pengiriman' ?></span><span>:</span><strong><?= $is_international ? 'Courier' : 'Kurir' ?></strong></div>
                    <div class="meta-row"><span class="icon">$</span><span><?= $is_international ? 'Currency' : 'Mata Uang' ?></span><span>:</span><strong><?= htmlspecialchars($currency_label) ?></strong></div>
                    <div class="meta-row"><span class="icon">◔</span><span><?= $is_international ? 'Payment Terms' : 'Syarat Pembayaran' ?></span><span>:</span><strong>30 <?= $is_international ? 'Days' : 'Hari' ?></strong></div>
                </div>
            </section>

            <section class="letter">
                <?php if ($is_international): ?>
                    <p>Dear Sir/Madam,</p>
                    <p>Thank you for the opportunity given to Nucoco.<br>
                    We are pleased to submit our quotation for the <strong><?= htmlspecialchars($project) ?></strong> project as detailed below.</p>
                <?php else: ?>
                    <p>Dengan hormat,</p>
                    <p>Terima kasih atas kepercayaan yang diberikan kepada Nucoco.<br>
                    Berikut kami sampaikan penawaran harga untuk kebutuhan <strong><?= htmlspecialchars($project) ?></strong> sesuai rincian berikut.</p>
                <?php endif; ?>
            </section>

            <table class="items">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th><?= $is_international ? 'Product / Service' : 'Produk / Layanan' ?></th>
                        <th><?= $is_international ? 'Description' : 'Deskripsi' ?></th>
                        <th>Qty</th>
                        <th><?= $is_international ? 'Unit' : 'Satuan' ?></th>
                        <th><?= $is_international ? 'Unit Price' : 'Harga Satuan' ?></th>
                        <th><?= $is_international ? 'Total Price' : 'Total' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td class="no"><span class="number-pill"><?= $index + 1 ?></span></td>
                            <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                            <td><?= htmlspecialchars(print_quotation_item_description($item, $is_international)) ?></td>
                            <td class="center"><?= number_format((float) $item['quantity'], 0, ',', '.') ?></td>
                            <td class="center"><?= htmlspecialchars($item['unit']) ?></td>
                            <td class="right"><?= print_money($item['unit_price'], $quote['currency'], $is_international) ?></td>
                            <td class="right green-strong"><?= print_money($item['total'], $quote['currency'], $is_international) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <section class="bottom-grid">
                <div>
                    <div class="note-card">
                        <h3 class="section-title"><span>▣</span> <?= $is_international ? 'TERMS & NOTES' : 'CATATAN' ?></h3>
                        <?php foreach ($notes as $note): ?>
                            <div class="check-line"><span class="check">✓</span><span><?= htmlspecialchars($note) ?></span></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="info-card">
                        <h3 class="section-title"><span>▤</span> <?= $is_international ? 'ADDITIONAL INFORMATION' : 'INFORMASI LAINNYA' ?></h3>
                        <div class="info-line"><span class="info-icon">▣</span><span><strong><?= $is_international ? 'Payment Terms' : 'Syarat Pembayaran' ?></strong><?= $is_international ? '30 Days after invoice receipt' : '30 Hari setelah invoice diterima' ?></span></div>
                        <div class="info-line"><span class="info-icon">▰</span><span><strong><?= $is_international ? 'Delivery Method' : 'Metode Pengiriman' ?></strong><?= $is_international ? 'Courier / Vendor Delivery' : 'Kurir / Pengiriman Vendor' ?></span></div>
                        <div class="info-line"><span class="info-icon">◷</span><span><strong><?= $is_international ? 'Estimated Lead Time' : 'Estimasi Pekerjaan' ?></strong><?= $is_international ? '14 Working Days' : '14 Hari Kerja' ?></span></div>
                    </div>
                </div>

                <div>
                    <div class="total-card">
                        <div class="total-row"><span>Subtotal</span><span><?= print_money($quote['subtotal'], $quote['currency'], $is_international) ?></span></div>
                        <div class="total-row"><span><?= $is_international ? 'Discount' : 'Diskon' ?></span><span><?= print_money($quote['discount'], $quote['currency'], $is_international) ?></span></div>
                        <div class="total-row"><span><?= $is_international ? 'VAT' : 'PPN' ?> (11%)</span><span><?= print_money($quote['tax'], $quote['currency'], $is_international) ?></span></div>
                        <div class="total-separator"></div>
                        <div class="total-label"><?= $is_international ? 'GRAND TOTAL' : 'TOTAL PENAWARAN' ?></div>
                        <div class="grand-total"><?= print_money($quote['grand_total'], $quote['currency'], $is_international) ?></div>
                        <div class="terbilang"><?= $is_international ? 'Amount in Words:' : 'Terbilang:' ?><em><?= htmlspecialchars($amount_words) ?></em></div>
                    </div>

                    <div class="sign-card">
                        <div class="sign-box">
                            <div><?= $is_international ? 'Prepared By,' : 'Dibuat Oleh,' ?></div>
                            <div class="signature"><?= htmlspecialchars(strtok($sales_name, ' ') ?: $sales_name) ?></div>
                            <div class="sign-name"><?= htmlspecialchars($sales_name) ?></div>
                            <div>Sales Executive<br>Nucoco</div>
                        </div>
                        <div class="sign-box">
                            <div><?= $is_international ? 'Approved By,' : 'Disetujui Oleh,' ?></div>
                            <div class="sign-line">............................</div>
                            <div class="sign-name"><?= $is_international ? 'Customer Name' : 'Nama Customer' ?></div>
                            <div>Purchasing Manager<br><?= htmlspecialchars($customer_title) ?></div>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <div class="footer-brand">
                    <img class="footer-logo" src="<?= htmlspecialchars($base_url) ?>img/logo-nucoco.webp" alt="Nucoco">
                    <div><h3><?= htmlspecialchars($company_name) ?></h3><?= nl2br(htmlspecialchars($company_address)) ?></div>
                </div>
                <div class="footer-contact">
                    <div>🌐 www.nucoco.com</div>
                    <?php if ($company_email !== ''): ?><div>✉ <?= htmlspecialchars($company_email) ?></div><?php endif; ?>
                    <?php if ($company_phone !== ''): ?><div>☎ <?= htmlspecialchars($company_phone) ?></div><?php endif; ?>
                    <?php if ($company_instagram_label !== ''): ?><div>◎ <?= htmlspecialchars($company_instagram_label) ?></div><?php endif; ?>
                </div>
            </footer>
        </div>
    </main>
</body>
</html>
