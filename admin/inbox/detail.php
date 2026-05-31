<?php
$page = 'inbox';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . admin_url('inbox/?error=invalid_id'));
    exit;
}

$inquiry = db_select_one("SELECT * FROM inquiries WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$inquiry) {
    header('Location: ' . admin_url('inbox/?error=not_found'));
    exit;
}

db_update("UPDATE inquiries SET status = 'read', updated_at = NOW() WHERE id = ? AND status = 'new'", 'i', [$id]);
$lead = db_select_one("SELECT * FROM leads WHERE inquiry_id = ? LIMIT 1", 'i', [$id]);
$quotations = db_select_all("SELECT * FROM quotations WHERE inquiry_id = ? ORDER BY id DESC", 'i', [$id]);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Inbox', admin_url('inbox/'), 'Inbox Detail', $inquiry['name'], [
        ['label' => 'Create Quotation', 'url' => admin_url('quotations/create.php?inquiry_id=' . (int) $inquiry['id']), 'icon' => 'send', 'class' => 'btn detail-btn detail-btn-outline-success'],
        ['label' => 'Edit', 'url' => admin_url('inbox/edit.php?id=' . (int) $inquiry['id']), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('inbox/delete.php?id=' . (int) $inquiry['id']), 'icon' => 'delete', 'confirm' => 'Delete this inbox?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Sender', 'value' => $inquiry['name'], 'icon' => 'person', 'meta' => $inquiry['email']],
        ['label' => 'Service', 'value' => $inquiry['service_type'] ?: '-', 'icon' => 'support_agent', 'meta' => $inquiry['phone'] ?: '-'],
        ['label' => 'Date', 'value' => date('d M Y H:i', strtotime($inquiry['created_at'])), 'icon' => 'calendar_month', 'meta' => ucfirst($inquiry['status'])],
        ['label' => 'Lead', 'value' => $lead ? 'Converted' : 'Not converted', 'icon' => 'flag', 'meta' => count($quotations) . ' quotations'],
    ]);
    ?>

    <div class="row">
        <div class="col-lg-8">
            <?php detail_card_open('Incoming Message', 'mail'); ?>
                <?php detail_fields([
                    'Name' => detail_text($inquiry['name']),
                    'Email' => detail_text($inquiry['email']),
                    'Phone' => detail_text($inquiry['phone']),
                    'Service Type' => detail_text($inquiry['service_type']),
                ]); ?>
                <hr>
                <div class="detail-body-text"><?= nl2br(detail_text($inquiry['message'])) ?></div>
            <?php detail_card_close(); ?>
        </div>

        <div class="col-lg-4">
            <?php detail_card_open('Sales Status', 'monitoring'); ?>
                <?php detail_fields([
                    'Inbox Status' => '<span class="' . detail_badge_class($inquiry['status']) . '">' . detail_text(ucfirst($inquiry['status'])) . '</span>',
                    'Date' => detail_text(date('d M Y H:i', strtotime($inquiry['created_at']))),
                    'Lead' => $lead ? '<span class="detail-badge detail-badge-success">Converted</span>' : '<span class="detail-badge detail-badge-muted">Not converted</span>',
                ]); ?>
            <?php detail_card_close(); ?>

            <?php detail_card_open('Related Quotations', 'request_quote'); ?>
                <?php if ($quotations): ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($quotations as $quote): ?>
                            <li class="mb-2">
                                <a href="<?= admin_url('quotations/detail.php?id=' . (int) $quote['id']) ?>">
                                    <?= htmlspecialchars($quote['quote_number']) ?>
                                </a>
                                <span class="text-secondary">(<?= htmlspecialchars($quote['status']) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="mb-0 text-secondary">No quotation yet.</p>
                <?php endif; ?>
            <?php detail_card_close(); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
