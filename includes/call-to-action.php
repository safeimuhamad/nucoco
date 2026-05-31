<div class="ltn__call-to-action-area ltn__call-to-action-4 bg-image pt-115 pb-120" data-bg="<?= $base_url ?>img/bg/coconut-contact-background.webp">
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="call-to-action-inner call-to-action-inner-4 text-center">

                    <div class="section-title-area ltn__section-title-2">

                        <!-- SUBTITLE -->
                        <h2 class="section-subtitle ltn__secondary-color">
                            <?= current_lang() === 'en' 
                                ? '// Any questions? Contact us directly' 
                                : '// Ada pertanyaan? Hubungi kami langsung' ?>
                        </h2>

                        <!-- PHONE -->
                        <h3 class="section-title white-color" dir="ltr">
                            +62 218 9090 882
                        </h3>

                    </div>

                    <!-- BUTTON -->
                    <div class="btn-wrapper">

                        <a href="tel:+622189090882" class="theme-btn-1 btn btn-effect-1">
                            <?= current_lang() === 'en' ? 'Make a Call' : 'Hubungi Sekarang' ?>
                        </a>

                        <a href="<?= $base_url . lang_url('en/contact', 'kontak') ?>" class="btn btn-transparent btn-effect-4 white-color">
                            <?= current_lang() === 'en' ? 'Contact Us' : 'Hubungi Kami' ?>
                        </a>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- IMAGE DECOR -->
    <div class="ltn__call-to-4-img-1">
        <img src="<?= $base_url ?>img/bg/coconut-product.webp" alt="#">
    </div>

    <div class="ltn__call-to-4-img-2">
        <img src="<?= $base_url ?>img/bg/coconut-sales.webp" alt="#">
    </div>

</div>