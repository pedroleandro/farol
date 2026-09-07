<?php
$col = $col ?? 'col-12 col-sm-6 col-lg-3';
$color = $color ?? 'blue';
$icon = $icon ?? 'iconly-boldBag';
$label = $label ?? '';
$value = $value ?? '—';
?>
<div class="<?= $col ?>">
    <div class="card stat-card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-card-icon stats-icon <?= $color ?>">
                    <i class="<?= $icon ?>"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <p class="stat-card-label mb-1"><?= htmlspecialchars($label) ?></p>
                    <h3 class="stat-card-value mb-0"><?= htmlspecialchars((string)$value) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>