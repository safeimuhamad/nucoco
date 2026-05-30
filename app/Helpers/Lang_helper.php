<?php

if (! function_exists('switch_lang_url')) {
    function switch_lang_url($currentPath)
    {
        return match ($currentPath) {
            'id' => '/en',
            'en' => '/id',
            'id/tentang-kami' => '/en/about-us',
            'en/about-us' => '/id/tentang-kami',
            'id/kontak' => '/en/contact',
            'en/contact' => '/id/kontak',
            default => '/id',
        };
    }
}