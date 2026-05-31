<?php
$page = 'leads';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$lead = db_select_one("SELECT * FROM leads WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$lead) {
    header('Location: ' . admin_url('leads/?error=not_found'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    foreach (['name','email','phone','company','source','interest_type','message','status'] as $key) {
        $lead[$key] = trim($_POST[$key] ?? '');
    }

    if ($lead['name'] === '') {
        $error = 'Name is required.';
    } elseif ($lead['email'] !== '' && !filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email.';
    } else {
        db_update(
            "UPDATE leads SET name=?, email=?, phone=?, company=?, source=?, interest_type=?, message=?, status=?, updated_by=? WHERE id=?",
            'ssssssssii',
            [$lead['name'], $lead['email'], $lead['phone'], $lead['company'], $lead['source'], $lead['interest_type'], $lead['message'], $lead['status'], (int) current_user_id(), $id]
        );
        header('Location: ' . admin_url('leads/detail.php?id=' . $id . '&success=updated'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/form.php';
include __DIR__ . '/../includes/footer.php';
