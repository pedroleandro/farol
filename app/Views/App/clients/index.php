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
                        <a href="<?= url('/clientes/cadastrar') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus-fill me-1"></i>
                            Novo Cliente
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="table1">
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
                                        <tr>
                                            <td><?= $client->getId() ?></td>
                                            <td>
                                                <i class="bi bi-person-fill text-primary me-1"></i>
                                                <?= htmlspecialchars($client->getName()) ?>
                                            </td>
                                            <td>
                                                <i class="bi bi-geo-alt-fill text-muted me-1"></i>
                                                <?= htmlspecialchars($client->getLocation()) ?>
                                            </td>
                                            <td>
                                                <a href="<?= url('/clientes/editar/' . $client->getId()) ?>"
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil-fill"></i>
                                                    <span class="d-none d-xl-inline ms-1">Editar</span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalExcluir<?= $client->getId() ?>">
                                                    <i class="bi bi-trash-fill"></i>
                                                    <span class="d-none d-xl-inline ms-1">Excluir</span>
                                                </button>

                                                <div class="modal fade text-left"
                                                     id="modalExcluir<?= $client->getId() ?>"
                                                     tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger">
                                                                <h5 class="modal-title white">
                                                                    <i class="bi bi-trash-fill me-2"></i>
                                                                    Excluir Cliente
                                                                </h5>
                                                                <button type="button" class="close"
                                                                        data-bs-dismiss="modal" aria-label="Close">
                                                                    <i data-feather="x"></i>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Tem certeza que deseja excluir o cliente
                                                                <strong><?= htmlspecialchars($client->getName()) ?></strong>?
                                                                <br>
                                                                <small class="text-muted">Esta ação não poderá ser
                                                                    desfeita.</small>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light-secondary"
                                                                        data-bs-dismiss="modal">
                                                                    <span class="d-none d-sm-block">Cancelar</span>
                                                                </button>
                                                                <form action="<?= url('/clientes/excluir/' . $client->getId()) ?>"
                                                                      method="POST" class="d-inline">
                                                                    <?= csrf_input() ?>
                                                                    <input type="hidden" name="_method" value="DELETE">
                                                                    <button type="submit" class="btn btn-danger ms-1">
                                                                        <span class="d-none d-sm-block">Confirmar</span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted fst-italic py-4">
                                            <i class="bi bi-inbox-fill me-2"></i>
                                            Nenhum cliente cadastrado ainda.
                                            <a href="<?= url('/clientes/cadastrar') ?>">Cadastrar o primeiro</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
