<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$page = 'faq';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

$language = '';
$question = '';
$answer = '';
$display_order = '0';
$status = 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language      = trim($_POST['language'] ?? '');
    $question      = trim($_POST['question'] ?? '');
    $answer        = trim($_POST['answer'] ?? '');
    $display_order = trim($_POST['display_order'] ?? '0');
    $status        = trim($_POST['status'] ?? 'active');

    $allowed_status = ['active', 'inactive'];
    $allowed_languages = ['Indonesia', 'English'];

    if ($language === '') {
        $error = 'Language is required.';
    } elseif (!in_array($language, $allowed_languages, true)) {
        $error = 'Invalid language.';
    } elseif ($question === '') {
        $error = 'Question is required.';
    } elseif ($answer === '') {
        $error = 'Answer is required.';
    } elseif ($display_order !== '' && !is_numeric($display_order)) {
        $error = 'Display order must be a valid number.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid FAQ status.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO faqs (language, question, answer, display_order, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        if ($stmt) {
            $display_order_int = (int) $display_order;

            mysqli_stmt_bind_param(
                $stmt,
                "sssis",
                $language,
                $question,
                $answer,
                $display_order_int,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: ./?success=created');
                exit;
            } else {
                $error = 'Failed to save FAQ: ' . mysqli_error($conn);
                mysqli_stmt_close($stmt);
            }
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
        <h3 class="mb-0">Create FAQ</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">FAQ</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Create FAQ</span>
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

                        <!-- ID -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">FAQ ID</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="Auto Generate" readonly>
                                    <label>FAQ ID</label>
                                </div>
                            </div>
                        </div>

                        <!-- LANGUAGE -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Language</label>
                                <div class="form-floating">
                                    <select class="form-select" name="language" required>
                                        <option value="Indonesia" <?= ($language === 'Indonesia') ? 'selected' : '' ?>>Indonesia</option>
                                        <option value="English" <?= ($language === 'English') ? 'selected' : '' ?>>English</option>
                                    </select>
                                    <label>Language</label>
                                </div>
                            </div>
                        </div>

                        <!-- QUESTION -->
                        <div class="col-md-12">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Question</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="question"
                                        value="<?= htmlspecialchars($question) ?>" required>
                                    <label>Question</label>
                                </div>
                            </div>
                        </div>

                        <!-- DISPLAY ORDER -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Display Order</label>
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="display_order"
                                        value="<?= htmlspecialchars((string)$display_order) ?>" required>
                                    <label>Display Order</label>
                                </div>
                                <small class="text-secondary">Smaller number will appear first</small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <!-- STATUS -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Status</label>
                        <div class="form-floating">
                            <select class="form-select" name="status" required>
                                <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>

                </div>

                <!-- ANSWER -->
                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Answer</label>
                        <textarea class="form-control" name="answer" rows="6" required><?= htmlspecialchars($answer) ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">Save FAQ</button>
                        <a href="./" class="btn btn-danger text-white">Cancel</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex-grow-1"></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>