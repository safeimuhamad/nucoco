<?php
$page = 'dashboard';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../quotations/helpers.php';

$usd_rate = 16000;
$status_meta = [
    'draft' => ['label' => 'Draft', 'color' => '#d8dde5', 'class' => 'dash-status-muted'],
    'sent' => ['label' => 'Sent', 'color' => '#2f8fed', 'class' => 'dash-status-blue'],
    'accepted' => ['label' => 'Approved', 'color' => '#36a915', 'class' => 'dash-status-green'],
    'rejected' => ['label' => 'Rejected', 'color' => '#ef4444', 'class' => 'dash-status-red'],
    'cancelled' => ['label' => 'Expired', 'color' => '#f6bd26', 'class' => 'dash-status-yellow'],
];

function dashboard_amount_idr(array $quote, int $usd_rate): float
{
    $amount = (float) ($quote['grand_total'] ?? 0);
    return ($quote['currency'] ?? 'IDR') === 'USD' ? $amount * $usd_rate : $amount;
}

function dashboard_money_idr(float $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function dashboard_short_money(float $amount): string
{
    if ($amount >= 1000000000) {
        return 'Rp ' . rtrim(rtrim(number_format($amount / 1000000000, 1, ',', '.'), '0'), ',') . ' M';
    }
    if ($amount >= 1000000) {
        return 'Rp ' . rtrim(rtrim(number_format($amount / 1000000, 1, ',', '.'), '0'), ',') . ' jt';
    }
    return dashboard_money_idr($amount);
}

function dashboard_percent(float $part, float $total): string
{
    return $total > 0 ? number_format(($part / $total) * 100, 1) . '%' : '0%';
}

$quotes = db_select_all(
    "SELECT q.*, COALESCE(u.name, 'Unassigned') AS sales_name
     FROM quotations q
     LEFT JOIN users u ON u.id = q.created_by
     ORDER BY q.created_at ASC, q.id ASC"
);

$total_quotes = count($quotes);
$total_value = 0;
$approved_count = 0;
$expired_count = 0;
$status_counts = array_fill_keys(array_keys($status_meta), 0);
$sales_totals = [];

foreach ($quotes as $quote) {
    $amount_idr = dashboard_amount_idr($quote, $usd_rate);
    $total_value += $amount_idr;
    $status = $quote['status'] ?? 'draft';
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
    if ($status === 'accepted') {
        $approved_count++;
    }
    if ($status === 'cancelled' || (!empty($quote['valid_until']) && strtotime($quote['valid_until']) < strtotime(date('Y-m-d')) && !in_array($status, ['accepted', 'rejected'], true))) {
        $expired_count++;
    }
    $sales_totals[$quote['sales_name']] = ($sales_totals[$quote['sales_name']] ?? 0) + $amount_idr;
}
arsort($sales_totals);

$conversion_rate = dashboard_percent($approved_count, max(1, $total_quotes));
$date_min = $quotes ? strtotime($quotes[0]['created_at']) : time();
$date_max = $quotes ? strtotime(end($quotes)['created_at']) : time();
$date_range = date('d M Y', $date_min) . ' - ' . date('d M Y', $date_max);

$end_month = strtotime(date('Y-m-01', $date_max));
$trend = [];
for ($i = 5; $i >= 0; $i--) {
    $month_ts = strtotime("-{$i} months", $end_month);
    $key = date('Y-m', $month_ts);
    $trend[$key] = ['label' => date('M', $month_ts), 'value' => 0];
}
foreach ($quotes as $quote) {
    $key = date('Y-m', strtotime($quote['created_at']));
    if (isset($trend[$key])) {
        $trend[$key]['value'] += dashboard_amount_idr($quote, $usd_rate);
    }
}

$trend_values = array_column($trend, 'value');
$chart_max = max($trend_values ?: [0]);
$chart_max = $chart_max > 0 ? $chart_max : 1;
$points = [];
$chart_w = 760;
$chart_h = 250;
$left_pad = 54;
$right_pad = 20;
$top_pad = 18;
$bottom_pad = 34;
$plot_w = $chart_w - $left_pad - $right_pad;
$plot_h = $chart_h - $top_pad - $bottom_pad;
$trend_count = max(1, count($trend) - 1);
$index = 0;
foreach ($trend as $row) {
    $x = $left_pad + ($plot_w / $trend_count) * $index;
    $y = $top_pad + $plot_h - (($row['value'] / $chart_max) * $plot_h);
    $points[] = round($x, 1) . ',' . round($y, 1);
    $index++;
}
$area_points = $left_pad . ',' . ($top_pad + $plot_h) . ' ' . implode(' ', $points) . ' ' . ($chart_w - $right_pad) . ',' . ($top_pad + $plot_h);

$donut_segments = [];
$start = 0;
foreach ($status_counts as $status => $count) {
    $degrees = $total_quotes > 0 ? ($count / $total_quotes) * 360 : 0;
    $end = $start + $degrees;
    $donut_segments[] = $status_meta[$status]['color'] . ' ' . round($start, 2) . 'deg ' . round($end, 2) . 'deg';
    $start = $end;
}
$donut_gradient = implode(', ', $donut_segments);

$latest_quotes = array_slice(array_reverse($quotes), 0, 5);
$top_sales = array_slice($sales_totals, 0, 5, true);
$max_sales = max($top_sales ?: [1]);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden dashboard-sales">
    <div class="dash-hero">
        <div>
            <h3>Dashboard</h3>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>.</p>
        </div>
        <div class="dash-range">
            <span class="material-symbols-outlined">calendar_month</span>
            <span><?= htmlspecialchars($date_range) ?></span>
        </div>
    </div>

    <div class="dash-kpi-grid">
        <div class="dash-kpi-card">
            <span class="dash-kpi-icon green"><span class="material-symbols-outlined">request_quote</span></span>
            <div>
                <p>Total Quotations</p>
                <strong><?= number_format($total_quotes) ?></strong>
                <small class="up">Actual quotation records</small>
            </div>
        </div>
        <div class="dash-kpi-card">
            <span class="dash-kpi-icon yellow"><span class="material-symbols-outlined">fact_check</span></span>
            <div>
                <p>Approved Quotations</p>
                <strong><?= number_format($approved_count) ?></strong>
                <small class="up"><?= $conversion_rate ?> conversion</small>
            </div>
        </div>
        <div class="dash-kpi-card">
            <span class="dash-kpi-icon purple"><span class="material-symbols-outlined">conversion_path</span></span>
            <div>
                <p>Conversion Rate</p>
                <strong><?= $conversion_rate ?></strong>
                <small>Approved / total</small>
            </div>
        </div>
        <div class="dash-kpi-card">
            <span class="dash-kpi-icon red"><span class="material-symbols-outlined">timer_off</span></span>
            <div>
                <p>Expired Quotations</p>
                <strong><?= number_format($expired_count) ?></strong>
                <small class="<?= $expired_count > 0 ? 'down' : 'up' ?>">Needs follow up</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <h4>Quotation Value Trend</h4>
                    <span>Last 6 Months</span>
                </div>
                <svg class="dash-line-chart" viewBox="0 0 <?= $chart_w ?> <?= $chart_h ?>" role="img" aria-label="Quotation trend">
                    <?php for ($i = 0; $i <= 4; $i++): $y = $top_pad + ($plot_h / 4) * $i; $value = $chart_max - (($chart_max / 4) * $i); ?>
                        <line x1="<?= $left_pad ?>" y1="<?= $y ?>" x2="<?= $chart_w - $right_pad ?>" y2="<?= $y ?>" class="grid-line" />
                        <text x="8" y="<?= $y + 4 ?>" class="axis-label"><?= htmlspecialchars(str_replace('Rp ', '', dashboard_short_money($value))) ?></text>
                    <?php endfor; ?>
                    <polygon points="<?= htmlspecialchars($area_points) ?>" class="area-fill"></polygon>
                    <polyline points="<?= htmlspecialchars(implode(' ', $points)) ?>" class="trend-line"></polyline>
                    <?php $i = 0; foreach ($trend as $row): $x = $left_pad + ($plot_w / $trend_count) * $i; $y = $top_pad + $plot_h - (($row['value'] / $chart_max) * $plot_h); ?>
                        <circle cx="<?= $x ?>" cy="<?= $y ?>" r="4.5" class="trend-dot"></circle>
                        <text x="<?= $x ?>" y="<?= $chart_h - 9 ?>" text-anchor="middle" class="axis-label"><?= htmlspecialchars($row['label']) ?></text>
                    <?php $i++; endforeach; ?>
                </svg>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-head">
                    <h4>Quotation Status</h4>
                    <span class="material-symbols-outlined">more_vert</span>
                </div>
                <div class="dash-status-wrap">
                    <div class="dash-donut" style="background: conic-gradient(<?= htmlspecialchars($donut_gradient) ?>);">
                        <div><span>Total</span><strong><?= number_format($total_quotes) ?></strong></div>
                    </div>
                    <div class="dash-status-list">
                        <?php foreach ($status_meta as $status => $meta): ?>
                            <div>
                                <span class="dot" style="background: <?= htmlspecialchars($meta['color']) ?>"></span>
                                <span><?= htmlspecialchars($meta['label']) ?></span>
                                <strong><?= number_format($status_counts[$status]) ?> (<?= dashboard_percent($status_counts[$status], max(1, $total_quotes)) ?>)</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <small class="text-secondary">Percentages are based on total quotations.</small>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-8">
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <h4>Latest Quotations</h4>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle dash-table">
                        <thead><tr><th>No.</th><th>Customer</th><th>Date</th><th>Value</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($latest_quotes as $quote): $meta = $status_meta[$quote['status']] ?? $status_meta['draft']; ?>
                                <tr>
                                    <td><a href="<?= admin_url('quotations/detail.php?id=' . (int) $quote['id']) ?>"><?= htmlspecialchars($quote['quote_number']) ?></a></td>
                                    <td><?= htmlspecialchars($quote['customer_name']) ?></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($quote['created_at']))) ?></td>
                                    <td><?= quotation_format_money($quote['grand_total'], $quote['currency']) ?></td>
                                    <td><span class="dash-status <?= htmlspecialchars($meta['class']) ?>"><?= htmlspecialchars($meta['label']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="<?= admin_url('quotations/') ?>" class="dash-link-btn">View All Quotations <span class="material-symbols-outlined">arrow_forward</span></a>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <h4>Quotation Value by Sales</h4>
                    <span>Top 5 Sales</span>
                </div>
                <div class="dash-sales-bars">
                    <?php foreach ($top_sales as $sales => $value): $width = max(5, ($value / $max_sales) * 100); ?>
                        <div class="dash-sales-row">
                            <span><?= htmlspecialchars($sales) ?></span>
                            <div><i style="width: <?= $width ?>%;"></i></div>
                            <strong><?= dashboard_short_money($value) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-secondary">Based on IDR equivalent total quotation value.</small>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
