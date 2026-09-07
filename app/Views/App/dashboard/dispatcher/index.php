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

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldBag',
                    'color' => 'blue',
                    'label' => 'Pedidos no mês',
                    'value' => $ordersInMonth ?? 0,
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

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Pedidos de <?= htmlspecialchars($formatMonthOption($selectedMonth)) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentOrders)): ?>
                            <table class="table table-hover table-responsive-cards mb-0">
                                <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <?php $client = $recentOrdersClients[$order->getClientId()] ?? null; ?>
                                    <tr>
                                        <td data-label="Pedido">
                                            <strong>#<?= htmlspecialchars($order->getOrderNumber()) ?></strong>
                                        </td>
                                        <td data-label="Cliente">
                                            <?= htmlspecialchars($client?->getName() ?? '—') ?>
                                        </td>
                                        <td data-label="Status">
                                            <span class="badge <?= $order->getStatusBadgeClass() ?>">
                                                <?= htmlspecialchars($order->getStatusLabel()) ?>
                                            </span>
                                        </td>
                                        <td data-label="Ações">
                                            <a href="<?= url('/pedidos/editar/' . $order->getId()) ?>"
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye-fill me-1"></i>
                                                Ver pedido
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted fst-italic mb-0">
                                Nenhum pedido cadastrado neste mês.
                            </p>
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