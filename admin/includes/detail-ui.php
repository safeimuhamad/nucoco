<?php

if (!function_exists('detail_badge_class')) {
    function detail_badge_class(string $status): string
    {
        $status = strtolower($status);
        if (in_array($status, ['active', 'published', 'deal', 'converted', 'allowed', 'read', 'paid', 'sent', 'accepted'], true)) {
            return 'detail-badge detail-badge-success';
        }
        if (in_array($status, ['pending', 'draft', 'new', 'overdue'], true)) {
            return 'detail-badge detail-badge-warning';
        }
        if (in_array($status, ['lost', 'inactive', 'denied', 'deleted', 'cancelled', 'rejected'], true)) {
            return 'detail-badge detail-badge-danger';
        }
        return 'detail-badge detail-badge-muted';
    }
}

if (!function_exists('detail_page_header')) {
    function detail_page_header(string $parent, string $parentUrl, string $title, string $badge = '', array $actions = []): void
    {
        ?>
        <div class="detail-page-head">
            <div>
                <nav class="detail-breadcrumb">
                    <a href="<?= htmlspecialchars($parentUrl) ?>"><?= htmlspecialchars($parent) ?></a>
                    <span class="material-symbols-outlined">chevron_right</span>
                    <span>Detail</span>
                </nav>
                <div class="detail-title-row">
                    <h3><?= htmlspecialchars($title) ?></h3>
                    <?php if ($badge !== ''): ?>
                        <span class="detail-title-badge"><?= htmlspecialchars($badge) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($actions): ?>
                <div class="detail-actions">
                    <?php foreach ($actions as $action): ?>
                        <?php
                        $attributes = '';
                        foreach (($action['attrs'] ?? []) as $attr_name => $attr_value) {
                            $attributes .= ' ' . htmlspecialchars((string) $attr_name) . '="' . htmlspecialchars((string) $attr_value) . '"';
                        }
                        ?>
                        <a
                            href="<?= htmlspecialchars($action['url']) ?>"
                            class="<?= htmlspecialchars($action['class'] ?? 'btn detail-btn detail-btn-outline') ?>"
                            <?= !empty($action['target']) ? 'target="' . htmlspecialchars($action['target']) . '"' : '' ?>
                            <?= !empty($action['confirm']) ? 'onclick="return confirm(\'' . htmlspecialchars($action['confirm'], ENT_QUOTES) . '\')"' : '' ?>
                            <?= $attributes ?>
                        >
                            <?php if (!empty($action['icon'])): ?>
                                <span class="material-symbols-outlined"><?= htmlspecialchars($action['icon']) ?></span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($action['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('detail_summary')) {
    function detail_summary(array $items): void
    {
        ?>
        <div class="detail-summary-card">
            <div class="row g-0">
                <?php foreach ($items as $item): ?>
                    <div class="col-md">
                        <div class="detail-summary-item">
                            <span class="material-symbols-outlined detail-summary-icon"><?= htmlspecialchars($item['icon'] ?? 'info') ?></span>
                            <div>
                                <span class="detail-summary-label"><?= htmlspecialchars($item['label']) ?></span>
                                <strong><?= htmlspecialchars($item['value']) ?></strong>
                                <?php if (!empty($item['meta'])): ?>
                                    <small><?= htmlspecialchars($item['meta']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($item['url'])): ?>
                                    <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['link_label'] ?? 'View Detail') ?> <span class="material-symbols-outlined">arrow_forward</span></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('detail_card_open')) {
    function detail_card_open(string $title, string $icon = 'description', string $class = ''): void
    {
        ?>
        <div class="detail-card <?= htmlspecialchars($class) ?>">
            <div class="detail-card-title">
                <span class="material-symbols-outlined"><?= htmlspecialchars($icon) ?></span>
                <h4><?= htmlspecialchars($title) ?></h4>
            </div>
        <?php
    }
}

if (!function_exists('detail_card_close')) {
    function detail_card_close(): void
    {
        echo '</div>';
    }
}

if (!function_exists('detail_fields')) {
    function detail_fields(array $fields): void
    {
        echo '<div class="detail-fields">';
        foreach ($fields as $label => $value) {
            echo '<div class="detail-field"><span>' . htmlspecialchars((string) $label) . '</span><strong>' . $value . '</strong></div>';
        }
        echo '</div>';
    }
}

if (!function_exists('detail_text')) {
    function detail_text($value): string
    {
        $value = (string) ($value ?? '');
        return htmlspecialchars($value !== '' ? $value : '-');
    }
}
