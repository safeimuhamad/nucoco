<?php
$page = 'setting';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

/*
|--------------------------------------------------------------------------
| Load existing config (1 row)
|--------------------------------------------------------------------------
*/
$query = mysqli_query($conn, "SELECT * FROM web_config LIMIT 1");
if (!$query) {
    die('Query error: ' . mysqli_error($conn));
}
$config = mysqli_fetch_assoc($query);

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$site_name            = $config['site_name'] ?? '';
$company_name         = $config['company_name'] ?? '';
$tagline              = $config['tagline'] ?? '';
$site_description     = $config['site_description'] ?? '';
$logo                 = $config['logo'] ?? '';
$logo_white           = $config['logo_white'] ?? '';
$favicon              = $config['favicon'] ?? '';
$email                = $config['email'] ?? '';
$phone                = $config['phone'] ?? '';
$whatsapp             = $config['whatsapp'] ?? '';
$address              = $config['address'] ?? '';
$google_maps          = $config['google_maps'] ?? '';
$business_hours       = $config['business_hours'] ?? '';
$facebook_url         = $config['facebook_url'] ?? '';
$instagram_url        = $config['instagram_url'] ?? '';
$linkedin_url         = $config['linkedin_url'] ?? '';
$youtube_url          = $config['youtube_url'] ?? '';
$tiktok_url           = $config['tiktok_url'] ?? '';
$meta_title           = $config['meta_title'] ?? '';
$meta_description     = $config['meta_description'] ?? '';
$meta_keywords        = $config['meta_keywords'] ?? '';
$og_title             = $config['og_title'] ?? '';
$og_description       = $config['og_description'] ?? '';
$og_image             = $config['og_image'] ?? '';
$primary_color        = $config['primary_color'] ?? '#000000';
$secondary_color      = $config['secondary_color'] ?? '#000000';
$accent_color         = $config['accent_color'] ?? '#000000';
$copyright_text       = $config['copyright_text'] ?? '';
$google_analytics_id  = $config['google_analytics_id'] ?? '';
$gtm_id               = $config['gtm_id'] ?? '';
$meta_pixel_id        = $config['meta_pixel_id'] ?? '';
$smtp_host            = $config['smtp_host'] ?? '';
$smtp_port            = $config['smtp_port'] ?? '';
$smtp_user            = $config['smtp_user'] ?? '';
$smtp_pass            = $config['smtp_pass'] ?? '';
$smtp_secure          = $config['smtp_secure'] ?? '';
$maintenance_mode     = isset($config['maintenance_mode']) ? (string) $config['maintenance_mode'] : '0';
$preloader_enabled    = isset($config['preloader_enabled']) ? (string) $config['preloader_enabled'] : '1';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $site_name            = trim($_POST['site_name'] ?? '');
    $company_name         = trim($_POST['company_name'] ?? '');
    $tagline              = trim($_POST['tagline'] ?? '');
    $site_description     = trim($_POST['site_description'] ?? '');
    $logo                 = trim($_POST['logo'] ?? '');
    $logo_white           = trim($_POST['logo_white'] ?? '');
    $favicon              = trim($_POST['favicon'] ?? '');
    $email                = trim($_POST['email'] ?? '');
    $phone                = trim($_POST['phone'] ?? '');
    $whatsapp             = trim($_POST['whatsapp'] ?? '');
    $address              = trim($_POST['address'] ?? '');
    $google_maps          = trim($_POST['google_maps'] ?? '');
    $business_hours       = trim($_POST['business_hours'] ?? '');
    $facebook_url         = trim($_POST['facebook_url'] ?? '');
    $instagram_url        = trim($_POST['instagram_url'] ?? '');
    $linkedin_url         = trim($_POST['linkedin_url'] ?? '');
    $youtube_url          = trim($_POST['youtube_url'] ?? '');
    $tiktok_url           = trim($_POST['tiktok_url'] ?? '');
    $meta_title           = trim($_POST['meta_title'] ?? '');
    $meta_description     = trim($_POST['meta_description'] ?? '');
    $meta_keywords        = trim($_POST['meta_keywords'] ?? '');
    $og_title             = trim($_POST['og_title'] ?? '');
    $og_description       = trim($_POST['og_description'] ?? '');
    $og_image             = trim($_POST['og_image'] ?? '');
    $primary_color        = trim($_POST['primary_color'] ?? '#000000');
    $secondary_color      = trim($_POST['secondary_color'] ?? '#000000');
    $accent_color         = trim($_POST['accent_color'] ?? '#000000');
    $copyright_text       = trim($_POST['copyright_text'] ?? '');
    $google_analytics_id  = trim($_POST['google_analytics_id'] ?? '');
    $gtm_id               = trim($_POST['gtm_id'] ?? '');
    $meta_pixel_id        = trim($_POST['meta_pixel_id'] ?? '');
    $smtp_host            = trim($_POST['smtp_host'] ?? '');
    $smtp_port            = trim($_POST['smtp_port'] ?? '');
    $smtp_user            = trim($_POST['smtp_user'] ?? '');
    $smtp_pass            = trim($_POST['smtp_pass'] ?? '');
    $smtp_secure          = trim($_POST['smtp_secure'] ?? '');
    $maintenance_mode     = trim($_POST['maintenance_mode'] ?? '0');
    $preloader_enabled    = trim($_POST['preloader_enabled'] ?? '1');

    if ($site_name === '') {
        $error = 'Site name is required.';
    } elseif (!in_array($maintenance_mode, ['0', '1'], true)) {
        $error = 'Invalid maintenance mode value.';
    } elseif (!in_array($preloader_enabled, ['0', '1'], true)) {
        $error = 'Invalid preloader value.';
    } else {

        if ($config) {
            $stmt = mysqli_prepare($conn, "
                UPDATE web_config SET
                    site_name = ?, company_name = ?, tagline = ?, site_description = ?,
                    logo = ?, logo_white = ?, favicon = ?,
                    email = ?, phone = ?, whatsapp = ?, address = ?, google_maps = ?, business_hours = ?,
                    facebook_url = ?, instagram_url = ?, linkedin_url = ?, youtube_url = ?, tiktok_url = ?,
                    meta_title = ?, meta_description = ?, meta_keywords = ?,
                    og_title = ?, og_description = ?, og_image = ?,
                    primary_color = ?, secondary_color = ?, accent_color = ?,
                    copyright_text = ?,
                    google_analytics_id = ?, gtm_id = ?, meta_pixel_id = ?,
                    smtp_host = ?, smtp_port = ?, smtp_user = ?, smtp_pass = ?, smtp_secure = ?,
                    maintenance_mode = ?, preloader_enabled = ?
                WHERE id = ?
            ");

            if (!$stmt) {
                $error = 'Failed to prepare query: ' . mysqli_error($conn);
            } else {
                $maintenance_mode_int = (int) $maintenance_mode;
                $preloader_enabled_int = (int) $preloader_enabled;
                $config_id = (int) $config['id'];
                $types = str_repeat('s', 36) . 'iii';

                mysqli_stmt_bind_param(
                    $stmt,
                    $types,
                    $site_name,
                    $company_name,
                    $tagline,
                    $site_description,
                    $logo,
                    $logo_white,
                    $favicon,
                    $email,
                    $phone,
                    $whatsapp,
                    $address,
                    $google_maps,
                    $business_hours,
                    $facebook_url,
                    $instagram_url,
                    $linkedin_url,
                    $youtube_url,
                    $tiktok_url,
                    $meta_title,
                    $meta_description,
                    $meta_keywords,
                    $og_title,
                    $og_description,
                    $og_image,
                    $primary_color,
                    $secondary_color,
                    $accent_color,
                    $copyright_text,
                    $google_analytics_id,
                    $gtm_id,
                    $meta_pixel_id,
                    $smtp_host,
                    $smtp_port,
                    $smtp_user,
                    $smtp_pass,
                    $smtp_secure,
                    $maintenance_mode_int,
                    $preloader_enabled_int,
                    $config_id
                );
                            
            }

        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO web_config (
                    site_name, company_name, tagline, site_description,
                    logo, logo_white, favicon,
                    email, phone, whatsapp, address, google_maps, business_hours,
                    facebook_url, instagram_url, linkedin_url, youtube_url, tiktok_url,
                    meta_title, meta_description, meta_keywords,
                    og_title, og_description, og_image,
                    primary_color, secondary_color, accent_color,
                    copyright_text,
                    google_analytics_id, gtm_id, meta_pixel_id,
                    smtp_host, smtp_port, smtp_user, smtp_pass, smtp_secure,
                    maintenance_mode, preloader_enabled
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?
                )
            ");

            if (!$stmt) {
                $error = 'Failed to prepare query: ' . mysqli_error($conn);
            } else {
                $maintenance_mode_int = (int) $maintenance_mode;
                $preloader_enabled_int = (int) $preloader_enabled;

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssssssssssssssssssssssssssssssssii",
                    $site_name,
                    $company_name,
                    $tagline,
                    $site_description,
                    $logo,
                    $logo_white,
                    $favicon,
                    $email,
                    $phone,
                    $whatsapp,
                    $address,
                    $google_maps,
                    $business_hours,
                    $facebook_url,
                    $instagram_url,
                    $linkedin_url,
                    $youtube_url,
                    $tiktok_url,
                    $meta_title,
                    $meta_description,
                    $meta_keywords,
                    $og_title,
                    $og_description,
                    $og_image,
                    $primary_color,
                    $secondary_color,
                    $accent_color,
                    $copyright_text,
                    $google_analytics_id,
                    $gtm_id,
                    $meta_pixel_id,
                    $smtp_host,
                    $smtp_port,
                    $smtp_user,
                    $smtp_pass,
                    $smtp_secure,
                    $maintenance_mode_int,
                    $preloader_enabled_int
                );
            }
        }

        if ($error === '') {
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['success_message'] = 'Configuration updated successfully.';
                header("Location: ./");
                exit;
            } else {
                $error = mysqli_error($conn);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Web Configuration</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Settings</span>
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <span class="text-secondary">Web Config</span>
                </li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-3">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form method="POST">
        <!-- GENERAL -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">General Information</h4>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Site Name</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="site_name" value="<?= htmlspecialchars($site_name) ?>" required>
                            <label>Site Name</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Company Name</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($company_name) ?>">
                            <label>Company Name</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Tagline</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="tagline" value="<?= htmlspecialchars($tagline) ?>">
                            <label>Tagline</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Logo</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="logo" value="<?= htmlspecialchars($logo) ?>">
                            <label>Logo Path / URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">White Logo</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="logo_white" value="<?= htmlspecialchars($logo_white) ?>">
                            <label>White Logo Path / URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Favicon</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="favicon" value="<?= htmlspecialchars($favicon) ?>">
                            <label>Favicon Path / URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Site Description</label>
                        <textarea class="form-control" name="site_description" rows="4"><?= htmlspecialchars($site_description) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTACT -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">Contact Information</h4>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Email</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>">
                            <label>Email</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Phone</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>">
                            <label>Phone</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">WhatsApp</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="whatsapp" value="<?= htmlspecialchars($whatsapp) ?>">
                            <label>WhatsApp</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Business Hours</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="business_hours" value="<?= htmlspecialchars($business_hours) ?>">
                            <label>Business Hours</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Google Maps</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="google_maps" value="<?= htmlspecialchars($google_maps) ?>">
                            <label>Google Maps URL / Embed</label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Address</label>
                        <textarea class="form-control" name="address" rows="4"><?= htmlspecialchars($address) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- SOCIAL -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">Social Media</h4>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Facebook URL</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="facebook_url" value="<?= htmlspecialchars($facebook_url) ?>">
                            <label>Facebook URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Instagram URL</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="instagram_url" value="<?= htmlspecialchars($instagram_url) ?>">
                            <label>Instagram URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">LinkedIn URL</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="linkedin_url" value="<?= htmlspecialchars($linkedin_url) ?>">
                            <label>LinkedIn URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">YouTube URL</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="youtube_url" value="<?= htmlspecialchars($youtube_url) ?>">
                            <label>YouTube URL</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">TikTok URL</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="tiktok_url" value="<?= htmlspecialchars($tiktok_url) ?>">
                            <label>TikTok URL</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">SEO & Open Graph</h4>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Meta Title</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="meta_title" value="<?= htmlspecialchars($meta_title) ?>">
                            <label>Meta Title</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">OG Title</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="og_title" value="<?= htmlspecialchars($og_title) ?>">
                            <label>OG Title</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Meta Description</label>
                        <textarea class="form-control" name="meta_description" rows="4"><?= htmlspecialchars($meta_description) ?></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">OG Description</label>
                        <textarea class="form-control" name="og_description" rows="4"><?= htmlspecialchars($og_description) ?></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Meta Keywords</label>
                        <textarea class="form-control" name="meta_keywords" rows="4"><?= htmlspecialchars($meta_keywords) ?></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">OG Image</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="og_image" value="<?= htmlspecialchars($og_image) ?>">
                            <label>OG Image Path / URL</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BRANDING -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">Branding</h4>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Primary Color</label>
                        <div class="form-floating">
                            <input type="color" class="form-control" name="primary_color" value="<?= htmlspecialchars($primary_color ?: '#000000') ?>">
                            <label>Primary Color</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Secondary Color</label>
                        <div class="form-floating">
                            <input type="color" class="form-control" name="secondary_color" value="<?= htmlspecialchars($secondary_color ?: '#000000') ?>">
                            <label>Secondary Color</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Accent Color</label>
                        <div class="form-floating">
                            <input type="color" class="form-control" name="accent_color" value="<?= htmlspecialchars($accent_color ?: '#000000') ?>">
                            <label>Accent Color</label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Copyright Text</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="copyright_text" value="<?= htmlspecialchars($copyright_text) ?>">
                            <label>Copyright Text</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRACKING -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">Tracking & Analytics</h4>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Google Analytics ID</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="google_analytics_id" value="<?= htmlspecialchars($google_analytics_id) ?>">
                            <label>Google Analytics ID</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">GTM ID</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="gtm_id" value="<?= htmlspecialchars($gtm_id) ?>">
                            <label>GTM ID</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Meta Pixel ID</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="meta_pixel_id" value="<?= htmlspecialchars($meta_pixel_id) ?>">
                            <label>Meta Pixel ID</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMTP -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">SMTP Configuration</h4>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SMTP Host</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="smtp_host" value="<?= htmlspecialchars($smtp_host) ?>">
                            <label>SMTP Host</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SMTP Port</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="smtp_port" value="<?= htmlspecialchars($smtp_port) ?>">
                            <label>Port</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SMTP User</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="smtp_user" value="<?= htmlspecialchars($smtp_user) ?>">
                            <label>SMTP User</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SMTP Secure</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="smtp_secure" value="<?= htmlspecialchars($smtp_secure) ?>">
                            <label>SMTP Secure</label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SMTP Password</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="smtp_pass" value="<?= htmlspecialchars($smtp_pass) ?>">
                            <label>SMTP Password</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SYSTEM -->
        <div class="card bg-white p-20 rounded-10 border border-white mb-4">
            <h4 class="mb-20">System Settings</h4>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Maintenance Mode</label>
                        <div class="form-floating">
                            <select class="form-select" name="maintenance_mode">
                                <option value="0" <?= ($maintenance_mode === '0') ? 'selected' : '' ?>>Off</option>
                                <option value="1" <?= ($maintenance_mode === '1') ? 'selected' : '' ?>>On</option>
                            </select>
                            <label>Maintenance Mode</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Preloader Enabled</label>
                        <div class="form-floating">
                            <select class="form-select" name="preloader_enabled">
                                <option value="1" <?= ($preloader_enabled === '1') ? 'selected' : '' ?>>Enabled</option>
                                <option value="0" <?= ($preloader_enabled === '0') ? 'selected' : '' ?>>Disabled</option>
                            </select>
                            <label>Preloader Enabled</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUTTON -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary text-white">Save Configuration</button>
        </div>
    </form>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>