<?php $page_slug = 'contact'; ?>

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

<!-- CONTACT ADDRESS AREA -->
<div class="ltn__contact-address-area mb-90">
    <div class="container">
        <div class="row">

            <div class="col-lg-4">
                <div class="ltn__contact-address-item ltn__contact-address-item-3 box-shadow">
                    <div class="ltn__contact-address-icon">
                        <img src="<?= $base_url ?>img/icons/10.png" alt="">
                    </div>
                    <h3><?= current_lang() === 'en' ? 'Email Address' : 'Alamat Email' ?></h3>
                    <p>info@nucoco.id</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ltn__contact-address-item ltn__contact-address-item-3 box-shadow">
                    <div class="ltn__contact-address-icon">
                        <img src="<?= $base_url ?>img/icons/11.png" alt="">
                    </div>
                    <h3><?= current_lang() === 'en' ? 'Phone Number' : 'Nomor Telepon' ?></h3>
                    <p dir="ltr"><a href="tel:+622189090882">+62 218 9090 882</a></p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ltn__contact-address-item ltn__contact-address-item-3 box-shadow">
                    <div class="ltn__contact-address-icon">
                        <img src="<?= $base_url ?>img/icons/12.png" alt="">
                    </div>
                    <h3><?= current_lang() === 'en' ? 'Office Address' : 'Alamat Kantor' ?></h3>
                    <p>
                        Bellezza BSA 1st Floor<br>
                        <?= current_lang() === 'en' ? 'South Jakarta' : 'Jakarta Selatan' ?>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- CONTACT FORM -->
<div class="ltn__contact-message-area mb-120 mb--100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__form-box contact-form-box box-shadow white-bg">

                    <h4 class="title-2">
                        <?= current_lang() === 'en' ? 'Get A Quote' : 'Minta Penawaran' ?>
                    </h4>

                    <form id="contact-form" action="handlers/mail" method="post">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="input-item ltn__custom-icon">
                                    <input type="text" name="name"
                                    placeholder="<?= current_lang() === 'en' ? 'Enter your name' : 'Masukkan nama Anda' ?>"
                                    required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-item ltn__custom-icon">
                                    <input type="email" name="email"
                                    placeholder="<?= current_lang() === 'en' ? 'Enter email address' : 'Masukkan email Anda' ?>"
                                    required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-item ltn__custom-icon">
                                    <select name="service_type" class="service-type-select" required>
                                        <option value="">
                                            <?= current_lang() === 'en' ? 'Select Service Type' : 'Pilih Jenis Layanan' ?>
                                        </option>

                                        <option value="OEM & Private Label">OEM & Private Label</option>

                                        <option value="Global Export & Supply">
                                            <?= current_lang() === 'en' ? 'Global Export & Supply' : 'Ekspor Global & Supply' ?>
                                        </option>

                                        <option value="Industrial Bulk Supply">
                                            <?= current_lang() === 'en' ? 'Industrial Bulk Supply' : 'Supply Industri Skala Besar' ?>
                                        </option>

                                        <option value="Product Customization">
                                            <?= current_lang() === 'en' ? 'Product Customization' : 'Kustomisasi Produk' ?>
                                        </option>

                                        <option value="Sourcing & Consolidation">
                                            <?= current_lang() === 'en' ? 'Sourcing & Consolidation' : 'Pengadaan & Konsolidasi' ?>
                                        </option>

                                        <option value="Quality & Certification Support">
                                            <?= current_lang() === 'en' ? 'Quality & Certification Support' : 'Dukungan Kualitas & Sertifikasi' ?>
                                        </option>

                                        <option value="General Inquiry">
                                            <?= current_lang() === 'en' ? 'General Inquiry' : 'Pertanyaan Umum' ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const originalSelect = document.querySelector('.service-type-select');
                                    if (!originalSelect) return;

                                    function fixNiceSelect() {
                                        const niceSelect = originalSelect.nextElementSibling;
                                        if (!niceSelect || !niceSelect.classList.contains('nice-select')) return;

                                        niceSelect.style.width = '100%';
                                        niceSelect.style.textAlign = 'right';
                                        niceSelect.style.paddingRight = '55px';
                                        niceSelect.style.paddingLeft = '20px';

                                        const current = niceSelect.querySelector('.current');
                                        if (current) {
                                            current.style.display = 'block';
                                            current.style.width = '100%';
                                            current.style.textAlign = 'right';
                                        }

                                        const list = niceSelect.querySelector('.list');
                                        if (list) {
                                            list.style.left = '0';
                                            list.style.right = 'auto';
                                            list.style.width = '100%';
                                            list.style.textAlign = 'right';
                                        }

                                        const items = niceSelect.querySelectorAll('.list li');
                                        items.forEach(function (item) {
                                            item.style.textAlign = 'right';
                                            item.style.direction = 'rtl';
                                            item.style.paddingRight = '35px';
                                            item.style.paddingLeft = '15px';
                                        });
                                    }

                                    fixNiceSelect();
                                    setTimeout(fixNiceSelect, 200);
                                    setTimeout(fixNiceSelect, 600);
                                    setTimeout(fixNiceSelect, 1200);
                                });
                            </script>

                            <div class="col-md-6">
                                <div class="input-item ltn__custom-icon">
                                    <input type="text" name="phone"
                                    placeholder="<?= current_lang() === 'en' ? 'Enter phone number' : 'Masukkan nomor telepon' ?>">
                                </div>
                            </div>

                        </div>

                        <div class="input-item ltn__custom-icon">
                            <textarea name="message"
                            placeholder="<?= current_lang() === 'en' ? 'Tell us your inquiry details' : 'Jelaskan kebutuhan Anda' ?>"
                            required></textarea>
                        </div>

                        <div class="btn-wrapper mt-0">
                            <button class="btn theme-btn-1 btn-effect-1 text-uppercase" type="submit">
                                <?= current_lang() === 'en' ? 'Request a Quote' : 'Kirim Permintaan' ?>
                            </button>
                        </div>

                        <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
                            <p class="form-messege mb-0 mt-20 text-success">
                                <?= current_lang() === 'en'
                                ? 'Thank you. Your inquiry has been submitted successfully.'
                                : 'Terima kasih. Permintaan Anda berhasil dikirim.' ?>
                            </p>
                        <?php elseif (isset($_GET['error'])): ?>
                            <p class="form-messege mb-0 mt-20 text-danger">
                                <?= htmlspecialchars($_GET['error']) ?>
                            </p>
                        <?php endif; ?>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- GOOGLE MAP AREA START -->
<div class="google-map mb-120">

    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.321736822162!2d106.781706!3d-6.2212377!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f13278418439%3A0xe8663e21996bd059!2sBellezza%20Permata%20Hijau!5e0!3m2!1sen!2sid!4v1776154252730!5m2!1sen!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

</div>
    <!-- GOOGLE MAP AREA END -->
    <?php
    include __DIR__ . '/../section_choose.php';
    ?>