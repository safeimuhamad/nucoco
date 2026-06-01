<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$page = 'product';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

$name = '';
$slug = '';
$language = 'en';
$category = '';
$price = '';
$status = 'draft';
$seo_keywords = '';
$description = '';
$quotation_description = '';
product_categories_ensure_schema($conn);
$product_category_labels = product_category_labels($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = generate_slug($name);
    $language = trim($_POST['language'] ?? 'en');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $seo_keywords = trim($_POST['seo_keywords'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $description = trim($_POST['description'] ?? '');
    $quotation_description = trim($_POST['quotation_description'] ?? '');

    $allowed_status = ['publish', 'draft'];
    $allowed_language = ['id', 'en'];

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Product image is required.';
    }

    if ($name === '') {
        $error = 'Product title is required.';
    } elseif (!in_array($language, $allowed_language, true)) {
        $error = 'Invalid language.';
    } elseif ($category === '') {
        $error = 'Category is required.';
    } elseif ($price === '' || !is_numeric($price)) {
        $error = 'A valid price is required.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid product status.';
    } else {
        $image_name = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $original_name = $_FILES['image']['name'];
                $tmp_name = $_FILES['image']['tmp_name'];
                $file_size = $_FILES['image']['size'];

                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if ($ext === '') {
                    $error = 'File extension could not be detected.';
                } elseif (!in_array($ext, $allowed_ext, true)) {
                    $error = 'Only JPG, JPEG, PNG, and WEBP files are allowed.';
                } elseif ($file_size > 2 * 1024 * 1024) {
                    $error = 'Image size must not exceed 2MB.';
                } else {
                    $upload_dir = __DIR__ . '/../../uploads/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $image_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $image_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    }
                }
            } else {
                $error = 'An error occurred while uploading the image.';
            }
        }

        if ($error === '') {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO products (language, name, slug, category, price, image, status, description, quotation_description, seo_keywords, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssisssss",
                    $language,
                    $name,
                    $slug,
                    $category,
                    $price,
                    $image_name,
                    $status,
                    $description,
                    $quotation_description,
                    $seo_keywords
                );

                if (mysqli_stmt_execute($stmt)) {
                    header('Location: ./?success=created');
                    exit;
                } else {
                    $error = 'Failed to save product: ' . mysqli_error($conn);
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
        <h3 class="mb-0">Create Product</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="index.html" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Product</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Create Product</span>
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
                                <label class="label fs-16 mb-2">Product ID</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="Auto Generate" readonly>
                                    <label>Product ID</label>
                                </div>
                            </div>
                        </div>

                        <!-- NAME -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Product Title</label>
                                <div class="form-floating">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="name"
                                        value="<?= htmlspecialchars($name ?? '') ?>"
                                        required
                                    >
                                    <label>Product Title</label>
                                </div>
                            </div>
                        </div>

                        <!-- PRICE -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Price</label>
                                <div class="form-floating">
                                    <input
                                        type="number"
                                        class="form-control"
                                        name="price"
                                        id="product-price-input"
                                        value="<?= htmlspecialchars($price ?? '') ?>"
                                        inputmode="decimal"
                                        required
                                    >
                                    <label>Price</label>
                                </div>
                                <small class="text-secondary d-block mt-2" id="product-price-help">
                                    Currency: <?= product_currency_code($language ?? 'en') ?>.
                                </small>
                            </div>
                        </div>

                        <!-- SALE PRICE -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Sale Price</label>
                                <div class="form-floating">
                                    <input type="number" class="form-control">
                                    <label>Sale Price</label>
                                </div>
                                <small class="text-secondary">Optional field</small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <!-- LANGUAGE -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Language</label>
                        <div class="form-floating">
                            <select class="form-select" name="language" id="product-language-select" required>
                                <option value="en" <?= ($language === 'en') ? 'selected' : '' ?>>English</option>
                                <option value="id" <?= ($language === 'id') ? 'selected' : '' ?>>Indonesia</option>
                            </select>
                            <label>Language</label>
                        </div>
                    </div>

                    <!-- CATEGORY -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Category</label>
                        <div class="form-floating">
                            <select class="form-select" name="category" id="product-category-select" required>
                                <option value="">Select</option>
                                <?php foreach ($product_category_labels as $category_value => $labels): ?>
                                    <option
                                        value="<?= htmlspecialchars($category_value) ?>"
                                        data-label-en="<?= htmlspecialchars($labels['en']) ?>"
                                        data-label-id="<?= htmlspecialchars($labels['id']) ?>"
                                        <?= ($category === $category_value) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($labels[$language === 'id' ? 'id' : 'en']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label>Category</label>
                        </div>
                    </div>

                </div>


                <!-- DESCRIPTION -->
                <div class="col-8">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Web Description</label>
                        <textarea class="form-control" name="description" rows="6"><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Quotation Description</label>
                        <textarea class="form-control" name="quotation_description" rows="4" placeholder="Short description shown in quotations"><?= htmlspecialchars($quotation_description ?? '') ?></textarea>
                    </div>
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Upload Product Image</label>
                        <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp" required>
                        <small class="text-secondary d-block mt-2">
                            Recommended size: 300 x 300 px, square image, JPG/PNG/WebP, max 2MB.
                        </small>
                    </div>
                </div>
                <!-- SEO -->
                <div class="col-4">
                    <!-- STATUS -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Status</label>
                        <div class="form-floating">
                            <select class="form-select" name="status" required>
                                <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
                                <option value="publish" <?= ($status === 'publish') ? 'selected' : '' ?>>Publish</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SEO Keywords</label>
                        <textarea class="form-control" name="seo_keywords" rows="5"><?= htmlspecialchars($seo_keywords ?? '') ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">Save Product</button>
                        <a href="./" class="btn btn-danger text-white">Cancel</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex-grow-1"></div>
    <script>
        (() => {
            const languageSelect = document.getElementById('product-language-select');
            const categorySelect = document.getElementById('product-category-select');
            const priceHelp = document.getElementById('product-price-help');

            if (!languageSelect || !priceHelp) return;

            const syncPriceCurrency = () => {
                const currency = languageSelect.value === 'id' ? 'IDR' : 'USD';
                priceHelp.textContent = `Currency: ${currency}.`;
            };

            const syncCategoryLabels = () => {
                if (!categorySelect) return;
                const lang = languageSelect.value === 'id' ? 'id' : 'en';

                [...categorySelect.options].forEach((option) => {
                    if (!option.value) {
                        option.textContent = 'Select';
                        return;
                    }

                    option.textContent = (lang === 'id' ? option.dataset.labelId : option.dataset.labelEn) || option.textContent;
                });
            };

            languageSelect.addEventListener('change', () => {
                syncPriceCurrency();
                syncCategoryLabels();
            });
            syncPriceCurrency();
            syncCategoryLabels();
        })();
    </script>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
