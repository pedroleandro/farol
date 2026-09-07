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
        <h3>Dashboard</h3>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="page-content">
        <section class="row g-3">

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldProfile',
                    'color' => 'blue',
                    'label' => 'Usuários ativos',
                    'value' => $activeUsers ?? 0,
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldProfile',
                    'color' => 'green',
                    'label' => 'Último login: ' . htmlspecialchars($lastLoginUserName ?? '—'),
                    'value' => $lastLoginAt ?? '—',
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldTick-Square',
                    'color' => 'purple',
                    'label' => 'Logins hoje',
                    'value' => $totalLoginsToday ?? 0,
            ]) ?>

            <?= $this->insert('dashboard/partials/_stat-card', [
                    'col' => 'col-12 col-sm-6 col-lg-3',
                    'icon' => 'iconly-boldBag',
                    'color' => 'orange',
                    'label' => 'Pedidos criados hoje',
                    'value' => $ordersCreatedToday ?? 0,
            ]) ?>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people-fill me-2"></i>
                            Usuários por papel
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($usersByRole)): ?>
                            <?php foreach ($usersByRole as $i => $row): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 <?= $i < count($usersByRole) - 1 ? 'border-bottom' : '' ?>">
                                    <span><?= htmlspecialchars($row['label']) ?></span>
                                    <span class="badge bg-primary"><?= $row['total'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted fst-italic mb-0">Nenhum usuário ativo cadastrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Ações realizadas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentLogs)): ?>
                            <table class="table table-hover table-responsive-cards mb-0">
                                <thead>
                                <tr>
                                    <th>Quem</th>
                                    <th>O quê</th>
                                    <th>Detalhe</th>
                                    <th>Quando</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                    <?php $user = $recentLogsUsers[$log->getUserId()] ?? null; ?>
                                    <tr>
                                        <td data-label="Quem">
                                            <i class="bi bi-person-fill text-primary me-1"></i>
                                            <?= htmlspecialchars($user?->getName() ?? '—') ?>
                                        </td>
                                        <td data-label="O quê">
                                            <?= htmlspecialchars($log->getEventLabel()) ?>
                                        </td>
                                        <td data-label="Detalhe">
                                            <?= htmlspecialchars($log->getDetail()) ?>
                                        </td>
                                        <td data-label="Quando">
                                            <?= $log->getCreatedAt()
                                                    ? date('d/m/Y H:i', strtotime($log->getCreatedAt()))
                                                    : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted fst-italic mb-0">Nenhuma ação registrada ainda.</p>
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