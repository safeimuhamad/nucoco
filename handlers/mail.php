<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';

// ======================================
// VALIDASI REQUEST
// ======================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.php');
    exit;
}

// ======================================
// AMBIL DATA
// ======================================
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service_type = trim($_POST['service_type'] ?? '');
$message = trim($_POST['message'] ?? '');

$phone = preg_replace('/[^0-9+\-\s]/', '', $phone);

// ======================================
// VALIDASI
// ======================================
$allowed_service_types = [
    'OEM & Private Label',
    'Global Export & Supply',
    'Industrial Bulk Supply',
    'Product Customization',
    'Sourcing & Consolidation',
    'Quality & Certification Support',
    'General Inquiry'
];

$error = '';

if ($name === '') {
    $error = 'Name is required.';
} elseif ($email === '') {
    $error = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address.';
} elseif ($service_type === '') {
    $error = 'Please select a service type.';
} elseif (!in_array($service_type, $allowed_service_types, true)) {
    $error = 'Invalid service type.';
} elseif ($message === '') {
    $error = 'Message is required.';
}

if ($error !== '') {
    header('Location: ../contact.php?error=' . urlencode($error));
    exit;
}

// ======================================
// 1. SIMPAN KE DATABASE
// ======================================
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO inquiries (name, email, phone, service_type, message, status, created_at)
     VALUES (?, ?, ?, ?, ?, 'new', NOW())"
);

if (!$stmt) {
    header('Location: ../contact.php?error=' . urlencode('Database prepare error: ' . mysqli_error($conn)));
    exit;
}

mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $service_type, $message);

if (!mysqli_stmt_execute($stmt)) {
    $db_error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    header('Location: ../contact.php?error=' . urlencode('Failed to save data: ' . $db_error));
    exit;
}

mysqli_stmt_close($stmt);

// ======================================
// 2. KIRIM EMAIL (ADMIN + AUTO REPLY)
// ======================================
$email_error = '';

try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    // SMTP CONFIG
    $mail->isSMTP();
    $mail->Host       = 'mail.nucoco.id'; // jika gagal, coba ganti ke mail.nucoco.id
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@nucoco.id';
    $mail->Password   = 'M7f@s4r21';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom('info@nucoco.id', 'Nucoco.id');
    $mail->isHTML(true);

    /*
    |--------------------------------------------------------------------------
    | EMAIL 1: KE ADMIN
    |--------------------------------------------------------------------------
    */
    $mail->clearAddresses();
    $mail->addAddress('info@nucoco.id'); // atau sales@nucoco.id kalau ada

    $mail->Subject = 'New Inquiry - Nucoco.id';
    $mail->Body = "
        <h3>New Inquiry Received</h3>
        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
        <p><strong>Service Type:</strong> " . htmlspecialchars($service_type) . "</p>
        <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
    ";

    $mail->send();

    /*
    |--------------------------------------------------------------------------
    | EMAIL 2: AUTO REPLY KE USER
    |--------------------------------------------------------------------------
    */
    $mail->clearAddresses();
    $mail->addAddress($email, $name);

    $mail->Subject = 'Thank You for Contacting Nucoco.id';
    $mail->Body = "
        <h3>Thank you for your inquiry</h3>
        <p>Dear " . htmlspecialchars($name) . ",</p>

        <p>Thank you for contacting <strong>Nucoco.id</strong>. We have received your inquiry and our team will review it shortly.</p>

        <p>Our team will get back to you as soon as possible with the best solution for your request.</p>

        <br>

        <p><strong>Your Inquiry Summary:</strong></p>
        <p><strong>Service:</strong> " . htmlspecialchars($service_type) . "</p>
        <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>

        <br>

        <p>Best regards,<br>
        <strong>Nucoco.id</strong></p>
    ";

    $mail->send();

} catch (\PHPMailer\PHPMailer\Exception $e) {
    $email_error = $e->getMessage();
}

// ======================================
// 3. REDIRECT
// ======================================
if ($email_error !== '') {
    header('Location: ../contact.php?success=1&mail_error=' . urlencode($email_error));
    exit;
}

header('Location: ../contact.php?success=1');
exit;