<div class="main-content-container overflow-hidden">
    <div class="form-page-head">
        <div class="form-top-actions">
            <a href="<?= admin_url('product-categories/') ?>" class="form-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back</span>
            </a>
        </div>
        <div class="form-title-row">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h3><?= $mode === 'edit' ? 'Edit Product Category' : 'Create Product Category' ?></h3>
                <?php if (!empty($category['category_key'])): ?>
                    <span class="form-title-badge"><?= htmlspecialchars($category['category_key']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" class="admin-form">
        <?= csrf_field() ?>
        <div class="admin-form-card mb-4">
            <div class="form-section-title">
                <span class="material-symbols-outlined">category</span>
                <h4>Category Information</h4>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-20">
                    <label class="label fs-16 mb-2">English Name</label>
                    <input type="text" name="label_en" class="form-control" value="<?= htmlspecialchars($category['label_en'] ?? '') ?>" required>
                </div>
                <div class="col-lg-6 col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Indonesia Name</label>
                    <input type="text" name="label_id" class="form-control" value="<?= htmlspecialchars($category['label_id'] ?? '') ?>" required>
                </div>
                <div class="col-lg-6 col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Category Key</label>
                    <input type="text" name="category_key" class="form-control" value="<?= htmlspecialchars($category['category_key'] ?? '') ?>" placeholder="Auto from English name if empty">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string) ($category['sort_order'] ?? 0)) ?>">
                </div>
                <div class="col-lg-3 col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= (($category['status'] ?? 'active') === $value) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= admin_url('product-categories/') ?>" class="btn btn-danger text-white">Cancel</a>
            <button class="btn btn-primary text-white" type="submit">
                <span class="material-symbols-outlined">save</span>
                <span>Save</span>
            </button>
        </div>
    </form>
</div>
