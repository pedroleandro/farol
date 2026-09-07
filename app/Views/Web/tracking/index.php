<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Rastreio de Pedido" ?></title>
    <link rel="stylesheet" href="<?= assets_mazer('/assets/compiled/css/app.css') ?>">
    <link rel="stylesheet" href="<?= assets_mazer('/assets/compiled/css/app-dark.css') ?>">
</head>
<body>
<script src="<?= assets_mazer('/assets/static/js/initTheme.js') ?>"></script>

<div class="container py-5" style="max-width: 640px;">
    <div class="text-center mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-truck me-2"></i>
            Rastreio de Pedido
        </h2>
        <p class="text-muted">Digite o código de rastreio informado pela transportadora.</p>
    </div>

    <form method="get" action="<?= url('/rastreio') ?>" class="card mb-4">
        <div class="card-body d-flex gap-2">
            <input type="text" name="codigo" class="form-control text-uppercase"
                   placeholder="Ex: FR-A1B2C3D4"
                   value="<?= htmlspecialchars($code ?? '') ?>" required autofocus>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>
                Buscar
            </button>
        </div>
    </form>

    <?php if ($notFound): ?>
        <div class="alert alert-warning text-center">
            Nenhum pedido encontrado com esse código de rastreio.
            Confira se digitou corretamente.
        </div>
    <?php endif; ?>

    <?php if ($order): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    Código: <?= htmlspecialchars($order->getTrackingCode()) ?>
                </h5>
                <span class="badge <?= $order->getStatusBadgeClass() ?> fs-6">
                    <?= htmlspecialchars($order->getStatusLabel()) ?>
                </span>
            </div>

            <div class="card-body">
                <p class="mb-2">
                    <i class="bi bi-geo-alt-fill text-muted me-1"></i>
                    Destino: <strong><?= htmlspecialchars($client?->getLocation() ?? '—') ?></strong>
                </p>
                <p class="mb-0">
                    <i class="bi bi-calendar-event-fill text-muted me-1"></i>
                    Previsão de entrega:
                    <strong>
                        <?= $order->getExpectedDelivery()
                            ? date('d/m/Y', strtotime($order->getExpectedDelivery()))
                            : '—' ?>
                    </strong>
                </p>
                <?php if ($order->getDeliveryDate()): ?>
                    <p class="mb-0 mt-2 text-success">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Entregue em <?= date('d/m/Y', strtotime($order->getDeliveryDate())) ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($history)): ?>
                <div class="card-body border-top">
                    <h6 class="text-muted mb-3">Histórico</h6>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($history as $entry): ?>
                            <li class="mb-2">
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($entry->getToStatusLabel()) ?>
                                </span>
                                <small class="text-muted ms-2">
                                    <?= $entry->getCreatedAt()
                                        ? date('d/m/Y H:i', strtotime($entry->getCreatedAt()))
                                        : '' ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p class="text-center text-muted small mt-4 mb-0">
        <?= date('Y') ?> — <?= APP_NAME ?>
    </p>
</div>
</body>
</html>