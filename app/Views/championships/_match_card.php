<?php
$homeName = $matchSide($match, 'home');
$awayName = $matchSide($match, 'away');
$homeScore = $matchScore($match, 'home');
$awayScore = $matchScore($match, 'away');
$matchDate = !empty($match['match_date']) ? date('d/m/Y', strtotime($match['match_date'])) : 'Data a definir';
$matchTime = !empty($match['match_time']) ? substr((string) $match['match_time'], 0, 5) : '';
$status = (string) ($match['status'] ?? 'pendente');
$statusClass = match ($status) {
    'finalizada', 'completed', 'encerrada' => 'is-done',
    'em_andamento', 'in_progress' => 'is-live',
    'agendada', 'scheduled', 'pendente' => 'is-scheduled',
    default => 'is-neutral',
};
?>
<article class="sc-match-card">
    <header class="sc-match-head">
        <div>
            <span><?= e($match['phase'] ?? 'Partida') ?></span>
            <?php if (!empty($match['round_number'])): ?><strong>Rodada <?= (int) $match['round_number'] ?></strong><?php endif; ?>
        </div>
        <span class="sc-match-status <?= e($statusClass) ?>"><?= e($status) ?></span>
    </header>

    <div class="sc-scoreboard" aria-label="<?= e($homeName . ' ' . $homeScore . ' x ' . $awayScore . ' ' . $awayName) ?>">
        <div class="sc-score-team">
            <span>Mandante</span>
            <strong><?= e($homeName) ?></strong>
        </div>
        <div class="sc-score">
            <b><?= e($homeScore) ?></b>
            <span>x</span>
            <b><?= e($awayScore) ?></b>
        </div>
        <div class="sc-score-team sc-score-team-away">
            <span>Visitante</span>
            <strong><?= e($awayName) ?></strong>
        </div>
    </div>

    <footer class="sc-match-foot">
        <span><i class="fa-solid fa-calendar-days" aria-hidden="true"></i><?= e($matchDate) ?></span>
        <?php if ($matchTime !== ''): ?><span><i class="fa-solid fa-clock" aria-hidden="true"></i><?= e($matchTime) ?></span><?php endif; ?>
        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i><?= e($match['venue'] ?? 'Local a definir') ?><?= !empty($match['court_or_field']) ? ' - ' . e($match['court_or_field']) : '' ?></span>
        <?php if (!empty($isOrganizerManage)): ?>
            <a class="btn btn-sm btn-primary sc-match-action" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $match['id'] . '/gerenciar') ?>">Gerenciar partida</a>
        <?php endif; ?>
    </footer>
</article>
