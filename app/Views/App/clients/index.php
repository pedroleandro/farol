<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Clientes | " . APP_NAME,
        'menuActive' => 'clientes',
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
                    <h3>Clientes</h3>
                    <p class="text-subtitle text-muted">Lista de todos os clientes cadastrados</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Clientes</li>
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
                            <i class="bi bi-people-fill me-2"></i>
                            Todos os Clientes
                        </h5>
                        <?php if (($userRole ?? '') !== \App\Models\User::ROLE_STAKEHOLDER): ?>
                            <a href="<?= url('/clientes/cadastrar') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-person-plus-fill me-1"></i>
                                Novo Cliente
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <input type="text" id="clientsSearch" class="form-control"
                                   placeholder="Buscar por nome, cidade...">
                        </div>

                        <table class="table table-hover table-responsive-cards mb-0" id="clientsTable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Cidade/UF</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($clients)): ?>
                                <?php foreach ($clients as $client): ?>
                                    <?php $hasOrders = in_array($client->getId(), $clientsWithOrders ?? [], true); ?>
                                    <tr>
                                        <td data-label="ID"><?= $client->getId() ?></td>
                                        <td data-label="Nome">
                                            <i class="bi bi-person-fill text-primary me-1"></i>
                                            <?= htmlspecialchars($client->getName()) ?>
                                        </td>
                                        <td data-label="Cidade/UF">
                                            <i class="bi bi-geo-alt-fill text-muted me-1"></i>
                                            <?= htmlspecialchars($client->getLocation()) ?>
                                        </td>
                                        <td data-label="Ações">
                                            <?php if (($userRole ?? '') !== \App\Models\User::ROLE_STAKEHOLDER): ?>
                                                <a href="<?= url('/clientes/editar/' . $client->getId()) ?>"
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil-fill"></i>
                                                    <span class="d-none d-xl-inline ms-1">Editar</span>
                                                </a>
                                                <?php if ($hasOrders): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" disabled
                                                            title="Cliente possui pedidos vinculados e não pode ser excluído"
                                                            data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash-fill"></i>
                                                        <span class="d-none d-xl-inline ms-1">Excluir</span>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalExcluir<?= $client->getId() ?>">
                                                        <i class="bi bi-trash-fill"></i>
                                                        <span class="d-none d-xl-inline ms-1">Excluir</span>
                                                    </button>
                                                    <div class="modal fade text-left"
                                                         id="modalExcluir<?= $client->getId() ?>"
                                                         tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered"
                                                             role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger">
                                                                    <h5 class="modal-title text-white">
                                                                        <i class="bi bi-trash-fill me-2"></i>
                                                                        Excluir Cliente
                                                                    </h5>
                                                                    <button type="button" class="close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close">
                                                                        <i data-feather="x"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body" style="overflow-wrap: break-word; word-break: break-all;">
                                                                    Tem certeza que deseja excluir o cliente
                                                                    <strong><?= htmlspecialchars($client->getName()) ?></strong>?
                                                                    <small class="text-muted d-block mt-1">Esta ação não poderá ser desfeita.</small>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button"
                                                                            class="btn btn-light-secondary"
                                                                            data-bs-dismiss="modal">
                                                                        Cancelar
                                                                    </button>
                                                                    <form action="<?= url('/clientes/excluir/' . $client->getId()) ?>"
                                                                          method="POST" class="d-inline">
                                                                        <?= csrf_input() ?>
                                                                        <input type="hidden" name="_method"
                                                                               value="DELETE">
                                                                        <button type="submit"
                                                                                class="btn btn-danger ms-1">
                                                                            Confirmar
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
                                    <td colspan="4" class="text-center text-muted fst-italic py-4">
                                        <i class="bi bi-inbox-fill me-2"></i>
                                        Nenhum cliente cadastrado ainda.
                                        <?php if (($userRole ?? '') !== \App\Models\User::ROLE_STAKEHOLDER): ?>
                                            <a href="<?= url('/clientes/cadastrar') ?>">Cadastrar o primeiro</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>

                        <p id="clientsNoResults" class="text-muted fst-italic text-center py-3 d-none">
                            Nenhum cliente encontrado para essa busca.
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
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (el) {
            new bootstrap.Tooltip(el);
        });

        initTableSearchPagination({
            tableId: "clientsTable",
            searchInputId: "clientsSearch",
            noResultsId: "clientsNoResults",
            perPage: 10,
        });
    });
</script>