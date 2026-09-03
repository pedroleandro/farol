<div class="col-12">
    <div class="row g-3">

        <?= $this->insert('dashboard/partials/_stat-card', [
            'col' => 'col-6 col-lg-4',
            'icon' => 'iconly-boldBag',
            'color' => 'blue',
            'label' => 'Pedidos cadastrados hoje',
            'value' => 0,
        ]) ?>

        <?= $this->insert('dashboard/partials/_stat-card', [
            'col' => 'col-6 col-lg-4',
            'icon' => 'iconly-boldEdit',
            'color' => 'orange',
            'label' => 'Aguardando atualização de status',
            'value' => 0,
        ]) ?>

        <?= $this->insert('dashboard/partials/_stat-card', [
            'col' => 'col-6 col-lg-4',
            'icon' => 'iconly-boldDanger',
            'color' => 'red',
            'label' => 'Pedidos em atraso',
            'value' => 0,
        ]) ?>

    </div>
</div>

<div class="col-12 mt-2">

</div>
