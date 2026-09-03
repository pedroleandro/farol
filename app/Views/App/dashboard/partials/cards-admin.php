<?= $this->insert('dashboard/partials/_kpis-business') ?>

<div class="col-12">
    <hr class="my-2">
    <h5 class="mb-3">Administração do sistema</h5>
</div>

<div class="col-12 col-xl-6">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-person-gear me-2"></i>
                Usuários do sistema
            </h5>
            <a href="<?= url('/usuarios') ?>" class="btn btn-sm btn-primary">
                Ver todos
            </a>
        </div>
        <div class="card-body">
            <p class="mb-1">Total de usuários ativos: <strong>—</strong></p>
            <p class="mb-0 text-muted">0</p>
        </div>
    </div>
</div>

<div class="col-12 col-xl-6">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>
                Última importação de planilha
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted fst-italic mb-0">—</p>
        </div>
    </div>
</div>
