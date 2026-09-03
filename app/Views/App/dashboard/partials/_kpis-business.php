<div class="col-12">
    <div class="row g-3">

        <?= $this->insert('dashboard/partials/_stat-card', [
            'icon' => 'iconly-boldBag',
            'color' => 'blue',
            'label' => 'Pedidos no mês',
            'value' => '—',
        ]) ?>

        <?= $this->insert('dashboard/partials/_stat-card', [
            'icon' => 'iconly-boldWallet',
            'color' => 'purple',
            'label' => 'Valor total em frete',
            'value' => '—',
        ]) ?>

        <?= $this->insert('dashboard/partials/_stat-card', [
            'icon' => 'iconly-boldTick-Square',
            'color' => 'green',
            'label' => 'Entregues no prazo',
            'value' => '—',
        ]) ?>

        <?= $this->insert('dashboard/partials/_stat-card', [
            'icon' => 'iconly-boldTime-Circle',
            'color' => 'red',
            'label' => 'Tempo médio de entrega',
            'value' => '—',
        ]) ?>

    </div>
</div>
