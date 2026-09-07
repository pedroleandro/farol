<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Novo Pedido | " . APP_NAME,
        'menuActive' => 'pedidos',
        'submenuActive' => 'novo',
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
                    <h3>Novo Pedido</h3>
                    <p class="text-subtitle text-muted">Preencha as informações do pedido e do frete</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= url('/pedidos') ?>">Pedidos</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Novo</li>
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
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="bi bi-truck me-2"></i>
                                Informações do Pedido
                            </h4>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/pedidos/cadastrar') ?>" method="post" id="orderForm">
                                <?= csrf_input() ?>

                                <!-- Cliente: select pesquisável via Choices.js -->
                                <div class="form-group">
                                    <label for="client_id" class="form-label">Cliente *</label>
                                    <select name="client_id" id="client_id" class="form-select" required>
                                        <option value="">Selecione o cliente</option>
                                        <?php if ($clients): ?>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?= $client->getId() ?>"
                                                        <?= old('client_id') == $client->getId() ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($client->getName()) ?>
                                                    (<?= htmlspecialchars($client->getLocation()) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>
                                                Nenhum cliente cadastrado — cadastre um primeiro
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (!$clients): ?>
                                        <small class="text-muted">
                                            <a href="<?= url('/clientes/cadastrar') ?>">Cadastrar o primeiro cliente</a>
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="product_qty" class="form-label">Qtd. de produtos *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-box-seam-fill"></i></span>
                                                <input type="number" name="product_qty" id="product_qty"
                                                       class="form-control" min="0" required
                                                       value="<?= old('product_qty') ?>">
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
                                                       value="<?= old('item_qty') ?>">
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
                                                       value="<?= old('invoice_number') ?>">
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

                                <!-- Tipo de Frete, Veículo e Valor do Frete na mesma linha -->
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="freight_type" class="form-label">Tipo de Frete *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-truck-flatbed"></i></span>
                                                <select name="freight_type" id="freight_type" class="form-select" required>
                                                    <option value="" disabled selected>Selecione o tipo</option>
                                                    <?php foreach (\App\Models\Order::FREIGHT_TYPE_LABELS as $value => $label): ?>
                                                        <option value="<?= $value ?>"
                                                                <?= old('freight_type') === $value ? 'selected' : '' ?>>
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
                                                       placeholder="Ex: Caminhão, Strada, Volvo"
                                                       value="<?= old('vehicle_type') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="driver_name" class="form-label">Motorista</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                                                <input type="text" name="driver_name" id="driver_name"
                                                       class="form-control"
                                                       placeholder="Nome do motorista"
                                                       value="<?= old('driver_name') ?>">
                                            </div>
                                            <small class="text-muted">Campo opcional.</small>
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
                                                       value="<?= old('freight_value') ?>">
                                            </div>
                                            <small class="text-muted">Opcional — pode ser definido depois.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data do pedido, Data de carregamento e Previsão de entrega na mesma linha -->
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="order_date" class="form-label">Data do pedido *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-calendar-event-fill"></i></span>
                                                <input type="date" name="order_date" id="order_date"
                                                       class="form-control" required
                                                       value="<?= old('order_date') ?>">
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
                                                       value="<?= old('loading_date') ?>">
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
                                                       value="<?= old('expected_delivery') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-muted small mt-2">* Campos obrigatórios</p>

                                <div class="form-group mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Salvar Pedido
                                    </button>
                                    <a href="<?= url('/pedidos') ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left-circle-fill me-1"></i>
                                        Cancelar
                                    </a>
                                </div>
                            </form>
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