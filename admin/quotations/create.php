<?php
$page = 'quotations';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/helpers.php';

$error = '';
$inquiry_id = isset($_GET['inquiry_id']) ? (int) $_GET['inquiry_id'] : 0;
$lead_id_from_url = isset($_GET['lead_id']) ? (int) $_GET['lead_id'] : 0;
$inquiry = $inquiry_id > 0 ? db_select_one("SELECT * FROM inquiries WHERE id = ? LIMIT 1", 'i', [$inquiry_id]) : null;
$lead_from_url = (!$inquiry && $lead_id_from_url > 0) ? db_select_one("SELECT * FROM leads WHERE id = ? LIMIT 1", 'i', [$lead_id_from_url]) : null;

$quote_type = $_POST['quote_type'] ?? ($_GET['quote_type'] ?? 'local');
$quote_type = in_array($quote_type, ['local', 'international'], true) ? $quote_type : 'local';
$language = quotation_product_language($quote_type);
$currency = quotation_currency($quote_type);

$products = db_select_all("SELECT id, name, price, description, quotation_description FROM products WHERE status='publish' AND language = ? ORDER BY name ASC", 's', [$language]);
$services = db_select_all("SELECT id, title, short_description, quotation_description FROM services WHERE status='publish' AND language = ? ORDER BY title ASC", 's', [$language]);

