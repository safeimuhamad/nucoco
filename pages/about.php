   <?php 
   $page_slug = 'about'; 
   $lang = current_lang() === 'en' ? 'English' : 'Indonesia';
      $choose_us_query = mysqli_prepare(
    $conn,
    "SELECT title, description, icon
    FROM why_choose_us
    WHERE is_active = 1 AND language = ?
    ORDER BY sort_order ASC, id DESC
    LIMIT 4"
    );

       $choose_us_items = [];

       if ($choose_us_query) {
        mysqli_stmt_bind_param($choose_us_query, "s", $lang);
        mysqli_stmt_execute($choose_us_query);
        $choose_us_result = mysqli_stmt_get_result($choose_us_query);

        while ($row = mysqli_fetch_assoc($choose_us_result)) {
            $choose_us_items[] = $row;
        }

        mysqli_stmt_close($choose_us_query);
    }
?>
<div class="ltn__utilize-overlay"></div>
<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area ltn__breadcrumb-area-2 ltn__breadcrumb-color-white bg-overlay-theme-black-90 bg-image" data-bg="<?= $base_url ?>img/bg/background-header.webp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner ltn__breadcrumb-inner-2 justify-content-between">
                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">
                            // <?= current_lang() === 'en' ? 'Welcome to our company' : 'Selamat datang di perusahaan kami' ?>
                        </h6>
                        <h1 class="section-title white-color">
                            <?= page_label($page_slug) ?>
                        </h1>
                    </div>
                    <div class="ltn__breadcrumb-list">
                        <ul>
                            <li>
                                <a href="<?= url() ?>">
                                    <?= page_label('home') ?>
                                </a>
                            </li>
                            <li><?= page_label($page_slug) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BREADCRUMB AREA END -->
<!-- ABOUT US AREA START -->
<div class="ltn__about-us-area pt-120--- pb-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 align-self-center">
                <div class="about-us-img-wrap about-img-left">
                    <img src="<?= $base_url ?>img/team/director.webp" alt="About Us Image">
                </div>
            </div>
            <div class="col-lg-6 align-self-center">
                <div class="about-us-info-wrap">
                    <div class="section-title-area ltn__section-title-2">

                        <h6 class="section-subtitle ltn__secondary-color">
                            <?= current_lang() === 'en' ? 'KNOW MORE ABOUT NUCOCO' : 'KENALI NUCOCO LEBIH DEKAT' ?>
                        </h6>

                        <h1 class="section-title">
                            <?= current_lang() === 'en' 
                            ? 'Trusted Coconut <br class="d-none d-md-block"> Supply Partner' 
                            : 'Mitra Terpercaya <br class="d-none d-md-block"> Pasokan Kelapa' ?>
                        </h1>

                        <p>
                            <?= current_lang() === 'en'
                            ? 'Nucoco is a reliable supplier of high-quality coconut-based products from Indonesia, serving global markets with consistent quality and scalable supply. We connect natural resources with industrial needs through an integrated approach from sourcing to export.'
                            : 'Nucoco adalah pemasok terpercaya produk berbasis kelapa berkualitas tinggi dari Indonesia, melayani pasar global dengan kualitas yang konsisten dan kapasitas pasokan yang dapat ditingkatkan. Kami menghubungkan sumber daya alam dengan kebutuhan industri melalui pendekatan terintegrasi dari pengadaan hingga ekspor.' ?>
                        </p>

                    </div>

                    <p>
                        <?= current_lang() === 'en'
                        ? 'We work closely with local farmers and producers to ensure sustainable sourcing, while maintaining strict quality control throughout processing and distribution. Our commitment is to deliver products that meet international standards and support long-term business partnerships.'
                        : 'Kami bekerja sama secara langsung dengan petani dan produsen lokal untuk memastikan sumber bahan baku yang berkelanjutan, serta menjaga kontrol kualitas yang ketat di setiap tahap proses dan distribusi. Komitmen kami adalah menghadirkan produk yang memenuhi standar internasional dan mendukung kemitraan bisnis jangka panjang.' ?>
                    </p>

                    <div class="about-author-info d-flex">
                        <div class="author-name-designation align-self-center">
                            <h4 class="mb-0">Sularto Abimanyu</h4>
                            <small>
                                <?= current_lang() === 'en' ? '/ Director' : '/ Direktur' ?>
                            </small>
                        </div>

                        <div class="author-sign">
                            <!-- <img src="img/icons/icon-img/author-sign.png" alt="#"> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ABOUT US AREA END -->

