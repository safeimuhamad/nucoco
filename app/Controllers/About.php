<?php

namespace App\Controllers;

class About extends BaseController
{
    public function index($locale = 'id')
    {
        if (! in_array($locale, ['id', 'en'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        service('language')->setLocale($locale);

        return view('about', [
            'locale' => $locale,
            'title'  => $locale === 'id' ? 'Tentang Kami' : 'About Us',
        ]);
    }
}