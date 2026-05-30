<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index($locale = 'id')
    {
        if (! in_array($locale, ['id', 'en'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        service('language')->setLocale($locale);

        return view('home', [
            'locale' => $locale,
            'title'  => $locale === 'id' ? 'Beranda' : 'Home',
        ]);
    }
}