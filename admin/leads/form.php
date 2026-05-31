<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0"><?= isset($id) ? 'Edit Lead' : 'Create Lead' ?></h3>
        <a href="<?= admin_url('leads/') ?>" class="btn btn-outline-primary">Back</a>
    </div>

    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" class="card bg-white p-20 rounded-10 border border-white mb-4">
        <?= csrf_field() ?>
        <div class="row">
            <div class="col-md-6 mb-20"><label class="label fs-16 mb-2">Name</label><input class="form-control" name="name" value="<?= htmlspecialchars($lead['name'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-20"><label class="label fs-16 mb-2">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($lead['email'] ?? '') ?>"></div>
            <div class="col-md-6 mb-20"><label class="label fs-16 mb-2">Phone</label><input class="form-control" name="phone" value="<?= htmlspecialchars($lead['phone'] ?? '') ?>"></div>
            <div class="col-md-6 mb-20"><label class="label fs-16 mb-2">Company</label><input class="form-control" name="company" value="<?= htmlspecialchars($lead['company'] ?? '') ?>"></div>
            <div class="col-md-4 mb-20"><label class="label fs-16 mb-2">Source</label><input class="form-control" name="source" value="<?= htmlspecialchars($lead['source'] ?? 'manual') ?>"></div>
            <div class="col-md-4 mb-20"><label class="label fs-16 mb-2">Interest</label><input class="form-control" name="interest_type" value="<?= htmlspecialchars($lead['interest_type'] ?? '') ?>"></div>
            <div class="col-md-4 mb-20">
                <label class="label fs-16 mb-2">Status</label>
                <select class="form-select" name="status">
                    <?php foreach (['new','contacted','qualified','proposal','won','lost'] as $option): ?>
                        <option value="<?= $option ?>" <?= ($lead['status'] ?? 'new') === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 mb-20"><label class="label fs-16 mb-2">Message</label><textarea class="form-control" name="message" rows="5"><?= htmlspecialchars($lead['message'] ?? '') ?></textarea></div>
        </div>
        <button class="btn btn-primary text-white" type="submit">Save Lead</button>
    </form>
</div>
