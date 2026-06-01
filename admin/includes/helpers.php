<?php

function generate_slug($text)
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

if (!function_exists('product_currency_code')) {
    function product_currency_code($language)
    {
        return $language === 'id' ? 'IDR' : 'USD';
    }
}

if (!function_exists('product_format_price')) {
    function product_format_price($price, $language)
    {
        $amount = number_format((float) $price, 0, '.', ',');

        return product_currency_code($language) === 'IDR'
            ? 'Rp ' . $amount
            : 'USD ' . $amount;
    }
}

if (!function_exists('product_default_category_labels')) {
    function product_default_category_labels()
    {
        return [
            'Fresh Coconut' => [
                'en' => 'Fresh Coconut',
                'id' => 'Kelapa Segar',
            ],
            'Coconut Ingredients' => [
                'en' => 'Coconut Ingredients',
                'id' => 'Bahan Baku Kelapa',
            ],
            'Coconut Derivatives' => [
                'en' => 'Coconut Derivatives',
                'id' => 'Produk Turunan Kelapa',
            ],
            'Coconut Industrial Product' => [
                'en' => 'Coconut Industrial Product',
                'id' => 'Produk Industri Kelapa',
            ],
        ];
    }
}

if (!function_exists('product_category_labels')) {
    function product_category_labels($conn = null)
    {
        static $cached_labels = null;

        if ($cached_labels !== null) {
            return $cached_labels;
        }

        if ($conn === null && isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $conn = $GLOBALS['conn'];
        }

        if ($conn instanceof mysqli) {
            $table_check = @mysqli_query($conn, "SHOW TABLES LIKE 'product_categories'");
            if ($table_check && mysqli_num_rows($table_check) > 0) {
                $result = @mysqli_query(
                    $conn,
                    "SELECT category_key, label_en, label_id
                     FROM product_categories
                     WHERE status = 'active'
                     ORDER BY sort_order ASC, id ASC"
                );

                if ($result) {
                    $labels = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $key = trim($row['category_key'] ?? '');
                        if ($key === '') {
                            continue;
                        }

                        $labels[$key] = [
                            'en' => $row['label_en'] ?: $key,
                            'id' => $row['label_id'] ?: ($row['label_en'] ?: $key),
                        ];
                    }

                    if ($labels) {
                        $cached_labels = $labels;
                        return $cached_labels;
                    }
                }
            }
        }

        $cached_labels = product_default_category_labels();
        return $cached_labels;
    }
}

if (!function_exists('product_categories_ensure_schema')) {
    function product_categories_ensure_schema($conn = null)
    {
        if ($conn === null && isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $conn = $GLOBALS['conn'];
        }

        if (!$conn instanceof mysqli) {
            return false;
        }

        mysqli_query(
            $conn,
            "CREATE TABLE IF NOT EXISTS `product_categories` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `category_key` varchar(100) NOT NULL,
              `label_en` varchar(150) NOT NULL,
              `label_id` varchar(150) NOT NULL,
              `sort_order` int(11) NOT NULL DEFAULT 0,
              `status` enum('active','inactive') NOT NULL DEFAULT 'active',
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `category_key` (`category_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );

        foreach (product_default_category_labels() as $key => $labels) {
            $sort_order = [
                'Fresh Coconut' => 10,
                'Coconut Ingredients' => 20,
                'Coconut Derivatives' => 30,
                'Coconut Industrial Product' => 40,
            ][$key] ?? 100;

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO product_categories (category_key, label_en, label_id, sort_order, status)
                 VALUES (?, ?, ?, ?, 'active')
                 ON DUPLICATE KEY UPDATE
                    label_en = VALUES(label_en),
                    label_id = VALUES(label_id),
                    sort_order = VALUES(sort_order),
                    status = VALUES(status)"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sssi', $key, $labels['en'], $labels['id'], $sort_order);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        return true;
    }
}

if (!function_exists('product_category_label')) {
    function product_category_label($category, $language)
    {
        $labels = product_category_labels();
        $language = $language === 'id' ? 'id' : 'en';

        return $labels[$category][$language] ?? $category;
    }
}

if (!function_exists('product_default_quotation_description')) {
    function product_default_quotation_description($name, $language = 'en', $type = 'product')
    {
        $name = trim((string) $name);
        $item = $name !== '' ? $name : ($type === 'service' ? 'service' : 'product');
        $language = $language === 'id' ? 'id' : 'en';

        if ($type === 'service') {
            return $language === 'id'
                ? 'Lingkup layanan standar untuk ' . $item . '. Cocok untuk kebutuhan penawaran dan operasional.'
                : 'Standard service scope for ' . $item . '. Suitable for quotation and operational requirements.';
        }

        return $language === 'id'
            ? 'Spesifikasi standar untuk ' . $item . '. Cocok untuk kebutuhan penawaran dan pengadaan.'
            : 'Standard specification for ' . $item . '. Suitable for quotation and supply requirements.';
    }
}

function convert_to_webp($source, $destination, $quality = 80)
{
    if (!file_exists($source)) {
        return false;
    }

    if (!function_exists('imagewebp')) {
        return false;
    }

    $info = getimagesize($source);
    if ($info === false || empty($info['mime'])) {
        return false;
    }

    $image = null;

    if ($info['mime'] === 'image/jpeg') {
        if (!function_exists('imagecreatefromjpeg')) {
            return false;
        }
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] === 'image/png') {
        if (!function_exists('imagecreatefrompng')) {
            return false;
        }
        $image = imagecreatefrompng($source);

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    } elseif ($info['mime'] === 'image/webp') {
        return copy($source, $destination);
    } else {
        return false;
    }

    if (!$image) {
        return false;
    }

    $result = imagewebp($image, $destination, $quality);
    imagedestroy($image);

    return $result;
}

function resize_to_webp($source, $destination, $target_width, $quality = 80)
{
    if (!file_exists($source)) {
        return false;
    }

    $info = getimagesize($source);
    if (!$info) {
        return false;
    }

    $width  = $info[0];
    $height = $info[1];
    $type   = $info[2];

    // Jangan upscale gambar kecil
    $target_width = min($target_width, $width);

    // Hitung tinggi setelah target_width final
    $ratio = $height / $width;
    $target_height = (int) round($target_width * $ratio);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;

        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;

        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($source);
            break;

        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    $resized = imagecreatetruecolor($target_width, $target_height);

    imagecopyresampled(
        $resized,
        $image,
        0,
        0,
        0,
        0,
        $target_width,
        $target_height,
        $width,
        $height
    );

    $result = imagewebp($resized, $destination, $quality);

    imagedestroy($image);
    imagedestroy($resized);

    return $result;
}
