<?php
$page = 'team';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid team member ID.');
}

/*
|--------------------------------------------------------------------------
| Load existing team member
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM team_members WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$member) {
    die('Team member not found.');
}

/*
|--------------------------------------------------------------------------
| Default form values from DB
|--------------------------------------------------------------------------
*/
$name = $member['name'] ?? '';
$position = $member['position'] ?? '';
$facebook = $member['facebook'] ?? '';
$twitter = $member['twitter'] ?? '';
$linkedin = $member['linkedin'] ?? '';
$display_order = $member['display_order'] ?? 0;
$status = $member['status'] ?? 'inactive';
$current_photo = $member['photo'] ?? '';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $position      = trim($_POST['position'] ?? '');
    $facebook      = trim($_POST['facebook'] ?? '');
    $twitter       = trim($_POST['twitter'] ?? '');
    $linkedin      = trim($_POST['linkedin'] ?? '');
    $display_order = trim($_POST['display_order'] ?? '0');
    $status        = trim($_POST['status'] ?? 'inactive');

    $allowed_status = ['active', 'inactive'];

    if ($name === '') {
        $error = 'Member name is required.';
    } elseif ($position === '') {
        $error = 'Position is required.';
    } elseif ($display_order !== '' && !is_numeric($display_order)) {
        $error = 'Display order must be a valid number.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid member status.';
    } else {
        $photo_name = $current_photo;

        /*
        |--------------------------------------------------------------------------
        | Upload new photo (optional)
        |--------------------------------------------------------------------------
        */
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $original_name = $_FILES['photo']['name'];
                $tmp_name      = $_FILES['photo']['tmp_name'];
                $file_size     = $_FILES['photo']['size'];

                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if ($ext === '') {
                    $error = 'File extension could not be detected.';
                } elseif (!in_array($ext, $allowed_ext, true)) {
                    $error = 'Only JPG, JPEG, PNG, and WEBP files are allowed.';
                } elseif ($file_size > 2 * 1024 * 1024) {
                    $error = 'Image size must not exceed 2MB.';
                } else {
                    $upload_dir = __DIR__ . '/../../uploads/team/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $new_photo_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $new_photo_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    } else {
                        if (!empty($current_photo)) {
                            $old_photo_path = $upload_dir . $current_photo;
                            if (file_exists($old_photo_path)) {
                                @unlink($old_photo_path);
                            }
                        }

                        $photo_name = $new_photo_name;
                    }
                }
            } else {
                $error = 'An error occurred while uploading the image.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update team member
        |--------------------------------------------------------------------------
        */
        if ($error === '') {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE team_members
                 SET name = ?, position = ?, photo = ?, facebook = ?, twitter = ?, linkedin = ?, display_order = ?, status = ?
                 WHERE id = ?"
            );

            if ($stmt) {
                $display_order_int = (int) $display_order;

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssssisi",
                    $name,
                    $position,
                    $photo_name,
                    $facebook,
                    $twitter,
                    $linkedin,
                    $display_order_int,
                    $status,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {
                    header('Location: ./?success=updated');
                    exit;
                } else {
                    $error = 'Failed to update team member: ' . mysqli_error($conn);
                }

                mysqli_stmt_close($stmt);
            } else {
                $error = 'Failed to prepare query: ' . mysqli_error($conn);
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Edit Team Member</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Team</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Edit Team Member</span>
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

        <form method="POST" enctype="multipart/form-data">
            <div class="row">

                <!-- LEFT -->
                <div class="col-lg-8">
                    <div class="row">

                        <!-- ID -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Member ID</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="<?= $id ?>" readonly>
                                    <label>Member ID</label>
                                </div>
                            </div>
                        </div>

                        <!-- NAME -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Full Name</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="name"
                                        value="<?= htmlspecialchars($name) ?>" required>
                                    <label>Full Name</label>
                                </div>
                            </div>
                        </div>

                        <!-- POSITION -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Position</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="position"
                                        value="<?= htmlspecialchars($position) ?>" required>
                                    <label>Position</label>
                                </div>
                            </div>
                        </div>

                        <!-- DISPLAY ORDER -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Display Order</label>
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="display_order"
                                        value="<?= htmlspecialchars($display_order) ?>" required>
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
                                <option value="inactive" <?= ($status == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Active</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>

                    <!-- CURRENT PHOTO -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Current Photo</label>
                        <?php if (!empty($current_photo)): ?>
                            <div class="mb-3">
                                <img src="<?= $base_url ?>uploads/team/<?= htmlspecialchars($current_photo) ?>" width="100">
                            </div>
                        <?php else: ?>
                            <p class="text-secondary mb-2">No photo uploaded.</p>
                        <?php endif; ?>

                        <input type="file" class="form-control" name="photo" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-secondary">Leave empty if you don’t want to change photo</small>
                    </div>

                </div>

                <!-- FACEBOOK -->
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Facebook URL</label>
                        <div class="form-floating">
                            <input type="url" class="form-control" name="facebook"
                                value="<?= htmlspecialchars($facebook) ?>">
                            <label>Facebook URL</label>
                        </div>
                    </div>
                </div>

                <!-- TWITTER -->
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Twitter URL</label>
                        <div class="form-floating">
                            <input type="url" class="form-control" name="twitter"
                                value="<?= htmlspecialchars($twitter) ?>">
                            <label>Twitter URL</label>
                        </div>
                    </div>
                </div>

                <!-- LINKEDIN -->
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">LinkedIn URL</label>
                        <div class="form-floating">
                            <input type="url" class="form-control" name="linkedin"
                                value="<?= htmlspecialchars($linkedin) ?>">
                            <label>LinkedIn URL</label>
                        </div>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">
                            Update Team Member
                        </button>
                        <a href="./" class="btn btn-danger text-white">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex-grow-1"></div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>