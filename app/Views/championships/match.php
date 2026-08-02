<?php
$current = null;
foreach ($matches as $row) {
    if ((int) $row['id'] === (int) $match['id']) {
        $current = $row;
        break;
    }
}
$current ??= $match;
$events = $eventsByMatch[(int) $match['id']] ?? [];
$sets = $setsByMatch[(int) $match['id']] ?? [];
$home = $current['home_team_name'] ?? $current['home_athlete_name'] ?? 'A definir';
$away = $current['away_team_name'] ?? $current['away_athlete_name'] ?? 'A definir';
?>
<section class="page-band"><div class="container"><h1><?= e($home) ?> x <?= e($away) ?></h1><p><?= e($championship['name']) ?></p></div></section>
<section class="container py-4 match-public-page">
    <div class="match-score-hero">
        <div class="match-score-team"><img src="<?= e(team_shield_url(['name' => $home])) ?>" alt="Escudo de <?= e($home) ?>"><strong><?= e($home) ?></strong></div>
        <div class="match-score-result"><span><?= $current['home_score'] !== null ? (int) $current['home_score'] : '-' ?></span><small>x</small><span><?= $current['away_score'] !== null ? (int) $current['away_score'] : '-' ?></span></div>
        <div class="match-score-team"><img src="<?= e(team_shield_url(['name' => $away])) ?>" alt="Escudo de <?= e($away) ?>"><strong><?= e($away) ?></strong></div>
        <div class="info-grid mt-3">
            <span><i class="fa-solid fa-layer-group"></i><?= e($current['phase']) ?></span>
            <span><i class="fa-solid fa-calendar"></i><?= e($current['match_date'] ? date('d/m/Y', strtotime($current['match_date'])) : 'Nao informado') ?></span>
            <span><i class="fa-solid fa-clock"></i><?= e($current['match_time'] ? substr($current['match_time'], 0, 5) : 'Nao informado') ?></span>
            <span><i class="fa-solid fa-location-dot"></i><?= e($current['venue'] ?? 'Nao informado') ?></span>
        </div>
    </div>
    <div class="row g-3 mt-1">
        <div class="col-lg-6"><div class="panel h-100 timeline-modern"><h2>Linha do tempo</h2><?php foreach ($events as $event): ?><div class="timeline-item"><strong><?= e(($event['minute'] ?? '') . (!empty($event['additional_time']) ? '+' . $event['additional_time'] : '') . "'") ?></strong> <?= e($event['athlete_name'] ?? 'Evento') ?> - <?= e(str_replace('_', ' ', $event['event_type'])) ?> - <?= e($event['team_name'] ?? '') ?></div><?php endforeach; ?><?php if (!$events): ?><p class="text-muted">Nenhum evento registrado.</p><?php endif; ?></div></div>
        <div class="col-lg-6"><div class="panel h-100 timeline-modern"><h2>Sets</h2><?php foreach ($sets as $set): ?><div class="timeline-item">Set <?= (int) $set['set_number'] ?>: <?= (int) $set['home_score'] ?> x <?= (int) $set['away_score'] ?></div><?php endforeach; ?><?php if (!$sets): ?><p class="text-muted">Nenhum set registrado.</p><?php endif; ?></div></div>
    </div>
</section>
