<?php
$page = 'inbox';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid inquiry ID.');
}

/*
|--------------------------------------------------------------------------
| Load existing inquiry
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM inquiries WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inquiry = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$inquiry) {
    die('Inquiry not found.');
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$name = $inquiry['name'] ?? '';
$email = $inquiry['email'] ?? '';
$phone = $inquiry['phone'] ?? '';
$service_type = $inquiry['service_type'] ?? '';
$message = $inquiry['message'] ?? '';
$status = $inquiry['status'] ?? 'new';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $service_type = trim($_POST['service_type'] ?? '');
    $message      = trim($_POST['message'] ?? '');
    $status       = trim($_POST['status'] ?? 'new');

    $allowed_status = ['new', 'read', 'replied'];

    if ($name === '') {
        $error = 'Name is required.';
    } elseif ($email === '') {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($message === '') {
        $error = 'Message is required.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid inquiry status.';
    } else {
        /*
        |--------------------------------------------------------------------------
        | Update inquiry
        |--------------------------------------------------------------------------
        */
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE inquiries
             SET name = ?, email = ?, phone = ?, service_type = ?, message = ?, status = ?, updated_at = NOW()
             WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssi",
                $name,
                $email,
                $phone,
                $service_type,
                $message,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                header('Location: ./?success=updated');
                exit;
            } else {
                $error = 'Failed to update inquiry: ' . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = 'Failed to prepare query: ' . mysqli_error($conn);
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Edit Inquiry</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Inquiry</span>
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <span class="text-secondary">Edit Inquiry</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="card bg-white p-20 rounded-10 border border-white mb-4">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">

                <!-- LEFT -->
                <div class="col-lg-8">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Name</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="name"
                                        value="<?= htmlspecialchars($name) ?>" readonly>
                                    <label>Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Email</label>
                                <div class="form-floating">
                                    <input type="email" class="form-control" name="email"
                                        value="<?= htmlspecialchars($email) ?>" readonly>
                                    <label>Email</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Phone</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="phone"
                                        value="<?= htmlspecialchars($phone) ?>" readonly>
                                    <label>Phone</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Service Type</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="service_type"
                                        value="<?= htmlspecialchars($service_type) ?>" readonly>
                                    <label>Service Type</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Status</label>
                        <div class="form-floating">
                            <select class="form-select" name="status">
                                <option value="new" <?= ($status == 'new') ? 'selected' : '' ?>>New</option>
                                <option value="read" <?= ($status == 'read') ? 'selected' : '' ?>>Read</option>
                                <option value="replied" <?= ($status == 'replied') ? 'selected' : '' ?>>Replied</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>

                </div>

                <!-- MESSAGE -->
                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Message</label>
                        <textarea class="form-control" name="message" rows="6"><?= htmlspecialchars($message) ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">
                            Update Inbox
                        </button>
                        <a href="./" class="btn btn-danger text-white">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </form>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>