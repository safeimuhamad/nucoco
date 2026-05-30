<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PageModel;
use App\Models\PageTranslationModel;

class Pages extends BaseController
{
    public function index()
    {
        $pageModel = new PageModel();

        $pages = $pageModel->orderBy('id', 'DESC')->findAll();

        return view('admin/pages/index', [
            'pages' => $pages,
        ]);
    }

    public function create()
    {
        return view('admin/pages/create');
    }

    public function store()
    {
            // VALIDASI
        if (! $this->validate([
            'page_key' => 'required',
            'title_id' => 'required',
            'title_en' => 'required',
            'slug_id' => 'required',
            'slug_en' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $pageModel = new PageModel();
        $translationModel = new PageTranslationModel();

        $pageId = $pageModel->insert([
            'page_key' => $this->request->getPost('page_key'),
            'status'   => $this->request->getPost('status'),
        ]);

        $translations = [
            'id' => [
                'slug'             => $this->request->getPost('slug_id'),
                'title'            => $this->request->getPost('title_id'),
                'meta_title'       => $this->request->getPost('meta_title_id'),
                'meta_description' => $this->request->getPost('meta_description_id'),
                'content'          => $this->request->getPost('content_id'),
            ],
            'en' => [
                'slug'             => $this->request->getPost('slug_en'),
                'title'            => $this->request->getPost('title_en'),
                'meta_title'       => $this->request->getPost('meta_title_en'),
                'meta_description' => $this->request->getPost('meta_description_en'),
                'content'          => $this->request->getPost('content_en'),
            ],
        ];

        foreach ($translations as $locale => $data) {
            $translationModel->insert([
                'page_id'          => $pageId,
                'locale'           => $locale,
                'slug'             => $data['slug'],
                'title'            => $data['title'],
                'meta_title'       => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'content'          => $data['content'],
            ]);
        }

        return redirect()->to('/admin/pages')->with('success', 'Page created successfully.');
    }

    public function edit(int $id)
    {
        $pageModel = new PageModel();
        $translationModel = new PageTranslationModel();

        $page = $pageModel->find($id);
        $translations = $translationModel->getByPageId($id);

        $translationMap = [];
        foreach ($translations as $row) {
            $translationMap[$row['locale']] = $row;
        }

        return view('admin/pages/edit', [
            'page'         => $page,
            'translations' => $translationMap,
        ]);
    }

    public function update(int $id)
    {
        $pageModel = new PageModel();
        $translationModel = new PageTranslationModel();

        $pageModel->update($id, [
            'page_key' => $this->request->getPost('page_key'),
            'status'   => $this->request->getPost('status'),
        ]);

        foreach (['id', 'en'] as $locale) {
            $existing = $translationModel
                ->where('page_id', $id)
                ->where('locale', $locale)
                ->first();

            $data = [
                'slug'             => $this->request->getPost("slug_{$locale}"),
                'title'            => $this->request->getPost("title_{$locale}"),
                'meta_title'       => $this->request->getPost("meta_title_{$locale}"),
                'meta_description' => $this->request->getPost("meta_description_{$locale}"),
                'content'          => $this->request->getPost("content_{$locale}"),
            ];

            if ($existing) {
                $translationModel->update($existing['id'], $data);
            } else {
                $translationModel->insert(array_merge($data, [
                    'page_id' => $id,
                    'locale'  => $locale,
                ]));
            }
        }

        return redirect()->to('/admin/pages')->with('success', 'Page updated successfully.');
    }

    public function delete(int $id)
    {
        $pageModel = new PageModel();
        $pageModel->delete($id);

        return redirect()->to('/admin/pages')->with('success', 'Page deleted successfully.');
    }
}