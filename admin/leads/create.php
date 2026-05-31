<?php
$page = 'leads';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$lead = ['name'=>'','email'=>'','phone'=>'','company'=>'','source'=>'manual','interest_type'=>'','message'=>'','status'=>'new'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    foreach ($lead as $key => $value) {
        $lead[$key] = trim($_POST[$key] ?? $value);
    }

    if ($lead['name'] === '') {
        $error = 'Name is required.';
    } elseif ($lead['email'] !== '' && !filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email.';
    } else {
        $id = db_insert(
            "INSERT INTO leads (name, email, phone, company, source, interest_type, message, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'ssssssssi',
            [$lead['name'], $lead['email'], $lead['phone'], $lead['company'], $lead['source'], $lead['interest_type'], $lead['message'], $lead['status'], (int) current_user_id()]
        );
        header('Location: ' . admin_url('leads/detail.php?id=' . $id . '&success=created'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/form.php';
include __DIR__ . '/../includes/footer.php';
