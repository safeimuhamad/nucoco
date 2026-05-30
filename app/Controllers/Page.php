<?php

namespace App\Controllers;

use App\Models\PageTranslationModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Page extends BaseController
{
    public function detail(string $locale, string $slug = null)
    {
        $locale = in_array($locale, ['id', 'en']) ? $locale : 'id';
        service('language')->setLocale($locale);

        $slug = $slug ?: ($locale === 'id' ? 'beranda' : 'home');

        $translationModel = new PageTranslationModel();
        $page = $translationModel->getByLocaleAndSlug($locale, $slug);

        if (! $page) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('frontend/page', [
            'locale' => $locale,
            'page'   => $page,
        ]);
    }
}