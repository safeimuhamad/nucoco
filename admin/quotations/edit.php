<?php
$page = 'quotations';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$quote = quotation_load($id);
if (!$quote) {
    header('Location: ' . admin_url('quotations/?error=not_found'));
    exit;
}

$error = '';
$quote_type = $_POST['quote_type'] ?? $quote['quote_type'];
$quote_type = in_array($quote_type, ['local', 'international'], true) ? $quote_type : 'local';
$language = quotation_product_language($quote_type);
$currency = quotation_currency($quote_type);
$products = db_select_all("SELECT id, name, price, description, quotation_description FROM products WHERE status='publish' AND language = ? ORDER BY name ASC", 's', [$language]);
$services = db_select_all("SELECT id, title, short_description, quotation_description FROM services WHERE status='publish' AND language = ? ORDER BY title ASC", 's', [$language]);

$items = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['items'] ?? []) : quotation_items($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_company = trim($_POST['customer_company'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $valid_until = $_POST['valid_until'] ?: null;
    $status = $_POST['status'] ?? 'draft';
    $notes = trim($_POST['notes'] ?? '');
    $discount = (float) ($_POST['discount'] ?? 0);
    $tax = (float) ($_POST['tax'] ?? 0);
    $allowed_status = ['draft', 'sent', 'accepted', 'rejected', 'cancelled'];

    if ($customer_name === '') {
        $error = 'Customer name is required.';
    } elseif ($customer_email !== '' && !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid customer email.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid quotation status.';
    } else {
        db_update(
            "UPDATE quotations
             SET quote_type = ?, customer_name = ?, customer_email = ?, customer_phone = ?, customer_company = ?, customer_address = ?, currency = ?, status = ?, valid_until = ?, notes = ?, discount = ?, tax = ?, updated_by = ?
             WHERE id = ?",
            'ssssssssssddii',
            [$quote_type, $customer_name, $customer_email, $customer_phone, $customer_company, $customer_address, $currency, $status, $valid_until, $notes, $discount, $tax, (int) current_user_id(), $id]
        );

        $subtotal = sync_quotation_items($id, normalize_quotation_items_from_post());
        $grand_total = max(0, $subtotal - $discount + $tax);
        db_update("UPDATE quotations SET subtotal = ?, grand_total = ? WHERE id = ?", 'ddi', [$subtotal, $grand_total, $id]);

        header('Location: ' . admin_url('quotations/detail.php?id=' . $id . '&success=updated'));
        exit;
    }
}

$quote = quotation_load($id);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <div class="form-page-head">
        <div class="form-top-actions">
            <a href="<?= admin_url('quotations/detail.php?id=' . $id) ?>" class="form-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back</span>
            </a>
        </div>
        <div class="form-title-row">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h3>Edit Quotation</h3>
                <span class="form-title-badge"><?= htmlspecialchars($quote['quote_number']) ?></span>
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
                <select name="quote_type" class="form-select">
                    <option value="local" <?= $quote_type === 'local' ? 'selected' : '' ?>>Local</option>
                    <option value="international" <?= $quote_type === 'international' ? 'selected' : '' ?>>International</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['draft','sent','accepted','rejected','cancelled'] as $option): ?>
                        <option value="<?= $option ?>" <?= $quote['status'] === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Valid Until</label>
                <input type="date" name="valid_until" class="form-control" value="<?= htmlspecialchars($quote['valid_until'] ?? '') ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($quote['customer_name']) ?>" required>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Customer Email</label>
                <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($quote['customer_email']) ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Phone</label>
                <input type="text" name="customer_phone" class="form-control" value="<?= htmlspecialchars($quote['customer_phone']) ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Company</label>
                <input type="text" name="customer_company" class="form-control" value="<?= htmlspecialchars($quote['customer_company']) ?>">
            </div>
            <div class="col-lg-6 col-md-12 mb-20">
                <label class="label fs-16 mb-2">Address</label>
                <textarea name="customer_address" class="form-control" rows="3"><?= htmlspecialchars($quote['customer_address'] ?? '') ?></textarea>
            </div>
            <div class="col-lg-3 col-md-6 mb-20">
                <label class="label fs-16 mb-2">Notes</label>
                <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($quote['notes'] ?? '') ?></textarea>
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
                <thead><tr><th>Catalog Item</th><th>Name</th><th style="width:120px;">Qty</th><th>Unit</th><th style="width:170px;">Price</th><th style="width:70px;"></th></tr></thead>
                <tbody>
                    <?php for ($i = 0; $i < max(5, count($items)); $i++): $item = $items[$i] ?? []; ?>
                        <tr>
                            <td style="min-width:260px;">
                                <select name="items[<?= $i ?>][catalog_key]" class="form-select catalog-select">
                                    <option value="">Manual item</option>
                                    <optgroup label="Products">
                                        <?php foreach ($products as $product): ?>
                                            <option value="product:<?= (int) $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= (float) ($product['price'] ?? 0) ?>" data-description="<?= htmlspecialchars(strip_tags($product['quotation_description'] ?: ($product['description'] ?? ''))) ?>">Product - <?= htmlspecialchars($product['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Services">
                                        <?php foreach ($services as $service): ?>
                                            <option value="service:<?= (int) $service['id'] ?>" data-name="<?= htmlspecialchars($service['title']) ?>" data-price="0" data-description="<?= htmlspecialchars(strip_tags($service['quotation_description'] ?: ($service['short_description'] ?? ''))) ?>">Service - <?= htmlspecialchars($service['title']) ?></option>
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
                            <td style="width:120px;"><input type="number" step="0.01" min="0" name="items[<?= $i ?>][quantity]" class="form-control qty-input" value="<?= htmlspecialchars($item['quantity'] ?? 1) ?>"></td>
                            <td style="width:90px;"><input type="text" name="items[<?= $i ?>][unit]" class="form-control" value="<?= htmlspecialchars($item['unit'] ?? 'pcs') ?>"></td>
                            <td style="width:170px;"><input type="text" inputmode="decimal" name="items[<?= $i ?>][unit_price]" class="form-control item-price money-input" value="<?= htmlspecialchars(number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.')) ?>"></td>
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
                <input type="number" step="0.01" min="0" name="discount" class="form-control" value="<?= htmlspecialchars($quote['discount']) ?>">
            </div>
            <div class="col-md-3 mb-20">
                <label class="label fs-16 mb-2">Tax</label>
                <input type="number" step="0.01" min="0" name="tax" class="form-control" value="<?= htmlspecialchars($quote['tax']) ?>">
            </div>
        </div>
        </div>

        <div class="form-actions">
            <a href="<?= admin_url('quotations/detail.php?id=' . $id) ?>" class="btn btn-danger text-white">Cancel</a>
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
