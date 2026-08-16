<?php
$homeName = $matchSide($match, 'home');
$awayName = $matchSide($match, 'away');
$homeScore = $matchScore($match, 'home');
$awayScore = $matchScore($match, 'away');
$matchDate = !empty($match['match_date']) ? date('d/m/Y', strtotime($match['match_date'])) : 'Data a definir';
$matchTime = !empty($match['match_time']) ? substr((string) $match['match_time'], 0, 5) : '';
?>
<article class="match-card-pro">
    <div class="match-meta">
        <span><?= e($match['phase'] ?? 'Partida') ?></span>
        <?php if (!empty($match['group_name'])): ?><span>Grupo <?= e($match['group_name']) ?></span><?php endif; ?>
        <?php if (!empty($match['round_number'])): ?><span>Rodada <?= (int) $match['round_number'] ?></span><?php endif; ?>
    </div>
    <div class="scoreboard">
        <div class="score-team"><strong><?= e($homeName) ?></strong></div>
        <div class="score-box"><b><?= e($homeScore) ?></b><span>x</span><b><?= e($awayScore) ?></b></div>
        <div class="score-team text-end"><strong><?= e($awayName) ?></strong></div>
    </div>
    <div class="match-footer">
        <span><i class="fa-solid fa-calendar"></i><?= e($matchDate) ?> <?= e($matchTime) ?></span>
        <span><i class="fa-solid fa-location-dot"></i><?= e($match['venue'] ?? 'Local a definir') ?><?= !empty($match['court_or_field']) ? ' - ' . e($match['court_or_field']) : '' ?></span>
        <span class="badge text-bg-light"><?= e($match['status'] ?? 'pendente') ?></span>
        <?php if (!empty($isOrganizerManage)): ?>
            <a class="btn btn-sm btn-primary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $match['id'] . '/gerenciar') ?>">Gerenciar partida</a>
        <?php endif; ?>
    </div>
</article>
