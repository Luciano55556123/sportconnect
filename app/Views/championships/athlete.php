<section class="page-band"><div class="container"><h1><?= e($athlete['name']) ?></h1><p><?= e($championship['name']) ?></p></div></section>
<section class="container py-4">
    <div class="panel">
        <div class="info-grid">
            <span><i class="fa-solid fa-people-group"></i><?= e($athlete['team_name'] ?? 'Sem equipe') ?></span>
            <span><i class="fa-solid fa-city"></i><?= e($athlete['city'] ?? 'Nao informado') ?></span>
            <span><i class="fa-solid fa-shirt"></i><?= e((string) ($athlete['shirt_number'] ?? 'Nao informado')) ?></span>
            <span><i class="fa-solid fa-location-crosshairs"></i><?= e($athlete['position'] ?? 'Nao informado') ?></span>
            <span><i class="fa-solid fa-layer-group"></i><?= e($athlete['category'] ?? 'Nao informado') ?></span>
            <span><i class="fa-solid fa-signal"></i><?= e($athlete['status']) ?></span>
        </div>
    </div>
</section>
