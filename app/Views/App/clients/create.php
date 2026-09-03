<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Novo Cliente | " . APP_NAME,
        'menuActive' => 'clientes',
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
                    <h3>Novo Cliente</h3>
                    <p class="text-subtitle text-muted">Preencha as informações para cadastrar um novo cliente</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= url('/clientes') ?>">Clientes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Novo</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?= \App\Core\Message::render() ?>

        <section class="section">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="bi bi-person-plus-fill me-2"></i>
                                Informações do Cliente
                            </h4>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/clientes/cadastrar') ?>" method="post">
                                <?= csrf_input() ?>

                                <div class="form-group">
                                    <label for="name" class="form-label">Nome</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" name="name" id="name"
                                               class="form-control"
                                               value="<?= old('name') ?>"
                                               placeholder="Nome Completo"
                                               required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <label for="city" class="form-label">Cidade</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                                <input type="text" name="city" id="city"
                                                       class="form-control"
                                                       value="<?= old('city') ?>"
                                                       placeholder="Cidade">
                                            </div>
                                            <small class="text-muted">Campo opcional.</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="state" class="form-label">UF</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-signpost-2-fill"></i></span>
                                                <input type="text" name="state" id="state"
                                                       class="form-control text-uppercase"
                                                       value="<?= old('state') ?>"
                                                       placeholder="MA"
                                                       maxlength="2">
                                            </div>
                                            <small class="text-muted">Campo opcional.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Salvar
                                    </button>
                                    <a href="<?= url('/clientes') ?>" class="btn btn-secondary">
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
