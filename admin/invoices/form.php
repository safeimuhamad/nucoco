<div class="main-content-container overflow-hidden">
    <div class="form-page-head">
        <div class="form-top-actions">
            <a href="<?= admin_url($mode === 'edit' ? 'invoices/detail.php?id=' . (int) $invoice['id'] : 'quotations/detail.php?id=' . (int) ($source_quote['id'] ?? 0)) ?>" class="form-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back</span>
            </a>
        </div>
        <div class="form-title-row">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h3><?= $mode === 'edit' ? 'Edit Invoice' : 'Create Invoice' ?></h3>
                <?php if (!empty($invoice['invoice_number'])): ?>
                    <span class="form-title-badge"><?= htmlspecialchars($invoice['invoice_number']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" class="admin-form" id="invoice-form">
        <?= csrf_field() ?>
        <input type="hidden" name="quotation_id" value="<?= htmlspecialchars((string) ($invoice['quotation_id'] ?? ($source_quote['id'] ?? ''))) ?>">
        <input type="hidden" name="lead_id" value="<?= htmlspecialchars((string) ($invoice['lead_id'] ?? ($source_quote['lead_id'] ?? ''))) ?>">

        <div class="admin-form-card mb-4">
            <div class="form-section-title">
                <span class="material-symbols-outlined">receipt_long</span>
                <h4>Main Information</h4>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Quotation No.</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($source_quote['quote_number'] ?? ($invoice['quote_number'] ?? '-')) ?>" readonly>
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Currency</label>
                    <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars($invoice['currency'] ?? 'IDR') ?>" readonly>
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control" value="<?= htmlspecialchars($invoice['invoice_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($invoice['due_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($invoice['customer_name'] ?? '') ?>" required>
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Customer Email</label>
                    <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($invoice['customer_email'] ?? '') ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Phone</label>
                    <input type="text" name="customer_phone" class="form-control" value="<?= htmlspecialchars($invoice['customer_phone'] ?? '') ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Company</label>
                    <input type="text" name="customer_company" class="form-control" value="<?= htmlspecialchars($invoice['customer_company'] ?? '') ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['draft','sent','paid','overdue','cancelled'] as $option): ?>
                            <option value="<?= $option ?>" <?= (($invoice['status'] ?? 'draft') === $option) ? 'selected' : '' ?>><?= invoice_status_label($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Bank Account No.</label>
                    <input type="text" name="bank_account_number" class="form-control" value="<?= htmlspecialchars($invoice['bank_account_number'] ?? '') ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Account Holder</label>
                    <input type="text" name="bank_account_name" class="form-control" value="<?= htmlspecialchars($invoice['bank_account_name'] ?? '') ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Bank Branch</label>
                    <input type="text" name="bank_branch" class="form-control" value="<?= htmlspecialchars($invoice['bank_branch'] ?? '') ?>">
                </div>
                <div class="col-12 mb-20">
                    <label class="label fs-16 mb-2">Notes</label>
                    <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($invoice['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="admin-form-card mb-4">
            <div class="form-section-title">
                <span class="material-symbols-outlined">inventory_2</span>
                <h4>Products / Services</h4>
            </div>
            <div class="table-responsive">
                <table class="table align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Product / Service</th>
                            <th style="min-width:260px;">Description</th>
                            <th style="width:120px;">Qty</th>
                            <th style="width:100px;">Unit</th>
                            <th style="width:180px;">Unit Price</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i => $item): ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="items[<?= $i ?>][item_type]" value="<?= htmlspecialchars($item['item_type'] ?? 'product') ?>">
                                    <input type="hidden" name="items[<?= $i ?>][reference_id]" value="<?= htmlspecialchars((string) ($item['reference_id'] ?? '')) ?>">
                                    <input type="text" name="items[<?= $i ?>][item_name]" class="form-control" value="<?= htmlspecialchars($item['item_name'] ?? '') ?>">
                                </td>
                                <td><textarea name="items[<?= $i ?>][description]" class="form-control" rows="2"><?= htmlspecialchars($item['description'] ?? '') ?></textarea></td>
                                <td><input type="number" step="0.01" min="0" name="items[<?= $i ?>][quantity]" class="form-control qty-input" value="<?= htmlspecialchars(quotation_format_quantity($item['quantity'] ?? 1)) ?>"></td>
                                <td><input type="text" name="items[<?= $i ?>][unit]" class="form-control" value="<?= htmlspecialchars($item['unit'] ?? 'pcs') ?>"></td>
                                <td><input type="text" inputmode="decimal" name="items[<?= $i ?>][unit_price]" class="form-control money-input" value="<?= htmlspecialchars(quotation_format_plain_number($item['unit_price'] ?? 0)) ?>"></td>
                                <td class="text-center">
                                    <button type="button" class="btn item-row-btn item-remove-btn" aria-label="Remove item">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
                    <input type="text" inputmode="decimal" name="discount" class="form-control money-input" value="<?= htmlspecialchars(quotation_format_plain_number($invoice['discount'] ?? 0)) ?>">
                </div>
                <div class="col-md-3 mb-20">
                    <label class="label fs-16 mb-2">Tax</label>
                    <input type="text" inputmode="decimal" name="tax" class="form-control money-input" value="<?= htmlspecialchars(quotation_format_plain_number($invoice['tax'] ?? 0)) ?>">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= admin_url('invoices/') ?>" class="btn btn-danger text-white">Cancel</a>
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

function bindMoneyInput(input) {
    input.value = formatNumber(input.value);
    input.addEventListener('blur', () => {
        input.value = formatNumber(input.value);
    });
}

document.querySelectorAll('.money-input').forEach(bindMoneyInput);

function renumberItemRows() {
    document.querySelectorAll('#items-table tbody tr').forEach((row, index) => {
        row.querySelectorAll('[name^="items["]').forEach((field) => {
            field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
        });
    });
}

function clearItemRow(row) {
    row.querySelector('[name$="[item_type]"]').value = 'product';
    row.querySelector('[name$="[reference_id]"]').value = '';
    row.querySelector('[name$="[item_name]"]').value = '';
    row.querySelector('[name$="[description]"]').value = '';
    row.querySelector('[name$="[quantity]"]').value = '1';
    row.querySelector('[name$="[unit]"]').value = 'pcs';
    row.querySelector('[name$="[unit_price]"]').value = '0';
}

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
    row.querySelectorAll('.money-input').forEach(bindMoneyInput);
});

document.querySelector('.admin-form')?.addEventListener('submit', () => {
    document.querySelectorAll('.money-input').forEach((input) => {
        input.value = normalizeNumber(input.value);
    });
});
</script>
