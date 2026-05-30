<?php

namespace App\Models;

use CodeIgniter\Model;

class PageTranslationModel extends Model
{
    protected $table            = 'page_translations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'page_id',
        'locale',
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'content',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByLocaleAndSlug(string $locale, string $slug): ?array
    {
        return $this->select('page_translations.*, pages.status, pages.page_key')
            ->join('pages', 'pages.id = page_translations.page_id')
            ->where('page_translations.locale', $locale)
            ->where('page_translations.slug', $slug)
            ->where('pages.status', 'publish')
            ->first();
    }

    public function getByPageId(int $pageId): array
    {
        return $this->where('page_id', $pageId)->findAll();
    }
}