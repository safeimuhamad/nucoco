<?php
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$invoice = invoice_load($id);
if (!$invoice) {
    http_response_code(404);
    echo 'Invoice not found.';
    exit;
}

$items = invoice_items($id);
$settings = db_select_one("SELECT company_name, email, phone, whatsapp, address, instagram_url FROM web_config LIMIT 1") ?: [];
$creator = !empty($invoice['created_by']) ? db_select_one("SELECT name FROM users WHERE id = ? LIMIT 1", 'i', [(int) $invoice['created_by']]) : null;

$sales_name = $creator['name'] ?? ($_SESSION['name'] ?? 'Sales Executive');
$company_name = trim($settings['company_name'] ?? '') ?: 'Nucoco';
$company_address = trim($settings['address'] ?? '') ?: '-';
$company_phone = trim($settings['phone'] ?? '') ?: (trim($settings['whatsapp'] ?? '') ?: '-');
$company_email = trim($settings['email'] ?? '') ?: 'billing@nucoco.com';
$company_website = 'www.nucoco.com';
$payment_account_name = $company_name;
$currency = $invoice['currency'];
$taxable_amount = max(0, (float) $invoice['subtotal'] - (float) $invoice['discount']);

function invoice_print_money($amount, $currency, bool $with_currency = false)
{
    $number = quotation_format_plain_number($amount);
    if ($with_currency) {
        return $currency . ' ' . $number;
    }
    return $number;
}

function invoice_print_date($date)
{
    return $date ? date('d M Y', strtotime($date)) : '-';
}

function invoice_print_short_description($description)
{
    $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $description)));
    if ($description === '') {
        return '-';
    }

    return strlen($description) > 95 ? substr($description, 0, 92) . '...' : $description;
}

function invoice_number_words_id($number)
{
    $number = (int) round($number);
    $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    if ($number < 12) return $words[$number];
    if ($number < 20) return $words[$number - 10] . ' belas';
    if ($number < 100) return trim($words[(int) floor($number / 10)] . ' puluh ' . $words[$number % 10]);
    if ($number < 200) return trim('seratus ' . invoice_number_words_id($number - 100));
    if ($number < 1000) return trim($words[(int) floor($number / 100)] . ' ratus ' . invoice_number_words_id($number % 100));
    if ($number < 2000) return trim('seribu ' . invoice_number_words_id($number - 1000));
    if ($number < 1000000) return trim(invoice_number_words_id((int) floor($number / 1000)) . ' ribu ' . invoice_number_words_id($number % 1000));
    if ($number < 1000000000) return trim(invoice_number_words_id((int) floor($number / 1000000)) . ' juta ' . invoice_number_words_id($number % 1000000));
    return trim(invoice_number_words_id((int) floor($number / 1000000000)) . ' miliar ' . invoice_number_words_id($number % 1000000000));
}

function invoice_number_words_en($number)
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
    if ($number < 20) return $words[$number];
    if ($number < 100) return trim($tens[(int) floor($number / 10)] . ' ' . $words[$number % 10]);
    if ($number < 1000) return trim($words[(int) floor($number / 100)] . ' hundred ' . invoice_number_words_en($number % 100));
    if ($number < 1000000) return trim(invoice_number_words_en((int) floor($number / 1000)) . ' thousand ' . invoice_number_words_en($number % 1000));
    if ($number < 1000000000) return trim(invoice_number_words_en((int) floor($number / 1000000)) . ' million ' . invoice_number_words_en($number % 1000000));
    return trim(invoice_number_words_en((int) floor($number / 1000000000)) . ' billion ' . invoice_number_words_en($number % 1000000000));
}

