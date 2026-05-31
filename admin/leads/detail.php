<?php
$page = 'leads';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$lead = db_select_one("SELECT * FROM leads WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$lead) {
    header('Location: ' . admin_url('leads/?error=not_found'));
    exit;
}
$quotations = db_select_all("SELECT * FROM quotations WHERE lead_id = ? ORDER BY id DESC", 'i', [$id]);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Leads', admin_url('leads/'), 'Lead Detail', $lead['name'], [
        ['label' => 'Create Quotation', 'url' => admin_url('quotations/create.php?lead_id=' . $id), 'icon' => 'send', 'class' => 'btn detail-btn detail-btn-outline-success'],
        ['label' => 'Edit', 'url' => admin_url('leads/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('leads/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this lead?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Name', 'value' => $lead['name'], 'icon' => 'person', 'meta' => $lead['company'] ?: '-'],
        ['label' => 'Contact', 'value' => $lead['email'] ?: '-', 'icon' => 'mail', 'meta' => $lead['phone'] ?: '-'],
        ['label' => 'Status', 'value' => ucfirst($lead['status']), 'icon' => 'flag', 'meta' => count($quotations) . ' quotations'],
    ]);
    ?>
    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Lead <?= htmlspecialchars($_GET['success']) ?> successfully.</div><?php endif; ?>
    <div class="row">
        <div class="col-lg-7">
            <?php detail_card_open('Lead Information', 'badge'); ?>
                <?php detail_fields([
                    'Name' => detail_text($lead['name']),
                    'Email' => detail_text($lead['email']),
                    'Phone' => detail_text($lead['phone']),
                    'Company' => detail_text($lead['company']),
                    'Status' => '<span class="' . detail_badge_class($lead['status']) . '">' . detail_text(ucfirst($lead['status'])) . '</span>',
                ]); ?>
                <hr>
                <div class="detail-body-text"><?= nl2br(detail_text($lead['message'])) ?></div>
            <?php detail_card_close(); ?>
        </div>
        <div class="col-lg-5">
            <?php detail_card_open('Related Quotations', 'request_quote'); ?>
            <?php if ($quotations): foreach ($quotations as $quote): ?>
                <p class="d-flex justify-content-between align-items-center gap-3 mb-2">
                    <a class="text-primary" href="<?= admin_url('quotations/detail.php?id=' . (int) $quote['id']) ?>"><?= htmlspecialchars($quote['quote_number']) ?></a>
                    <span class="<?= detail_badge_class($quote['status']) ?>"><?= htmlspecialchars(ucfirst($quote['status'])) ?></span>
                </p>
            <?php endforeach; else: ?>
                <p class="text-secondary">No quotation yet.</p>
            <?php endif; ?>
            <?php detail_card_close(); ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
