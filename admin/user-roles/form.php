<?php
$role = $role ?? ['role_key' => '', 'role_name' => '', 'description' => '', 'status' => 'active'];
?>
<form method="POST" class="card bg-white rounded-10 border border-white p-20">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-6 mb-20">
            <label class="label fs-16 mb-2">Role Key</label>
            <input class="form-control" name="role_key" value="<?= htmlspecialchars($role['role_key'] ?? '') ?>" required>
        </div>
        <div class="col-md-6 mb-20">
            <label class="label fs-16 mb-2">Role Name</label>
            <input class="form-control" name="role_name" value="<?= htmlspecialchars($role['role_name'] ?? '') ?>" required>
        </div>
        <div class="col-md-6 mb-20">
            <label class="label fs-16 mb-2">Status</label>
            <select class="form-select" name="status">
                <option value="active" <?= ($role['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($role['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-12 mb-20">
            <label class="label fs-16 mb-2">Description</label>
            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($role['description'] ?? '') ?></textarea>
        </div>
    </div>
    <div class="form-actions">
        <a href="<?= admin_url('user-roles/') ?>" class="btn btn-danger text-white">Cancel</a>
        <button class="btn btn-primary text-white" type="submit">Save Role</button>
    </div>
</form>