$amount_words = $currency === 'IDR'
    ? ucwords(invoice_number_words_en($invoice['grand_total'])) . ' Indonesian Rupiah Only'
    : ucwords(invoice_number_words_en($invoice['grand_total'])) . ' Dollars Only';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef3ef; color: #101828; font-family: Inter, Arial, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .sheet { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 10mm; background: #fff; box-shadow: 0 12px 32px rgba(15,23,42,.08); }
        .header { display: grid; grid-template-columns: 1fr auto; align-items: start; gap: 12mm; margin-bottom: 9mm; }
        .logo { width: 52mm; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0 0 3mm; color: #07651c; font-size: 34pt; line-height: 1; font-weight: 900; letter-spacing: 0; }
        .doc-number { min-width: 56mm; display: inline-block; padding: 2.2mm 7mm; border-radius: 7px; color: #fff; background: linear-gradient(180deg, #0b971f, #006516); font-size: 15pt; font-weight: 800; text-align: center; }
        .intro-grid { display: grid; grid-template-columns: 51mm 69mm 58mm; gap: 6mm; align-items: stretch; margin-bottom: 7mm; font-size: 9.5pt; }
        .bill-to { padding-top: 2mm; }
        .section-label { color: #08751f; font-weight: 900; font-size: 11pt; margin-bottom: 4mm; }
        .bill-to h2 { margin: 0 0 3mm; color: #111827; font-size: 15pt; line-height: 1.18; }
        .contact-line { display: flex; gap: 2.5mm; margin: 2.5mm 0; line-height: 1.35; }
        .icon { color: #08751f; font-weight: 900; width: 4mm; flex: 0 0 auto; }
        .meta { padding-left: 7mm; border-left: 1px solid #d6e3d2; }
        .meta-row { display: grid; grid-template-columns: 27mm 4mm 1fr; gap: 1.5mm; margin-bottom: 3.2mm; line-height: 1.25; }
        .meta-row strong { font-weight: 500; }
        .company-card { border: 1px solid #d6e3d2; border-radius: 9px; padding: 5mm; background: linear-gradient(135deg, #fbfef9, #f6fbf2); line-height: 1.38; }
        .company-card h3 { margin: 0 0 3mm; color: #08751f; font-size: 13pt; }
        .company-card strong { display: block; margin-bottom: 3mm; }
        .separator { height: 1px; background: #96b990; margin: 4mm 0; }
        .invoice-for { width: 82mm; border: 1px solid #d6e3d2; border-radius: 8px; padding: 3.5mm 5mm; background: #fbfef9; margin-bottom: 4mm; font-size: 9.5pt; }
        .invoice-for h3 { display: flex; gap: 2.5mm; align-items: center; margin: 0 0 2mm; color: #08751f; font-size: 12pt; }
        .invoice-for strong { display: block; margin-bottom: 1mm; }
        .items { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 0; overflow: hidden; border: 1px solid #d6e3d2; border-radius: 8px; font-size: 9.2pt; }
        .items th { padding: 3mm 2mm; color: #08751f; background: #f2f8ef; border-bottom: 1px solid #d6e3d2; font-weight: 800; text-align: center; line-height: 1.2; }
        .items td { padding: 3mm 2.5mm; border-bottom: 1px solid #e5ebe3; border-right: 1px solid #e5ebe3; vertical-align: middle; line-height: 1.28; }
        .items tr:last-child td { border-bottom: 0; }
        .items td:last-child, .items th:last-child { border-right: 0; }
        .item-name { display: block; margin-bottom: 1mm; }
        .item-description { font-size: 8.6pt; }
        .number-pill { width: 7mm; height: 7mm; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #08751f; background: #eaf6e5; font-size: 11pt; font-weight: 800; }
        .center { text-align: center; }
        .right { text-align: right; white-space: nowrap; }
        .green-strong { color: #08751f; font-weight: 800; }
        .bottom-grid { display: grid; grid-template-columns: .92fr 1fr; gap: 6mm; margin-top: 5mm; }
        .card { border: 1px solid #d6e3d2; border-radius: 8px; padding: 4mm 5mm; background: #fff; }
        .card + .card { margin-top: 3mm; }
        .card-title { display: flex; gap: 2.5mm; align-items: center; margin: 0 0 2.5mm; color: #08751f; font-size: 11.5pt; font-weight: 900; }
        .payment-row { display: grid; grid-template-columns: 30mm 3mm 1fr; gap: 2mm; margin: 1.5mm 0; font-size: 8.7pt; }
        .notes-line { display: flex; gap: 2.2mm; align-items: flex-start; margin: 1.8mm 0; font-size: 8.5pt; line-height: 1.25; }
        .check { width: 4mm; height: 4mm; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #fff; background: #08751f; font-size: 7pt; flex: 0 0 auto; }
        .total-card { border: 1px solid #d6e3d2; border-radius: 8px; overflow: hidden; background: linear-gradient(135deg, #fff, #f6fbf2); }
        .total-inner { padding: 4.5mm 6mm; }
        .total-row { display: grid; grid-template-columns: 1fr auto auto; gap: 4mm; margin-bottom: 2.4mm; font-size: 10.5pt; }
        .total-line { margin: 3.2mm 0 3.8mm; border-top: 1px dashed #8eae88; }
        .total-label { font-size: 12pt; font-weight: 900; }
        .grand-total { margin-top: 1.5mm; color: #08751f; text-align: right; font-size: 24pt; line-height: 1.1; font-weight: 900; }
        .words { margin-top: 0; padding: 3mm 6mm; background: #eef8e8; color: #08751f; font-size: 9pt; }
        .words span { display: block; margin-bottom: 1mm; color: #101828; font-weight: 700; }
        .sign-card { margin-top: 3.5mm; display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #d6e3d2; border-radius: 8px; overflow: hidden; text-align: center; page-break-inside: avoid; font-size: 9pt; }
        .sign-box { min-height: 32mm; padding: 3mm 5mm; line-height: 1.25; }
        .sign-box + .sign-box { border-left: 1px solid #d6e3d2; }
        .signature { height: 13mm; display: flex; align-items: end; justify-content: center; font-family: "Brush Script MT", cursive; font-size: 22pt; }
        .sign-line { height: 13mm; display: flex; align-items: end; justify-content: center; letter-spacing: 1px; }
        .sign-name { color: #08751f; font-weight: 900; }
        .footer { margin-top: 3.5mm; padding-top: 2.5mm; display: grid; grid-template-columns: 18mm 1fr 1fr 1fr; gap: 5mm; align-items: center; border-top: 2px solid #08751f; font-size: 8.8pt; }
        .footer-logo { width: 17mm; grid-row: 1 / span 2; }
        .footer-message { text-align: center; grid-column: 2 / 5; margin-bottom: 1mm; }
        .footer-contact { display: contents; }
        .footer-contact div { display: flex; align-items: center; justify-content: center; gap: 2mm; border-left: 1px solid #d6e3d2; min-height: 7mm; }
        @media screen { .sheet { margin: 12px auto; } }
        @media print { body { background: #fff; } .sheet { margin: 0; width: 210mm; min-height: 297mm; box-shadow: none; } @page { size: A4; margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <main class="sheet">
        <header class="header">
            <img class="logo" src="../../img/logo-nucoco.webp" alt="Nucoco">
            <div class="doc-title">
                <h1>INVOICE</h1>
                <div class="doc-number"><?= htmlspecialchars($invoice['invoice_number']) ?></div>
            </div>
        </header>

        <section class="intro-grid">
            <div class="bill-to">
                <div class="section-label">BILL TO:</div>
                <h2><?= htmlspecialchars($invoice['customer_name']) ?></h2>
                <div><?= htmlspecialchars($invoice['customer_company'] ?: '-') ?></div>
                <div class="contact-line"><span class="icon">☎</span><span><?= htmlspecialchars($invoice['customer_phone'] ?: '-') ?></span></div>
                <div class="contact-line"><span class="icon">✉</span><span><?= htmlspecialchars($invoice['customer_email'] ?: '-') ?></span></div>
            </div>

            <div class="meta">
                <div class="meta-row"><strong>Invoice Date</strong><span>:</span><span><?= invoice_print_date($invoice['invoice_date']) ?></span></div>
                <div class="meta-row"><strong>Due Date</strong><span>:</span><span><?= invoice_print_date($invoice['due_date']) ?></span></div>
                <div class="meta-row"><strong>PO Number</strong><span>:</span><span>-</span></div>
                <div class="meta-row"><strong>Sales</strong><span>:</span><span><?= htmlspecialchars($sales_name) ?></span></div>
                <div class="meta-row"><strong>Project</strong><span>:</span><span><?= htmlspecialchars($invoice['customer_company'] ?: 'Invoice from quotation') ?></span></div>
                <div class="meta-row"><strong>Currency</strong><span>:</span><span><?= htmlspecialchars($currency === 'IDR' ? 'IDR - Indonesian Rupiah' : 'USD - US Dollar') ?></span></div>
                <div class="meta-row"><strong>Payment Terms</strong><span>:</span><span>30 Days</span></div>
            </div>

            <div class="company-card">
                <h3>NUCOCO</h3>
                <strong><?= htmlspecialchars($company_name) ?></strong>
                <div><?= nl2br(htmlspecialchars($company_address)) ?></div>
                <div class="contact-line"><span class="icon">☎</span><span><?= htmlspecialchars($company_phone) ?></span></div>
                <div class="contact-line"><span class="icon">✉</span><span><?= htmlspecialchars($company_email) ?></span></div>
                <div class="contact-line"><span class="icon">🌐</span><span><?= htmlspecialchars($company_website) ?></span></div>
            </div>
        </section>

        <div class="separator"></div>

        <section class="invoice-for">
            <h3><span>▤</span> INVOICE FOR</h3>
            <strong><?= htmlspecialchars($invoice['customer_company'] ?: $invoice['customer_name']) ?></strong>
            <div>Based on Quotation <?= htmlspecialchars($invoice['quote_number'] ?: '-') ?></div>
        </section>

        <table class="items">
            <colgroup>
                <col style="width: 12mm;">
                <col style="width: 78mm;">
                <col style="width: 15mm;">
                <col style="width: 20mm;">
                <col style="width: 31mm;">
                <col style="width: 34mm;">
            </colgroup>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Unit Price (<?= htmlspecialchars($currency) ?>)</th>
                    <th>Amount (<?= htmlspecialchars($currency) ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td class="center"><span class="number-pill"><?= $index + 1 ?></span></td>
                        <td>
                            <strong class="item-name"><?= htmlspecialchars($item['item_name']) ?></strong>
                            <span class="item-description"><?= htmlspecialchars(invoice_print_short_description($item['description'] ?? '')) ?></span>
                        </td>
                        <td class="center"><?= quotation_format_quantity($item['quantity']) ?></td>
                        <td class="center"><?= htmlspecialchars($item['unit']) ?></td>
                        <td class="right"><?= invoice_print_money($item['unit_price'], $currency) ?></td>
                        <td class="right green-strong"><?= invoice_print_money($item['total'], $currency) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <section class="bottom-grid">
            <div>
                <div class="card">
                    <h3 class="card-title"><span>▤</span> PAYMENT INFORMATION</h3>
                    <div class="payment-row"><span>Bank Branch</span><span>:</span><strong><?= htmlspecialchars($invoice['bank_branch'] ?: '-') ?></strong></div>
                    <div class="payment-row"><span>Account Name</span><span>:</span><strong><?= htmlspecialchars($payment_account_name ?: '-') ?></strong></div>
                    <div class="payment-row"><span>Account Number</span><span>:</span><strong><?= htmlspecialchars($invoice['bank_account_number'] ?: '-') ?></strong></div>
                </div>
                <div class="card">
                    <h3 class="card-title"><span>▤</span> NOTES</h3>
                    <div class="notes-line"><span class="check">✓</span><span>Please make payment to the account above.</span></div>
                    <div class="notes-line"><span class="check">✓</span><span>Payment will be confirmed after we receive the full amount.</span></div>
                    <div class="notes-line"><span class="check">✓</span><span>Please include the invoice number in your payment.</span></div>
                    <div class="notes-line"><span class="check">✓</span><span>If you have any questions, feel free to contact us.</span></div>
                </div>
            </div>

            <div>
                <div class="total-card">
                    <div class="total-inner">
                        <div class="total-row"><span>Subtotal</span><span><?= htmlspecialchars($currency) ?></span><span><?= invoice_print_money($invoice['subtotal'], $currency) ?></span></div>
                        <div class="total-row"><span>Discount</span><span><?= htmlspecialchars($currency) ?></span><span><?= invoice_print_money($invoice['discount'], $currency) ?></span></div>
                        <div class="total-row"><span>Taxable Amount</span><span><?= htmlspecialchars($currency) ?></span><span><?= invoice_print_money($taxable_amount, $currency) ?></span></div>
                        <div class="total-row"><span>VAT 11%</span><span><?= htmlspecialchars($currency) ?></span><span><?= invoice_print_money($invoice['tax'], $currency) ?></span></div>
                        <div class="total-line"></div>
                        <div class="total-label">GRAND TOTAL</div>
                        <div class="grand-total"><?= htmlspecialchars($currency) ?> <?= invoice_print_money($invoice['grand_total'], $currency) ?></div>
                    </div>
                    <div class="words"><span>Amount in Words:</span><em><?= htmlspecialchars($amount_words) ?></em></div>
                </div>
            </div>
        </section>

        <section class="sign-card">
            <div class="sign-box">
                <div>Issued By,</div>
                <div class="signature"><?= htmlspecialchars(strtok($sales_name, ' ') ?: $sales_name) ?></div>
                <div class="sign-name"><?= htmlspecialchars($sales_name) ?></div>
                <div>Sales Executive<br>Nucoco</div>
            </div>
            <div class="sign-box">
                <div>Approved By,</div>
                <div class="sign-line">............................</div>
                <div class="sign-name">Customer Name</div>
                <div>Purchasing Manager<br><?= htmlspecialchars($invoice['customer_company'] ?: $invoice['customer_name']) ?></div>
            </div>
        </section>

        <footer class="footer">
            <img class="footer-logo" src="../../img/logo-nucoco.webp" alt="Nucoco">
            <div class="footer-message">Thank you for your business and trust in Nucoco.</div>
            <div class="footer-contact">
                <div>🌐 <?= htmlspecialchars($company_website) ?></div>
                <div>✉ <?= htmlspecialchars($company_email) ?></div>
                <div>☎ <?= htmlspecialchars($company_phone) ?></div>
            </div>
        </footer>
    </main>
</body>
</html>
