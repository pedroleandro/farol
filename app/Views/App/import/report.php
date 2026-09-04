<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Resultado da Importação | " . APP_NAME,
        'menuActive' => 'importar',
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
                    <h3>Resultado da Importação</h3>
                    <p class="text-subtitle text-muted">
                        Arquivo: <strong><?= htmlspecialchars($fileName) ?></strong>
                    </p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= url('/importar') ?>">Importar Planilha</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Resultado</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">

            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Linhas processadas</h6>
                            <h4 class="mb-0"><?= $totalRows ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Criados</h6>
                            <h4 class="mb-0 text-success"><?= $createdCount ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Atualizados</h6>
                            <h4 class="mb-0 text-primary"><?= $updatedCount ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Com falha</h6>
                            <h4 class="mb-0 text-danger"><?= $errorCount ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check me-2"></i>
                        Detalhe por linha
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="importReportTable">
                            <thead>
                            <tr>
                                <th>Linha</th>
                                <th>Nº Pedido</th>
                                <th>Cliente</th>
                                <th>Resultado</th>
                                <th>Observações</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?= $r['line'] ?></td>
                                    <td><?= htmlspecialchars($r['order_number']) ?></td>
                                    <td><?= htmlspecialchars($r['client_name']) ?></td>
                                    <td>
                                        <?php if ($r['outcome'] === 'created'): ?>
                                            <span class="badge bg-success">Criado</span>
                                        <?php elseif ($r['outcome'] === 'updated'): ?>
                                            <span class="badge bg-primary">Atualizado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Falha</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['reason'])): ?>
                                            <span class="text-danger"><?= htmlspecialchars($r['reason']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($r['warnings'])): ?>
                                            <?php foreach ($r['warnings'] as $w): ?>
                                                <div class="text-muted small">
                                                    <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($w) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if (empty($r['reason']) && empty($r['warnings'])): ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <a href="<?= url('/pedidos') ?>" class="btn btn-primary">
                    <i class="bi bi-truck me-1"></i>
                    Ver Pedidos
                </a>
                <a href="<?= url('/importar') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Nova Importação
                </a>
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof $ !== "undefined" && $.fn.DataTable && !$.fn.DataTable.isDataTable('#importReportTable')) {
            $('#importReportTable').DataTable({
                pageLength: 15,
                lengthChange: false,
                language: {
                    search: "Buscar:",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ linhas",
                    infoEmpty: "Nenhuma linha disponível",
                    infoFiltered: "(filtrado de _MAX_ linhas no total)",
                    paginate: { previous: "Anterior", next: "Próxima" },
                    zeroRecords: "Nenhum resultado encontrado"
                }
            });
        }
    });
</script>