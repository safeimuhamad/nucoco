<?php

function generate_slug($text)
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
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

