<?php

namespace App\Controllers;

class Contact extends BaseController
{
    public function index($locale = 'id')
    {
        if (! in_array($locale, ['id', 'en'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        service('language')->setLocale($locale);

        return view('contact', [
            'locale' => $locale,
            'title'  => $locale === 'id' ? 'Kontak' : 'Contact',
        ]);
    }
}