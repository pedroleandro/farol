<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Pedidos | " . APP_NAME,
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
                    <h3>Pedidos</h3>
                    <p class="text-subtitle text-muted">Lista de todos os pedidos cadastrados</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pedidos</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <?= \App\Core\Message::render() ?>
        <div class="page-content">
            <section class="section">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-truck me-2"></i>
                            Todos os Pedidos
                        </h5>
                        <?php if (($userRole ?? '') !== \App\Models\User::ROLE_STAKEHOLDER): ?>
                            <a href="<?= url('/pedidos/cadastrar') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>
                                Novo Pedido
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <input type="text" id="ordersSearch" class="form-control"
                                   placeholder="Buscar por número, cliente, cidade...">
                        </div>

                        <table class="table table-hover table-responsive-cards mb-0" id="ordersTable">
                            <thead>
                            <tr>
                                <th>Nº Pedido</th>
                                <th>Cliente</th>
                                <th>Cidade/UF</th>
                                <th>Frete</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <?php $client = $clients[$order->getClientId()] ?? null; ?>
                                    <tr>
                                        <td data-label="Nº Pedido">
                                            <i class="bi bi-hash text-muted"></i>
                                            <?= htmlspecialchars($order->getOrderNumber() ?? '—') ?>
                                        </td>
                                        <td data-label="Cliente">
                                            <i class="bi bi-person-fill text-primary me-1"></i>
                                            <?= htmlspecialchars($client?->getName() ?? '—') ?>
                                        </td>
                                        <td data-label="Cidade/UF">
                                            <i class="bi bi-geo-alt-fill text-muted me-1"></i>
                                            <?= htmlspecialchars($client?->getLocation() ?? '—') ?>
                                        </td>
                                        <td data-label="Frete">
                                            <?= htmlspecialchars($order->getFreightTypeLabel()) ?>
                                        </td>
                                        <td data-label="Status">
                                            <span class="badge <?= $order->getStatusBadgeClass() ?>">
                                                <?= htmlspecialchars($order->getStatusLabel()) ?>
                                            </span>
                                        </td>
                                        <td data-label="Ações" class="text-nowrap">
                                            <?php if (($userRole ?? '') !== \App\Models\User::ROLE_STAKEHOLDER): ?>
                                                <a href="<?= url('/pedidos/editar/' . $order->getId()) ?>"
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <?php if ($order->canAdvanceStatus()): ?>
                                                    <form action="<?= url('/pedidos/' . $order->getId() . '/avancar-status') ?>"
                                                          method="POST" class="d-inline">
                                                        <?= csrf_input() ?>
                                                        <button type="submit" class="btn btn-sm btn-primary"
                                                                title="Avançar para: <?= htmlspecialchars(\App\Models\Order::STATUS_LABELS[$order->getNextStatus()]) ?>">
                                                            <i class="bi bi-arrow-right-circle-fill"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (in_array($userRole ?? '', \App\Models\User::ROLES_CAN_DELETE_ORDERS)): ?>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            title="Excluir"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalExcluir<?= $order->getId() ?>">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                    <div class="modal fade text-left"
                                                         id="modalExcluir<?= $order->getId() ?>"
                                                         tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered"
                                                             role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger">
                                                                    <h5 class="modal-title white">
                                                                        <i class="bi bi-trash-fill me-2"></i>
                                                                        Excluir Pedido
                                                                    </h5>
                                                                    <button type="button" class="close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close">
                                                                        <i data-feather="x"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Tem certeza que deseja excluir o pedido
                                                                    <strong><?= htmlspecialchars($order->getOrderNumber()) ?></strong>?
                                                                    <br>
                                                                    <small class="text-muted">Esta ação não poderá
                                                                        ser
                                                                        desfeita.</small>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button"
                                                                            class="btn btn-light-secondary"
                                                                            data-bs-dismiss="modal">
                                                                        <span class="d-none d-sm-block">Cancelar</span>
                                                                    </button>
                                                                    <form action="<?= url('/pedidos/excluir/' . $order->getId()) ?>"
                                                                          method="POST" class="d-inline">
                                                                        <?= csrf_input() ?>
                                                                        <input type="hidden" name="_method"
                                                                               value="DELETE">
                                                                        <button type="submit"
                                                                                class="btn btn-danger ms-1">
                                                                            <span class="d-none d-sm-block">Confirmar</span>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Somente leitura</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted fst-italic py-4">
                                        <i class="bi bi-inbox-fill me-2"></i>
                                        Nenhum pedido cadastrado ainda.
                                        <?php if (($userRole ?? '') !== \App\Models\User::ROLE_STAKEHOLDER): ?>
                                            <a href="<?= url('/pedidos/cadastrar') ?>">Cadastrar o primeiro</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>

                        <p id="ordersNoResults" class="text-muted fst-italic text-center py-3 d-none">
                            Nenhum pedido encontrado para essa busca.
                        </p>

                    </div>
                </div>
            </section>
        </div>
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

<script src="<?= assets('/js/table-search-paginate.js') ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        initTableSearchPagination({
            tableId: "ordersTable",
            searchInputId: "ordersSearch",
            noResultsId: "ordersNoResults",
            perPage: 10,
        });
    });
</script>