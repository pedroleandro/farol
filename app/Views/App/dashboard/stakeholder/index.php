<?php
$monthNames = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
];

$formatMonthOption = function (string $ym) use ($monthNames): string {
    [$year, $month] = explode('-', $ym);
    return ($monthNames[$month] ?? $month) . ' de ' . $year;
};

$oldestAvailableMonth = $availableMonths[count($availableMonths) - 1] ?? $selectedMonth;
?>
<?= $this->layout('dashboard/app', [
    'title' => $title ?? "Dashboard | " . APP_NAME,
    'menuActive' => 'dashboard',
    'userRole' => $userRole ?? '',
]) ?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <h3 class="mb-0">Dashboard</h3>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="page-content">

        <!-- ================= SEÇÃO MENSAL ================= -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0 text-muted">Visão do mês</h5>
            <form method="get" action="<?= url('/dashboard') ?>" class="d-flex align-items-center gap-2">
                <input type="hidden" name="ano" value="<?= htmlspecialchars($selectedYear) ?>">
                <input
                    type="month"
                    name="mes"
                    id="mes"
                    class="form-control form-control-sm"
                    style="width: 170px;"
                    value="<?= htmlspecialchars($selectedMonth) ?>"
                    min="<?= htmlspecialchars($oldestAvailableMonth) ?>"
                    max="<?= htmlspecialchars($currentMonth) ?>"
                    onchange="this.form.submit()"
                >
            </form>
        </div>

        <section class="row g-3 mb-4">

            <?= $this->insert('dashboard/partials/_stat-card', [
                'col' => 'col-12 col-sm-6 col-lg-3',
                'icon' => 'iconly-boldBag',
                'color' => 'blue',
                'label' => 'Pedidos no mês',
                'value' => $totalOrders ?? 0,
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                'col' => 'col-12 col-sm-6 col-lg-3',
                'icon' => 'iconly-boldWallet',
                'color' => 'purple',
                'label' => 'Valor total em frete',
                'value' => 'R$ ' . number_format($totalFreightValue ?? 0, 2, ',', '.'),
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                'col' => 'col-12 col-sm-6 col-lg-3',
                'icon' => 'iconly-boldTick-Square',
                'color' => 'green',
                'label' => 'Entregues no prazo',
                'value' => isset($onTimeRate) && $onTimeRate !== null
                    ? number_format($onTimeRate, 1, ',', '.') . '%'
                    : '—',
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                'col' => 'col-12 col-sm-6 col-lg-3',
                'icon' => 'iconly-boldTime-Circle',
                'color' => 'red',
                'label' => 'Tempo médio de entrega',
                'value' => isset($avgDeliveryDays) && $avgDeliveryDays !== null
                    ? number_format($avgDeliveryDays, 1, ',', '.') . ' dias'
                    : '—',
            ]) ?>

            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pedidos por status</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="ordersByStatusChart" class="w-100"
                             data-labels='<?= htmlspecialchars(json_encode($statusLabels ?? []), ENT_QUOTES) ?>'
                             data-values='<?= htmlspecialchars(json_encode($statusValues ?? []), ENT_QUOTES) ?>'>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Valor de frete por tipo (mês)</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="freightByTypeChart" class="w-100"
                             data-labels='<?= htmlspecialchars(json_encode($freightTypeLabels ?? []), ENT_QUOTES) ?>'
                             data-values='<?= htmlspecialchars(json_encode($freightTypeValues ?? []), ENT_QUOTES) ?>'>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- ================= SEÇÃO ANUAL (exclusiva) ================= -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0 text-muted">Visão do ano</h5>
            <form method="get" action="<?= url('/dashboard') ?>" class="d-flex align-items-center gap-2">
                <input type="hidden" name="mes" value="<?= htmlspecialchars($selectedMonth) ?>">
                <select name="ano" id="ano" class="form-select form-select-sm" style="width: auto;"
                        onchange="this.form.submit()">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?= htmlspecialchars($year) ?>" <?= $year === $selectedYear ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year) ?>
                            <?= $year === $currentYear ? ' (atual)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <section class="row g-3">

            <?= $this->insert('dashboard/partials/_stat-card', [
                'col' => 'col-12',
                'icon' => 'iconly-boldWallet',
                'color' => 'blue',
                'label' => 'Valor total gasto no ano',
                'value' => 'R$ ' . number_format($totalFreightYear ?? 0, 2, ',', '.'),
            ]) ?>

            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">% de gasto por tipo de frete (ano)</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="freightShareByTypeYearChart" class="w-100"
                             data-labels='<?= htmlspecialchars(json_encode($freightShareLabels ?? []), ENT_QUOTES) ?>'
                             data-values='<?= htmlspecialchars(json_encode($freightShareValues ?? []), ENT_QUOTES) ?>'>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Gasto com frete por mês (<?= htmlspecialchars($selectedYear) ?>)</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="freightByMonthYearChart" class="w-100"
                             data-labels='<?= htmlspecialchars(json_encode($freightByMonthLabels ?? []), ENT_QUOTES) ?>'
                             data-values='<?= htmlspecialchars(json_encode($freightByMonthValues ?? []), ENT_QUOTES) ?>'>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <footer class="mt-5">
        <div class="footer clearfix mb-0 text-muted">
            <div class="float-start">
                <p><?= date('Y') . " - " . APP_NAME ?></p>
            </div>
            <div class="float-end">
                <p>
                    Desenvolvido com
                    <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                    por <a href="" target="_blank"><?= APP_DEVELOPER ?></a>
                </p>
            </div>
        </div>
    </footer>
</div>

<script src="<?= assets_mazer('/assets/extensions/apexcharts/apexcharts.min.js') ?>"></script>
<script src="<?= assets('/js/charts/orders-by-status-chart.js') ?>"></script>
<script src="<?= assets('/js/charts/freight-by-type-chart.js') ?>"></script>
<script src="<?= assets('/js/charts/freight-share-by-type-year-chart.js') ?>"></script>
<script src="<?= assets('/js/charts/freight-by-month-year-chart.js') ?>"></script>