$customer_name = $_POST['customer_name'] ?? ($inquiry['name'] ?? ($lead_from_url['name'] ?? ''));
$customer_email = $_POST['customer_email'] ?? ($inquiry['email'] ?? ($lead_from_url['email'] ?? ''));
$customer_phone = $_POST['customer_phone'] ?? ($inquiry['phone'] ?? ($lead_from_url['phone'] ?? ''));
$customer_company = $_POST['customer_company'] ?? ($lead_from_url['company'] ?? '');
$valid_until = $_POST['valid_until'] ?? date('Y-m-d', strtotime('+14 days'));
$status = $_POST['status'] ?? 'draft';
$notes = $_POST['notes'] ?? '';
$discount = (float) ($_POST['discount'] ?? 0);
$tax = (float) ($_POST['tax'] ?? 0);
$items = $_POST['items'] ?? [
    ['quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0],
    ['quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0],
    ['quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $customer_name = trim($customer_name);
    $customer_email = trim($customer_email);
    $allowed_status = ['draft', 'sent', 'accepted', 'rejected', 'cancelled'];

    if ($customer_name === '') {
        $error = 'Customer name is required.';
    } elseif ($customer_email !== '' && !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid customer email.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid quotation status.';
    } else {
        $inquiry_for_lead = $inquiry;
        if (!$inquiry_for_lead && $inquiry_id > 0) {
            $inquiry_for_lead = db_select_one("SELECT * FROM inquiries WHERE id = ? LIMIT 1", 'i', [$inquiry_id]);
        }

        $lead_id = $inquiry_for_lead ? ensure_lead_from_inquiry($conn, $inquiry_for_lead, current_user_id()) : ($lead_id_from_url ?: null);
        $quote_number = quotation_next_number($conn);

        $quotation_id = db_insert(
            "INSERT INTO quotations
             (quote_number, inquiry_id, lead_id, quote_type, customer_name, customer_email, customer_phone, customer_company, currency, status, valid_until, notes, discount, tax, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'siisssssssssddi',
            [
                $quote_number,
                $inquiry_id ?: null,
                $lead_id,
                $quote_type,
                $customer_name,
                $customer_email,
                trim($customer_phone),
                trim($customer_company),
                $currency,
                $status,
                $valid_until ?: null,
                trim($notes),
                $discount,
                $tax,
                (int) current_user_id(),
            ]
        );

        $normalized_items = normalize_quotation_items_from_post();
        $subtotal = sync_quotation_items($quotation_id, $normalized_items);
        $grand_total = max(0, $subtotal - $discount + $tax);

        db_update(
            "UPDATE quotations SET subtotal = ?, grand_total = ? WHERE id = ?",
            'ddi',
            [$subtotal, $grand_total, $quotation_id]
        );

        if ($inquiry_id > 0) {
            db_update("UPDATE inquiries SET status = 'replied', updated_at = NOW() WHERE id = ?", 'i', [$inquiry_id]);
        }

        header('Location: ' . admin_url('quotations/detail.php?id=' . $quotation_id . '&success=created'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <div class="form-page-head">
        <div class="form-top-actions">
            <a href="<?= admin_url('quotations/') ?>" class="form-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back</span>
            </a>
        </div>
        <div class="form-title-row">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h3>Create Quotation</h3>
                <span class="form-title-badge"><?= htmlspecialchars(strtoupper($quote_type)) ?></span>
            </div>
        </div>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" class="admin-form" id="quotation-form">
        <?= csrf_field() ?>
        <div class="admin-form-card mb-4">
            <div class="form-section-title">
                <span class="material-symbols-outlined">description</span>
                <h4>Main Information</h4>
            </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Quotation Type</label>
                <select name="quote_type" class="form-select" onchange="window.location='?<?= $inquiry_id ? 'inquiry_id=' . $inquiry_id . '&' : ($lead_id_from_url ? 'lead_id=' . $lead_id_from_url . '&' : '') ?>quote_type='+this.value">
                    <option value="local" <?= $quote_type === 'local' ? 'selected' : '' ?>>Local</option>
                    <option value="international" <?= $quote_type === 'international' ? 'selected' : '' ?>>International</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Currency</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($currency) ?>" readonly>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Valid Until</label>
                <input type="date" name="valid_until" class="form-control" value="<?= htmlspecialchars($valid_until) ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($customer_name) ?>" required>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Customer Email</label>
                <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($customer_email) ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Phone</label>
                <input type="text" name="customer_phone" class="form-control" value="<?= htmlspecialchars($customer_phone) ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Company</label>
                <input type="text" name="customer_company" class="form-control" value="<?= htmlspecialchars($customer_company) ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['draft','sent','accepted','rejected','cancelled'] as $option): ?>
                        <option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 mb-20">
                <label class="label fs-16 mb-2">Notes</label>
                <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($notes) ?></textarea>
            </div>
        </div>
        </div>

        <div class="admin-form-card mb-4">
        <div class="form-section-title">
            <span class="material-symbols-outlined">receipt_long</span>
            <h4>Products / Services</h4>
        </div>
        <div class="table-responsive">
            <table class="table align-middle" id="items-table">
                <thead>
                    <tr>
                        <th style="min-width:260px;">Catalog Item</th>
                        <th>Name</th>
                            <th style="width:120px;">Qty</th>
                        <th style="width:90px;">Unit</th>
                            <th style="width:170px;">Price</th>
                            <th style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < max(3, count($items)); $i++): $item = $items[$i] ?? []; ?>
                        <tr>
                            <td>
                                <select name="items[<?= $i ?>][catalog_key]" class="form-select catalog-select">
                                    <option value="">Manual item</option>
                                    <optgroup label="Products">
                                        <?php foreach ($products as $product): ?>
                                            <option value="product:<?= (int) $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= (float) ($product['price'] ?? 0) ?>" data-description="<?= htmlspecialchars(strip_tags($product['quotation_description'] ?: ($product['description'] ?? ''))) ?>">
                                                Product - <?= htmlspecialchars($product['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Services">
                                        <?php foreach ($services as $service): ?>
                                            <option value="service:<?= (int) $service['id'] ?>" data-name="<?= htmlspecialchars($service['title']) ?>" data-price="0" data-description="<?= htmlspecialchars(strip_tags($service['quotation_description'] ?: ($service['short_description'] ?? ''))) ?>">
                                                Service - <?= htmlspecialchars($service['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" name="items[<?= $i ?>][item_type]" value="<?= htmlspecialchars($item['item_type'] ?? 'product') ?>">
                                <input type="hidden" name="items[<?= $i ?>][reference_id]" value="<?= htmlspecialchars($item['reference_id'] ?? '') ?>">
                                <input type="text" name="items[<?= $i ?>][item_name]" class="form-control item-name" value="<?= htmlspecialchars($item['item_name'] ?? '') ?>">
                                <input type="hidden" name="items[<?= $i ?>][description]" class="item-description" value="<?= htmlspecialchars($item['description'] ?? '') ?>">
                            </td>
                            <td><input type="number" step="0.01" min="0" name="items[<?= $i ?>][quantity]" class="form-control qty-input" value="<?= htmlspecialchars($item['quantity'] ?? 1) ?>"></td>
                            <td><input type="text" name="items[<?= $i ?>][unit]" class="form-control" value="<?= htmlspecialchars($item['unit'] ?? 'pcs') ?>"></td>
                            <td><input type="text" inputmode="decimal" name="items[<?= $i ?>][unit_price]" class="form-control item-price money-input" value="<?= htmlspecialchars(number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.')) ?>"></td>
                            <td class="text-center">
                                <button type="button" class="btn item-row-btn item-remove-btn" aria-label="Remove item">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn add-item-btn mt-3" id="add-item-row">
            <span class="material-symbols-outlined">add</span>
            <span>Add Item</span>
        </button>

        <div class="row justify-content-end">
            <div class="col-md-3 mb-20">
                <label class="label fs-16 mb-2">Discount</label>
                <input type="number" step="0.01" min="0" name="discount" class="form-control" value="<?= htmlspecialchars((string) $discount) ?>">
            </div>
            <div class="col-md-3 mb-20">
                <label class="label fs-16 mb-2">Tax</label>
                <input type="number" step="0.01" min="0" name="tax" class="form-control" value="<?= htmlspecialchars((string) $tax) ?>">
            </div>
        </div>
        </div>

        <div class="form-actions">
            <a href="<?= admin_url('quotations/') ?>" class="btn btn-danger text-white">Cancel</a>
            <button class="btn btn-primary text-white" type="submit">
                <span class="material-symbols-outlined">save</span>
                <span>Save</span>
            </button>
        </div>
    </form>
</div>

<script>
function normalizeNumber(value) {
    return String(value || '').replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
}

function formatNumber(value) {
    const numeric = normalizeNumber(value);
    if (numeric === '' || Number.isNaN(Number(numeric))) return '';
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(numeric));
}

document.querySelectorAll('.money-input').forEach((input) => {
    input.value = formatNumber(input.value);
    input.addEventListener('blur', () => {
        input.value = formatNumber(input.value);
    });
});

function bindMoneyInput(input) {
    input.value = formatNumber(input.value);
    input.addEventListener('blur', () => {
        input.value = formatNumber(input.value);
    });
}

function renumberItemRows() {
    document.querySelectorAll('#items-table tbody tr').forEach((row, index) => {
        row.querySelectorAll('[name^="items["]').forEach((field) => {
            field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
        });
    });
}

function clearItemRow(row) {
    row.querySelector('.catalog-select').value = '';
    row.querySelector('[name$="[item_type]"]').value = 'product';
    row.querySelector('[name$="[reference_id]"]').value = '';
    row.querySelector('.item-name').value = '';
    row.querySelector('.item-description').value = '';
    row.querySelector('.qty-input').value = '1';
    row.querySelector('[name$="[unit]"]').value = 'pcs';
    row.querySelector('.item-price').value = '0';
}

document.querySelector('#items-table')?.addEventListener('change', (event) => {
    if (!event.target.classList.contains('catalog-select')) return;
    const select = event.target;
    const row = select.closest('tr');
    const selected = select.options[select.selectedIndex];
    const [type, id] = (select.value || ':').split(':');
    row.querySelector('[name$="[item_type]"]').value = type || 'product';
    row.querySelector('[name$="[reference_id]"]').value = id || '';
    if (selected.dataset.name) row.querySelector('.item-name').value = selected.dataset.name;
    if (selected.dataset.description) row.querySelector('.item-description').value = selected.dataset.description;
    if (selected.dataset.price) row.querySelector('.item-price').value = formatNumber(selected.dataset.price);
});

document.querySelector('#items-table')?.addEventListener('click', (event) => {
    const button = event.target.closest('.item-remove-btn');
    if (!button) return;
    const tbody = document.querySelector('#items-table tbody');
    const row = button.closest('tr');
    if (tbody.rows.length > 1) {
        row.remove();
        renumberItemRows();
        return;
    }
    clearItemRow(row);
});

document.querySelector('#add-item-row')?.addEventListener('click', () => {
    const tbody = document.querySelector('#items-table tbody');
    const row = tbody.rows[tbody.rows.length - 1].cloneNode(true);
    clearItemRow(row);
    tbody.appendChild(row);
    renumberItemRows();
    bindMoneyInput(row.querySelector('.money-input'));
});

document.querySelector('.admin-form')?.addEventListener('submit', () => {
    document.querySelectorAll('.money-input').forEach((input) => {
        input.value = normalizeNumber(input.value);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
