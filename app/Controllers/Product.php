<?php

namespace App\Controllers;

class Product extends BaseController
{
    public function index($locale = 'id')
    {
        if (! in_array($locale, ['id', 'en'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        service('language')->setLocale($locale);

        return view('product', [
            'locale' => $locale,
            'title'  => $locale === 'id' ? 'Tentang Kami' : 'Product Us',
        ]);
    }
}