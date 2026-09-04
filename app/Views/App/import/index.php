<?= $this->layout('dashboard/app', [
        'title' => $title ?? "Importar Planilha | " . APP_NAME,
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
                    <h3>Importar Planilha</h3>
                    <p class="text-subtitle text-muted">
                        Sincroniza pedidos e clientes a partir da planilha de controle
                    </p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Importar Planilha</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <?= \App\Core\Message::render() ?>
        <section class="section">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>
                                Enviar planilha
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="border rounded-3 p-3 mb-4">
                                <p class="mb-2">
                                    <strong>Como funciona:</strong> o sistema lê a aba
                                    <code>DADOS</code> da planilha e sincroniza os pedidos
                                    pelo número do pedido.
                                </p>
                                <ul class="mb-0 ps-3">
                                    <li>Pedido novo (número ainda não existe) → é <strong>criado</strong>.</li>
                                    <li>Pedido já existente → é <strong>atualizado</strong>, exceto o status,
                                        que é preservado.</li>
                                    <li>Cliente ainda não cadastrado → é <strong>criado automaticamente</strong>
                                        a partir do nome e cidade da planilha.</li>
                                    <li>Linhas sem número de pedido ou sem nome de cliente
                                        aparecem no relatório como <strong>falha</strong>.</li>
                                </ul>
                            </div>
                            <form action="<?= url('/importar') ?>" method="post" enctype="multipart/form-data" id="importForm">
                                <?= csrf_input() ?>
                                <div class="form-group">
                                    <label for="spreadsheet" class="form-label">Arquivo da planilha (.xlsx)</label>
                                    <input type="file" name="spreadsheet" id="spreadsheet"
                                           class="form-control" accept=".xlsx" required>
                                </div>
                                <div class="form-group mt-4 d-flex gap-2">
                                    <button type="submit" id="importSubmitBtn" class="btn btn-primary">
                                        <i class="bi bi-upload me-1"></i>
                                        <span id="importSubmitLabel">Importar</span>
                                    </button>
                                    <a href="<?= url('/dashboard') ?>" id="importCancelBtn" class="btn btn-secondary">
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

<!-- Overlay de carregamento, exibido enquanto a importação processa -->
<div id="importLoadingOverlay" class="d-none"
     style="position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:2000;
            display:flex; align-items:center; justify-content:center;">
    <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status" style="width:3rem; height:3rem;"></div>
        <div>Processando planilha, isso pode levar alguns segundos...</div>
    </div>
</div>

<script>
    document.getElementById('importForm').addEventListener('submit', function () {
        const submitBtn = document.getElementById('importSubmitBtn');
        const cancelBtn = document.getElementById('importCancelBtn');
        const overlay = document.getElementById('importLoadingOverlay');

        submitBtn.disabled = true;
        document.getElementById('importSubmitLabel').textContent = 'Processando...';
        cancelBtn.classList.add('disabled');
        cancelBtn.setAttribute('aria-disabled', 'true');
        cancelBtn.addEventListener('click', function (e) { e.preventDefault(); });

        overlay.classList.remove('d-none');
    });
</script>