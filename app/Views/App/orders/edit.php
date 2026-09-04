<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Editar Pedido | " . APP_NAME,
        'menuActive' => 'pedidos',
        'submenuActive' => 'todos',
        'userRole' => $userRole ?? '',
]) ?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Editar Pedido</h3>
                    <p class="text-subtitle text-muted">
                        Alterando o pedido <strong><?= htmlspecialchars($order->getOrderNumber()) ?></strong>
                    </p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= url('/pedidos') ?>">Pedidos</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?= \App\Core\Message::render() ?>

        <section class="section">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-pencil-fill me-2"></i>
                                Editar Pedido
                            </h4>
                            <div class="d-flex gap-2">
                                <?php if ($order->isPending()): ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Pendente
                                    </span>
                                <?php endif; ?>
                                <span class="badge <?= $order->getStatusBadgeClass() ?>">
                                    <?= htmlspecialchars($order->getStatusLabel()) ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body pb-0">
                            <div class="border rounded-3 p-3 d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <small class="text-muted d-block">Código de rastreio</small>
                                    <strong id="trackingCode" class="fs-5"><?= htmlspecialchars($order->getTrackingCode()) ?></strong>
                                </div>
                                <button type="button" id="copyTrackingBtn" class="btn btn-sm btn-outline-primary" onclick="copyTrackingCode()">
                                    <i class="bi bi-clipboard-fill me-1"></i>
                                    <span id="copyTrackingBtnLabel">Copiar</span>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="<?= url('/pedidos/editar/' . $order->getId()) ?>" method="post" id="orderForm">
                                <?= csrf_input() ?>
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="id" value="<?= $order->getId() ?>">

                                <div class="form-group">
                                    <label for="client_id" class="form-label">Cliente *</label>
                                    <select name="client_id" id="client_id" class="form-select" required>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client->getId() ?>"
                                                    <?= $order->getClientId() === $client->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($client->getName()) ?>
                                                (<?= htmlspecialchars($client->getLocation()) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="product_qty" class="form-label">Qtd. de produtos *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-box-seam-fill"></i></span>
                                                <input type="number" name="product_qty" id="product_qty"
                                                       class="form-control" min="0" required
                                                       value="<?= old('product_qty', (string)($order->getProductQty() ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="item_qty" class="form-label">Qtd. de itens *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-boxes"></i></span>
                                                <input type="number" name="item_qty" id="item_qty"
                                                       class="form-control" min="0" required
                                                       value="<?= old('item_qty', (string)($order->getItemQty() ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="invoice_number" class="form-label">Nota Fiscal</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                                                <input type="text" name="invoice_number" id="invoice_number"
                                                       class="form-control"
                                                       value="<?= old('invoice_number', htmlspecialchars($order->getInvoiceNumber() ?? '')) ?>">
                                            </div>
                                            <small class="text-muted">Campo opcional.</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-truck me-1"></i>
                                    Frete
                                </h6>

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="freight_type" class="form-label">Tipo de Frete *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-truck-flatbed"></i></span>
                                                <select name="freight_type" id="freight_type" class="form-select" required>
                                                    <option value="" disabled>Selecione o tipo</option>
                                                    <?php foreach (\App\Models\Order::FREIGHT_TYPE_LABELS as $value => $label): ?>
                                                        <option value="<?= $value ?>"
                                                                <?= $order->getFreightType() === $value ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($label) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="vehicle_type" class="form-label">Veículo *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                                <input type="text" name="vehicle_type" id="vehicle_type"
                                                       class="form-control" required
                                                       value="<?= old('vehicle_type', htmlspecialchars($order->getVehicleType() ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="freight_value" class="form-label">Valor do Frete (R$)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="text" name="freight_value" id="freight_value"
                                                       class="form-control money-input" inputmode="decimal"
                                                       placeholder="0,00"
                                                       value="<?= old('freight_value', $order->getFreightValueFormatted()) ?>">
                                            </div>
                                            <small class="text-muted">Opcional — pode ser definido depois.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="order_date" class="form-label">Data do pedido *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-calendar-event-fill"></i></span>
                                                <input type="date" name="order_date" id="order_date"
                                                       class="form-control" required
                                                       value="<?= old('order_date', $order->getOrderDate() ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="loading_date" class="form-label">Data de carregamento *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                                <input type="date" name="loading_date" id="loading_date"
                                                       class="form-control" required
                                                       value="<?= old('loading_date', $order->getLoadingDate() ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="expected_delivery" class="form-label">Previsão de entrega *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-calendar-plus"></i></span>
                                                <input type="date" name="expected_delivery" id="expected_delivery"
                                                       class="form-control" required
                                                       value="<?= old('expected_delivery', $order->getExpectedDelivery() ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($order->getDeliveryDate()): ?>
                                    <div class="alert alert-success mt-2 mb-0">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Entregue em <?= date('d/m/Y', strtotime($order->getDeliveryDate())) ?>
                                        (preenchido automaticamente ao avançar o status).
                                    </div>
                                <?php endif; ?>

                                <p class="text-muted small mt-3">* Campos obrigatórios</p>

                                <div class="form-group d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Atualizar
                                    </button>
                                    <a href="<?= url('/pedidos') ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left-circle-fill me-1"></i>
                                        Cancelar
                                    </a>
                                </div>
                            </form>

                            <hr>
                            <h6 class="fw-bold mb-3">Ações de status</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if ($order->canAdvanceStatus()): ?>
                                    <form action="<?= url('/pedidos/' . $order->getId() . '/avancar-status') ?>" method="post">
                                        <?= csrf_input() ?>
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="bi bi-arrow-right-circle-fill me-1"></i>
                                            Avançar para "<?= htmlspecialchars(\App\Models\Order::STATUS_LABELS[$order->getNextStatus()]) ?>"
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($order->canMarkPending()): ?>
                                    <form action="<?= url('/pedidos/' . $order->getId() . '/marcar-pendente') ?>" method="post">
                                        <?= csrf_input() ?>
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            Marcar como pendente
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($order->canResolvePending()): ?>
                                    <form action="<?= url('/pedidos/' . $order->getId() . '/resolver-pendencia') ?>" method="post">
                                        <?= csrf_input() ?>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            Resolver pendência
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <?php if ($order->isPending()): ?>
                                <small class="text-muted d-block mt-2">
                                    O pedido continua em "<?= htmlspecialchars($order->getStatusLabel()) ?>" —
                                    resolver a pendência não faz o status avançar sozinho.
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer>
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

<link rel="stylesheet" href="<?= assets_mazer('/assets/extensions/choices.js/public/assets/styles/choices.min.css') ?>">
<script src="<?= assets_mazer('/assets/extensions/choices.js/public/assets/scripts/choices.min.js') ?>"></script>
<script src="<?= assets('/js/order-form.js') ?>"></script>


<script>
    function copyTrackingCode() {
        const code = document.getElementById('trackingCode').innerText;
        const btn = document.getElementById('copyTrackingBtn');
        const label = document.getElementById('copyTrackingBtnLabel');

        navigator.clipboard.writeText(code).then(function () {
            label.textContent = 'Copiado!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');

            setTimeout(function () {
                label.textContent = 'Copiar';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        });
    }
</script>