<!-- FEATURE AREA START ( Feature - 6) -->
<div class="ltn__feature-area section-bg-1 pt-115 pb-90">
    <div class="container-fluid px-5">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h6 class="section-subtitle ltn__secondary-color">
                        // <?= current_lang() === 'en' ? 'Features' : 'Fitur' ?> //
                    </h6>
                    <h1 class="section-title">
                        <?= current_lang() === 'en' ? 'Why Choose Us' : 'Kenapa Memilih Kami' ?><span>.</span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php foreach ($choose_us_items as $item): ?>
                <div class="col-lg-3 col-sm-6 col-12" style="display:flex; margin-bottom:30px;">

                    <div class="ltn__feature-item ltn__feature-item-7" style="width:100%;">

                        <div class="ltn__feature-icon-title">
                            <div class="ltn__feature-icon">
                                <span>
                                    <?php if (!empty($item['icon'])): ?>
                                        <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($item['icon'] ?? '') ?>"
                                        alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
                                    <?php endif; ?>
                                </span>
                            </div>

                            <h3>
                                <?= htmlspecialchars($item['title'] ?? '') ?>
                            </h3>
                        </div>

                        <div class="ltn__feature-info">
                            <p style="min-height:120px;">
                                <?= htmlspecialchars($item['description'] ?? '') ?>
                            </p>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- FEATURE AREA END -->
<?php
include __DIR__ . '/../section_team.php';
?>
<!-- CALL TO ACTION START (call-to-action-5) -->
<div class="call-to-action-area call-to-action-5 bg-image bg-overlay-theme-90 pt-40 pb-25 d-none" data-bg="img/bg/13.jpg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="call-to-action-inner call-to-action-inner-5 text-center">
                    <h2 class="white-color text-decoration">24/7 Availability, Make <a href="#">An Appointment</a></h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- CALL TO ACTION END -->

<!-- PROGRESS BAR AREA START -->
<div class="ltn__progress-bar-area before-bg-right pt-115 pb-95 d-none">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="ltn__progress-bar-wrap">
                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">// skills</h6>
                        <h1 class="section-title">We Have A Skillest
                            Team Ever<span>.</span></h1>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore</p>
                        </div>
                        <div class="ltn__progress-bar-inner">
                            <div class="ltn__progress-bar-item">
                                <p>Car Repair</p>
                                <div class="progress">
                                    <div class="progress-bar wow fadeInLeft" data-wow-duration="0.5s" data-wow-delay=".5s" role="progressbar" style="width: 72%">
                                        <span>72%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ltn__progress-bar-item">
                                <p>Car Rental Service</p>
                                <div class="progress">
                                    <div class="progress-bar wow fadeInLeft" data-wow-duration="0.5s" data-wow-delay=".5s" role="progressbar" style="width: 74%">
                                        <span>74%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ltn__progress-bar-item">
                                <p>Car Cleaning & Parts</p>
                                <div class="progress">
                                    <div class="progress-bar wow fadeInLeft" data-wow-duration="0.5s" data-wow-delay=".5s" role="progressbar" style="width: 81%">
                                        <span>81%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="ltn__video-bg-img ltn__video-popup-height-500 bg-overlay-black-50-- bg-image ml-30" data-bg="img/others/5.jpg">
                        <a class="ltn__video-icon-2 ltn__video-icon-2-border---" href="https://www.youtube.com/embed/eWUxqVFBq74?autoplay=1&showinfo=0" data-rel="lightcase:myCollection">
                            <i class="fa fa-play"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- PROGRESS BAR AREA END -->
    <?php
    include __DIR__ . '/../section_testimonials.php';
    include __DIR__ . '/../section_faq.php';
    ?>
    <!-- NEWSLETTER AREA START -->
    <div class="ltn__newsletter-area section-bg-1 bg-overlay-black-90 pt-110 pb-90 bg-image" data-bg="img/bg/2.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="ltn__newsletter-inner text-center">

                        <h1 class="white-color">
                            <?= current_lang() === 'en' 
                            ? 'Stay Updated with Our Latest Insights' 
                            : 'Dapatkan Update Informasi Terbaru' ?>
                        </h1>

                        <p class="white-color" dir="ltr">
                            <?= current_lang() === 'en' 
                            ? 'Subscribe to our newsletter for the latest updates on coconut products, industry insights, and exclusive offers for your business.' 
                            : 'Berlangganan newsletter kami untuk mendapatkan update terbaru tentang produk kelapa, insight industri, dan penawaran eksklusif untuk bisnis Anda.' ?>
                        </p>

                        <form action="#" class="ltn__form-box">
                            <input 
                            type="email" 
                            name="email" 
                            placeholder="<?= current_lang() === 'en' ? 'Your Email Address*' : 'Alamat Email Anda*' ?>"
                            >

                            <div class="btn-wrapper">
                                <button class="theme-btn-1 btn btn-effect-1 text-uppercase" type="submit">
                                    <?= current_lang() === 'en' ? 'Subscribe' : 'Berlangganan' ?>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- NEWSLETTER AREA END -->

    <?php
    include __DIR__ . '/../section_new_article.php';
    include __DIR__ . '/../section_choose.php';
    ?>