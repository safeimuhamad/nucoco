<?php

if (!function_exists('admin_pagination_meta')) {
    function admin_pagination_meta(int $total, int $pageNum, int $limit): array
    {
        $limit = max(1, $limit);
        $totalPages = max(1, (int) ceil($total / $limit));
        $pageNum = max(1, min($pageNum, $totalPages));
        $offset = ($pageNum - 1) * $limit;

        return [
            'page' => $pageNum,
            'limit' => $limit,
            'offset' => $offset,
            'total_pages' => $totalPages,
            'start_data' => $total > 0 ? $offset + 1 : 0,
            'end_data' => min($offset + $limit, $total),
        ];
    }
}

if (!function_exists('admin_pagination_url')) {
    function admin_pagination_url(int $pageNum, array $params = []): string
    {
        $query = array_merge($_GET, $params, ['page' => $pageNum]);
        foreach ($query as $key => $value) {
            if ($value === '' || $value === null) {
                unset($query[$key]);
            }
        }

        return '?' . http_build_query($query);
    }
}

if (!function_exists('render_admin_pagination')) {
    function render_admin_pagination(int $pageNum, int $totalPages, int $totalData, int $startData, int $endData, array $params = []): void
    {
        if ($totalData <= 0) {
            return;
        }
        ?>
        <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap pt-15 p-20">
            <span class="fs-15">
                Showing <?= (int) $startData ?> to <?= (int) $endData ?> of <?= (int) $totalData ?> entries
            </span>

            <nav class="custom-pagination" aria-label="Table pagination">
                <ul class="pagination mb-0 justify-content-center">
                    <li class="page-item <?= ($pageNum <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link icon" href="<?= htmlspecialchars(admin_pagination_url($pageNum - 1, $params)) ?>">
                            <i class="material-symbols-outlined">west</i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item">
                            <a class="page-link <?= ($pageNum === $i) ? 'active' : '' ?>" href="<?= htmlspecialchars(admin_pagination_url($i, $params)) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($pageNum >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link icon" href="<?= htmlspecialchars(admin_pagination_url($pageNum + 1, $params)) ?>">
                            <i class="material-symbols-outlined">east</i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php
    }
}
