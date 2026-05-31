<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

    $base_url = site_base_url();
    $admin_base_url = admin_base_url();
    $admin_name = $_SESSION['name'] ?? 'John Doe';
    $admin_role = $_SESSION['role'] ?? 'Administrator';
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/sidebar-menu.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/simplebar.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/prism.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/quill.snow.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/remixicon.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/jsvectormap.min.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $admin_base_url ?>assets/css/nucoco-admin.css?v=20260531-5">

    <link rel="icon" type="image/png" href="<?= $base_url ?>img/favicon.png">
    <title>Admin - Nucoco</title>
</head>
<body class="bg-body-bg nucoco-admin">
    <div class="preloader d-none" id="preloader" aria-hidden="true">
        <div class="preloader">
            <div class="waviy position-relative">
                <span class="d-inline-block">N</span>
                <span class="d-inline-block">U</span>
                <span class="d-inline-block">C</span>
                <span class="d-inline-block">O</span>
                <span class="d-inline-block">C</span>
                <span class="d-inline-block">O</span>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="main-content d-flex flex-column">
            <header class="header-area admin-topbar" id="header-area">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <button class="header-burger-menu bg-transparent p-0 border-0" id="header-burger-menu" aria-label="Toggle menu">
                        <span class="material-symbols-outlined">menu</span>
                    </button>

                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-secondary border-0 p-0 position-relative admin-notification" type="button" aria-label="Notifications">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="count">3</span>
                        </button>

                        <div class="dropdown admin-profile">
                            <button class="admin-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img class="rounded-circle" src="<?= $admin_base_url ?>assets/images/admin.png" alt="admin">
                            </button>
                            <div class="dropdown-menu border-0 bg-white dropdown-menu-end">
                                <div class="d-flex align-items-center info">
                                    <img class="rounded-circle" style="width: 40px; height: 40px;" src="<?= $admin_base_url ?>assets/images/admin.png" alt="admin">
                                    <div class="flex-grow-1 ms-10">
                                        <h3 class="fw-medium fs-17 mb-0"><?= htmlspecialchars($admin_name) ?></h3>
                                        <span class="fs-15 fw-medium"><?= htmlspecialchars(ucfirst($admin_role)) ?></span>
                                    </div>
                                </div>
                                <ul class="admin-link mb-0 list-unstyled">
                                    <li>
                                        <a class="dropdown-item admin-item-link d-flex align-items-center text-body" href="<?= admin_url('users/detail.php?id=' . (int) ($_SESSION['user_id'] ?? 0)) ?>">
                                            <i class="material-symbols-outlined">person</i>
                                            <span class="ms-2">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item admin-item-link d-flex align-items-center text-body" href="<?= admin_url('logout') ?>">
                                            <i class="material-symbols-outlined">logout</i>
                                            <span class="ms-2">Logout</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
