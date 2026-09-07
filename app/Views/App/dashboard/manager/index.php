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

    <div class="page-heading d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="mb-0">Dashboard</h3>

        <form method="get" action="<?= url('/dashboard') ?>" class="d-flex align-items-center gap-2">
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

    <?= \App\Core\Message::render() ?>

    <div class="page-content">
        <section class="row g-3">

            <!-- KPIs de negócio, escopados pelo mês selecionado -->
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

            <!-- Financeiro, escopado pelo mês selecionado -->
            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-6',
                    'icon' => 'iconly-boldWallet',
                    'color' => 'purple',
                    'label' => 'Frete médio por pedido',
                    'value' => isset($avgFreightPerOrder) && $avgFreightPerOrder !== null
                            ? 'R$ ' . number_format($avgFreightPerOrder, 2, ',', '.')
                            : '—',
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-6',
                    'icon' => 'iconly-boldWallet',
                    'color' => 'blue',
                    'label' => 'Gasto médio por dia',
                    'value' => 'R$ ' . number_format($avgDailyFreight ?? 0, 2, ',', '.'),
            ]) ?>

            <!-- Pontos de atenção, sempre "agora", não muda com o mês selecionado -->
            <div class="col-12">
                <hr class="my-2">
                <h6 class="text-muted mb-0">Pontos de atenção</h6>
            </div>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldTime-Circle',
                    'color' => 'blue',
                    'label' => 'Vencendo em breve',
                    'value' => $dueSoon ?? 0,
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldEdit',
                    'color' => 'orange',
                    'label' => 'Aguardando atualização',
                    'value' => $awaitingStatusUpdate ?? 0,
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldDanger',
                    'color' => 'red',
                    'label' => 'Em atraso',
                    'value' => $lateOrders ?? 0,
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldDanger',
                    'color' => 'purple',
                    'label' => 'Pendentes',
                    'value' => $pendingOrders ?? 0,
            ]) ?>

            <!-- Gráficos, escopados pelo mês selecionado -->
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
                        <h5 class="card-title mb-0">Valor de frete por tipo</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="freightByTypeChart" class="w-100"
                             data-labels='<?= htmlspecialchars(json_encode($freightTypeLabels ?? []), ENT_QUOTES) ?>'
                             data-values='<?= htmlspecialchars(json_encode($freightTypeValues ?? []), ENT_QUOTES) ?>'>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Pedidos por dia — <?= htmlspecialchars($formatMonthOption($selectedMonth)) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="ordersByDayChart"
                             data-labels='<?= htmlspecialchars(json_encode($ordersByDayLabels ?? []), ENT_QUOTES) ?>'
                             data-values='<?= htmlspecialchars(json_encode($ordersByDayValues ?? []), ENT_QUOTES) ?>'>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Última importação -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>
                            Última importação de planilha
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($lastImport)): ?>
                            <p class="mb-1">
                                Por <strong><?= htmlspecialchars($lastImport['user_name']) ?></strong>
                                em <?= htmlspecialchars($lastImport['created_at']) ?>
                            </p>
                            <p class="mb-0 text-muted">
                                <?= $lastImport['created_count'] ?> criados,
                                <?= $lastImport['updated_count'] ?> atualizados,
                                <?= $lastImport['error_count'] ?> com erro
                            </p>
                        <?php else: ?>
                            <p class="text-muted fst-italic mb-0">Nenhuma importação realizada ainda.</p>
                        <?php endif; ?>
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
<script src="<?= assets('/js/charts/orders-by-day-chart.js') ?>"></script>