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
        <section class="row">

            <?php
            $partial = match ($userRole ?? '') {
                \App\Models\User::ROLE_ADMIN => 'dashboard/partials/cards-admin',
                \App\Models\User::ROLE_MANAGER => 'dashboard/partials/cards-manager',
                \App\Models\User::ROLE_DISPATCHER => 'dashboard/partials/cards-dispatcher',
                \App\Models\User::ROLE_STAKEHOLDER => 'dashboard/partials/cards-stakeholder',
                default => null,
            };
            ?>

            <?php if ($partial): ?>
                <?= $this->insert($partial) ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning">
                        Nenhum painel configurado para este perfil de usuário.
                    </div>
                </div>
            <?php endif; ?>

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
