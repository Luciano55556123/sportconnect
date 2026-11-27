<?php
$competitionData = $competitionData ?? [];
$competitionCounts = $competitionCounts ?? [];
$championship = $championship ?? [];
$isOrganizerManage = !empty($isOrganizerManage);
$teams = $competitionData['teams'] ?? [];
$matches = $competitionData['matches'] ?? [];
$standings = $competitionData['standings'] ?? [];
$events = $competitionData['events'] ?? [];
$statistics = $competitionData['statistics'] ?? [];
$sets = $competitionData['sets'] ?? [];
$reports = $competitionData['reports'] ?? [];
$reschedules = $competitionData['reschedules'] ?? [];
$summary = $competitionData['summary'] ?? ['progress' => 0, 'counts' => $competitionCounts];
$counts = $summary['counts'] ?? $competitionCounts;
$progress = (int) ($summary['progress'] ?? 0);
$totalCompetitionRows = array_sum(array_map('intval', $competitionCounts));
$matchSide = static fn(array $row, string $side): string => (string) (($row[$side . '_team'] ?? '') ?: ($row[$side . '_athlete'] ?? 'A definir'));
$matchScore = static fn(array $row, string $side): string => is_numeric($row[$side . '_score'] ?? null) ? (string) (int) $row[$side . '_score'] : '-';
$sportName = mb_strtolower((string) ($championship['sport_name'] ?? ''), 'UTF-8');
$isGoalSport = in_array($sportName, ['futebol', 'futsal', 'handebol'], true);
$isPointSport = in_array($sportName, ['volei', 'vôlei', 'voleibol', 'basquete', 'beach tennis', 'tenis de mesa', 'tênis de mesa', 'tenis', 'tênis'], true);
$scoreLabel = $isGoalSport ? 'Gols' : ($isPointSport ? 'Pontos' : 'Pontuação');
$scoreField = $isGoalSport ? 'goals' : 'points';
$hideScoreTotal = (int) ($championship['id'] ?? 0) === 23;
$scoreTotal = 0;
$setsByMatch = [];
foreach ($sets as $set) {
    $mid = (int) ($set['match_id'] ?? 0);
    $setsByMatch[$mid] = ($setsByMatch[$mid] ?? 0) + (int) ($set['home_score'] ?? 0) + (int) ($set['away_score'] ?? 0);
}
foreach ($matches as $match) {
    if (!in_array($match['status'] ?? '', ['finalizada', 'completed', 'encerrada'], true)) continue;
    $mid = (int) ($match['id'] ?? 0);
    $scoreTotal += !$isGoalSport && isset($setsByMatch[$mid]) ? $setsByMatch[$mid] : (int) ($match['home_score'] ?? 0) + (int) ($match['away_score'] ?? 0);
}
$cardEvents = array_values(array_filter($events, static fn(array $e): bool => in_array($e['event_type'] ?? '', ['cartao_amarelo', 'cartao_vermelho'], true)));
$yellowTotal = count(array_filter($cardEvents, static fn(array $e): bool => ($e['event_type'] ?? '') === 'cartao_amarelo'));
$redTotal = count(array_filter($events, static fn(array $e): bool => ($e['event_type'] ?? '') === 'cartao_vermelho'));
$topScorers = array_values(array_filter($statistics, static fn(array $row): bool => (int) ($row[$scoreField] ?? 0) > 0));
usort($topScorers, static fn(array $a, array $b): int => (int) ($b[$scoreField] ?? 0) <=> (int) ($a[$scoreField] ?? 0));
$cards = array_values(array_filter($statistics, static fn(array $row): bool => ((int) ($row['yellow_cards'] ?? 0) + (int) ($row['red_cards'] ?? 0)) > 0));
$pointEvents = array_values(array_filter($events, static fn(array $row): bool => in_array((string) ($row['event_type'] ?? ''), $isGoalSport ? ['gol', 'penalti_convertido'] : ['ponto', 'ataque', 'ace', 'bloqueio'], true)));
$nextMatches = array_values(array_filter($matches, static fn(array $row): bool => !in_array((string) ($row['status'] ?? ''), ['finalizada', 'completed', 'encerrada'], true)));
$recentMatches = array_slice(array_reverse($matches), 0, 4);
$groupedRounds = [];
foreach ($matches as $match) {
    $round = (string) ($match['round_number'] ?? 'Sem rodada');
    $groupedRounds[$round][] = $match;
}
$phaseLabels = [
    'Classificatoria' => 'Fase classificatória',
    'Repescagem' => 'Repescagem',
    'Quartas de final' => 'Quartas de final',
    'Semifinais' => 'Semifinais',
    'Disputa de 3º lugar' => 'Disputa de 3º lugar',
    'Final' => 'Final',
];
$matchesByPhase = [];
foreach ($phaseLabels as $phase => $label) {
    $matchesByPhase[$phase] = [
        'label' => $label,
        'matches' => [],
    ];
}
foreach ($matches as $match) {
    $phase = (string) ($match['phase'] ?? '');
    if ($phase === '') {
        $phase = 'Sem fase';
    }
    if (!isset($matchesByPhase[$phase])) {
        $matchesByPhase[$phase] = [
            'label' => $phase,
            'matches' => [],
        ];
    }
    $matchesByPhase[$phase]['matches'][] = $match;
}
$matchesByPhase = array_filter($matchesByPhase, static fn(array $phase): bool => !empty($phase['matches']));
$statCards = [
    ['icon' => 'fa-solid fa-people-group', 'value' => (int) ($counts['teams'] ?? 0), 'label' => 'Equipes'],
    ['icon' => 'fa-solid fa-person-running', 'value' => (int) ($counts['athletes'] ?? 0), 'label' => 'Atletas'],
    ['icon' => 'fa-solid fa-calendar-days', 'value' => (int) ($counts['matches'] ?? 0), 'label' => 'Partidas'],
    ['icon' => 'fa-solid fa-circle-check', 'value' => (int) ($counts['completed_matches'] ?? 0), 'label' => 'Concluídas'],
    ['icon' => $isGoalSport ? 'fa-solid fa-futbol' : 'fa-solid fa-volleyball', 'value' => $scoreTotal, 'label' => $scoreLabel],
    ['icon' => 'fa-solid fa-id-card', 'value' => (int) ($occupiedSpots ?? (new \App\Models\Championship())->occupiedSpots($championship)), 'label' => ($championship['registration_type'] ?? 'individual') === 'team' ? 'Equipes inscritas' : 'Participantes inscritos'],
];
if ($hideScoreTotal) {
    $statCards = array_values(array_filter($statCards, static fn(array $card): bool => $card['label'] !== $scoreLabel));
}
if ($isGoalSport) {
    $statCards[] = ['icon' => 'fa-solid fa-square', 'value' => $yellowTotal, 'label' => 'Cartões amarelos'];
    $statCards[] = ['icon' => 'fa-solid fa-square', 'value' => $redTotal, 'label' => 'Cartões vermelhos'];
}
?>

<?php if ($totalCompetitionRows > 0): ?>
    <section class="sc-board" aria-label="Andamento do campeonato">
        <div class="sc-stat-grid">
            <?php foreach ($statCards as $card): ?>
                <?php ['icon' => $icon, 'value' => $value, 'label' => $label] = $card; require BASE_PATH . '/app/Views/championships/_stat_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <p class="small text-muted mb-3">O total de inscritos inclui cadastros do organizador e inscrições online válidas, sem duplicar participantes. <?php if (!$hideScoreTotal): ?><?= e($scoreLabel) ?> somados pelos placares das partidas concluídas; quando há sets, pelos placares dos sets.<?php endif; ?></p>
        <section class="sc-progress-panel">
            <div>
                <span class="sc-eyebrow">Progresso do campeonato</span>
                <h2><?= (int) ($counts['completed_matches'] ?? 0) ?> de <?= (int) ($counts['matches'] ?? 0) ?> partidas concluídas</h2>
                <p><?= count($nextMatches) ?> pendências ou partidas futuras acompanhadas pelo painel.</p>
            </div>
            <div class="sc-progress-meter">
                <strong><?= $progress ?>%</strong>
                <div class="sc-progress-track" aria-label="Progresso <?= $progress ?>%">
                    <span style="--progress: <?= $progress ?>%"></span>
                </div>
            </div>
        </section>

        <nav class="sc-tabs" role="tablist" aria-label="Seções do campeonato">
            <button class="active" id="tab-overview" role="tab" aria-controls="panel-overview" aria-selected="true" data-tab-target="overview" type="button"><i class="fa-solid fa-chart-line"></i>Visão geral</button>
            <button id="tab-teams" role="tab" aria-controls="panel-teams" aria-selected="false" data-tab-target="teams" type="button"><i class="fa-solid fa-shield-halved"></i>Equipes</button>
            <button id="tab-matches" role="tab" aria-controls="panel-matches" aria-selected="false" data-tab-target="matches" type="button"><i class="fa-solid fa-calendar-days"></i>Partidas</button>
            <button id="tab-standings" role="tab" aria-controls="panel-standings" aria-selected="false" data-tab-target="standings" type="button"><i class="fa-solid fa-ranking-star"></i>Classificação</button>
            <button id="tab-bracket" role="tab" aria-controls="panel-bracket" aria-selected="false" data-tab-target="bracket" type="button"><i class="fa-solid fa-sitemap"></i>Rodadas</button>
            <button id="tab-stats" role="tab" aria-controls="panel-stats" aria-selected="false" data-tab-target="stats" type="button"><i class="fa-solid fa-chart-simple"></i>Estatísticas</button>
            <button id="tab-events" role="tab" aria-controls="panel-events" aria-selected="false" data-tab-target="events" type="button"><i class="fa-solid fa-bolt"></i>Eventos</button>
            <button id="tab-goals" role="tab" aria-controls="panel-goals" aria-selected="false" data-tab-target="goals" type="button"><i class="<?= $isGoalSport ? 'fa-solid fa-futbol' : 'fa-solid fa-volleyball' ?>"></i><?= e($scoreLabel) ?></button>
            <?php if ($isGoalSport || $cardEvents): ?>
                <button id="tab-cards" role="tab" aria-controls="panel-cards" aria-selected="false" data-tab-target="cards" type="button"><i class="fa-solid fa-square"></i>Cartões (<?= count($cardEvents) ?>)</button>
            <?php endif; ?>
            <?php if ($isGoalSport): ?>
                <button id="tab-scorers" role="tab" aria-controls="panel-scorers" aria-selected="false" data-tab-target="scorers" type="button"><i class="fa-solid fa-futbol"></i>Artilharia</button>
            <?php endif; ?>
            <button id="tab-history" role="tab" aria-controls="panel-history" aria-selected="false" data-tab-target="history" type="button"><i class="fa-solid fa-clock-rotate-left"></i>Histórico</button>
        </nav>

        <div class="sc-tab-content">
            <section class="sc-tab-panel active" id="panel-overview" role="tabpanel" aria-labelledby="tab-overview" data-tab-panel="overview">
                <div class="sc-dashboard-grid">
                    <article class="sc-panel sc-panel-wide">
                        <div class="sc-panel-head"><div><span class="sc-eyebrow">Agenda</span><h3>Próximas e últimas partidas</h3></div><span><?= count($matches) ?> jogos</span></div>
                        <div class="sc-match-list">
                            <?php foreach (array_slice($matches, 0, 5) as $match): ?>
                                <?php require BASE_PATH . '/app/Views/championships/_match_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="sc-panel">
                        <div class="sc-panel-head"><div><span class="sc-eyebrow">Destaque</span><h3><?= $isGoalSport ? 'Artilheiros' : 'Maiores pontuadores' ?></h3></div></div>
                        <div class="sc-rank-list">
                            <?php foreach (array_slice($topScorers, 0, 5) as $index => $stat): ?>
                                <div class="sc-rank-row"><span><?= $index + 1 ?></span><div><strong><?= e($stat['athlete_name'] ?? 'Atleta') ?></strong><small><?= e($stat['team_name'] ?? '') ?></small></div><b><?= (int) ($stat[$scoreField] ?? 0) ?></b></div>
                            <?php endforeach; ?>
                            <?php if (!$topScorers): ?><p class="text-muted mb-0">Nenhuma pontuação individual registrada.</p><?php endif; ?>
                        </div>
                    </article>

                    <article class="sc-panel">
                        <div class="sc-panel-head"><div><span class="sc-eyebrow">Situação</span><h3>Atividades recentes</h3></div></div>
                        <div class="sc-activity-list">
                            <?php foreach (array_slice($events, 0, 5) as $event): ?>
                                <p><strong><?= e($event['event_type'] ?? 'Evento') ?></strong><span><?= (int) ($event['minute'] ?? 0) ?>' - <?= e($event['athlete_name'] ?? '') ?></span></p>
                            <?php endforeach; ?>
                            <?php if (!$events): ?><p class="text-muted mb-0">Nenhuma atividade registrada.</p><?php endif; ?>
                        </div>
                    </article>
                </div>
            </section>

            <section class="sc-tab-panel" id="panel-teams" role="tabpanel" aria-labelledby="tab-teams" data-tab-panel="teams" hidden>
                <div class="sc-team-grid">
                    <?php foreach ($teams as $team): ?>
                        <?php require BASE_PATH . '/app/Views/championships/_team_card.php'; ?>
                    <?php endforeach; ?>
                    <?php if (!$teams): ?><article class="sc-empty-state"><i class="fa-solid fa-shield-halved"></i><strong>Nenhum dado disponivel nesta secao.</strong></article><?php endif; ?>
                </div>
            </section>

            <section class="sc-tab-panel" id="panel-matches" role="tabpanel" aria-labelledby="tab-matches" data-tab-panel="matches" hidden>
                <?php if ($matchesByPhase): ?>
                    <?php foreach ($matchesByPhase as $phase): ?>
                        <section class="sc-panel mb-3">
                            <div class="sc-panel-head"><div><span class="sc-eyebrow">Fase</span><h3><?= e($phase['label']) ?></h3></div><span><?= count($phase['matches']) ?> partidas</span></div>
                            <div class="sc-match-list">
                                <?php foreach ($phase['matches'] as $match): ?>
                                    <?php require BASE_PATH . '/app/Views/championships/_match_card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="sc-empty-state"><i class="fa-solid fa-calendar-days"></i><strong>Nenhum dado disponivel nesta secao.</strong></article>
                <?php endif; ?>
            </section>

            <section class="sc-tab-panel" id="panel-standings" role="tabpanel" aria-labelledby="tab-standings" data-tab-panel="standings" hidden>
                <article class="sc-panel">
                    <div class="sc-panel-head"><div><span class="sc-eyebrow">Tabela</span><h3>Classificação</h3></div></div>
                    <?php if ($standings): ?><?php require BASE_PATH . '/app/Views/championships/_standings_table.php'; ?><?php else: ?><article class="sc-empty-state"><i class="fa-solid fa-ranking-star"></i><strong>Nenhum dado disponivel nesta secao.</strong></article><?php endif; ?>
                </article>
            </section>

            <section class="sc-tab-panel" id="panel-bracket" role="tabpanel" aria-labelledby="tab-bracket" data-tab-panel="bracket" hidden>
                <?php if (count($groupedRounds) > 1): ?>
                    <div class="sc-bracket">
                        <?php foreach ($groupedRounds as $round => $roundMatches): ?>
                            <section class="sc-round">
                                <h3>Rodada <?= e($round) ?></h3>
                                <?php foreach ($roundMatches as $match): ?>
                                    <div class="sc-round-match">
                                        <span><?= e($matchSide($match, 'home')) ?></span>
                                        <strong><?= e($matchScore($match, 'home')) ?> x <?= e($matchScore($match, 'away')) ?></strong>
                                        <span><?= e($matchSide($match, 'away')) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <article class="sc-empty-state"><i class="fa-solid fa-sitemap"></i><strong>Este campeonato nao possui fase eliminatoria configurada.</strong><span>As partidas cadastradas continuam disponiveis na aba Partidas.</span></article>
                <?php endif; ?>
            </section>

            <section class="sc-tab-panel" id="panel-stats" role="tabpanel" aria-labelledby="tab-stats" data-tab-panel="stats" hidden>
                <div class="sc-dashboard-grid">
                    <article class="sc-panel"><div class="sc-panel-head"><h3><?= e($scoreLabel) ?></h3></div><?php foreach (array_slice($topScorers, 0, 8) as $stat): ?><div class="sc-rank-row"><div><strong><?= e($stat['athlete_name'] ?? '') ?></strong><small><?= e($stat['team_name'] ?? '') ?></small></div><b><?= (int) ($stat[$scoreField] ?? 0) ?> <?= $isGoalSport ? 'gols' : 'pts' ?></b></div><?php endforeach; ?><?php if (!$topScorers): ?><p class="text-muted mb-0">Nenhum dado disponivel nesta secao.</p><?php endif; ?></article>
                    <article class="sc-panel"><div class="sc-panel-head"><h3><?= $isGoalSport ? 'Disciplina' : 'Fundamentos' ?></h3></div><?php foreach (array_slice($topScorers, 0, 8) as $stat): ?><div class="sc-rank-row"><div><strong><?= e($stat['athlete_name'] ?? '') ?></strong><small><?= e($stat['team_name'] ?? '') ?></small></div><b><?php if ($isGoalSport): ?><?= (int) ($stat['yellow_cards'] ?? 0) ?> amarelos / <?= (int) ($stat['red_cards'] ?? 0) ?> vermelhos<?php else: ?><?= (int) ($stat['aces'] ?? 0) ?> aces / <?= (int) ($stat['blocks'] ?? 0) ?> bloqueios<?php endif; ?></b></div><?php endforeach; ?><?php if (!$topScorers): ?><p class="text-muted mb-0">Nenhum dado disponivel nesta secao.</p><?php endif; ?></article>
                </div>
            </section>

            <section class="sc-tab-panel" id="panel-events" role="tabpanel" aria-labelledby="tab-events" data-tab-panel="events" hidden>
                <div class="sc-filter-row" aria-label="Filtros visuais de eventos"><span>Todos</span><span><?= e($scoreLabel) ?></span><?php if ($isGoalSport): ?><span>Cartões amarelos</span><span>Cartões vermelhos</span><?php else: ?><span>Aces</span><span>Bloqueios</span><?php endif; ?><span>Observações</span></div>
                <div class="sc-timeline">
                    <?php foreach ($events as $event): ?>
                        <?php $eventType = (string) ($event['event_type'] ?? 'Evento'); $eventIcon = str_contains($eventType, 'cartao') ? 'fa-solid fa-square' : (in_array($eventType, ['gol', 'penalti_convertido', 'ponto', 'ataque', 'ace', 'bloqueio'], true) ? 'fa-solid fa-volleyball' : 'fa-solid fa-circle-info'); ?>
                        <article class="sc-timeline-item">
                            <span class="sc-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <i class="<?= e($eventIcon) ?>" aria-hidden="true"></i>
                            <div><strong><?= e($eventType) ?></strong><p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p><?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?></div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$events): ?><article class="sc-empty-state"><i class="fa-solid fa-bolt"></i><strong>Nenhum dado disponivel nesta secao.</strong></article><?php endif; ?>
                </div>
            </section>

            <section class="sc-tab-panel" id="panel-goals" role="tabpanel" aria-labelledby="tab-goals" data-tab-panel="goals" hidden>
                <div class="sc-timeline">
                    <?php foreach ($pointEvents as $event): ?>
                        <article class="sc-timeline-item">
                            <span class="sc-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <i class="fa-solid fa-volleyball" aria-hidden="true"></i>
                            <div><strong><?= e($event['event_type'] ?? 'Ponto') ?></strong><p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p><?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?></div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$pointEvents): ?><article class="sc-empty-state"><i class="fa-solid fa-volleyball"></i><strong>Nenhum dado disponivel nesta secao.</strong></article><?php endif; ?>
                </div>
            </section>

            <?php if ($isGoalSport || $cardEvents): ?>
            <section class="sc-tab-panel" id="panel-cards" role="tabpanel" aria-labelledby="tab-cards" data-tab-panel="cards" hidden>
                <article class="sc-panel">
                    <div class="sc-panel-head"><div><span class="sc-eyebrow">Disciplina</span><h3>Cartões por jogador</h3></div><span><?= $yellowTotal ?> amarelos / <?= $redTotal ?> vermelhos</span></div>
                    <?php if ($cardEvents): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Jogador</th><th>Equipe</th><th>Cartão</th><th>Partida</th><th>Minuto</th></tr></thead>
                            <tbody>
                            <?php foreach ($cardEvents as $cardEvent): ?>
                                <?php $red = ($cardEvent['event_type'] ?? '') === 'cartao_vermelho'; ?>
                                <tr>
                                    <td><?= e(($cardEvent['athlete_name'] ?? '') ?: 'Não informado') ?></td>
                                    <td><?= e(($cardEvent['team_name'] ?? '') ?: 'Não informada') ?></td>
                                    <td><span class="badge <?= $red ? 'text-bg-danger' : 'text-bg-warning' ?>"><i class="fa-solid fa-square me-1"></i><?= $red ? 'Vermelho' : 'Amarelo' ?></span></td>
                                    <td><?= e(($cardEvent['home_team'] ?? '') ?: 'A definir') ?> × <?= e(($cardEvent['away_team'] ?? '') ?: 'A definir') ?></td>
                                    <td><?= isset($cardEvent['minute']) ? (int) $cardEvent['minute'] . "'" : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?><p class="text-muted mb-0">Nenhum cartão registrado.</p><?php endif; ?>
                </article>
            </section>
            <?php endif; ?>

            <?php if ($isGoalSport): ?>
            <section class="sc-tab-panel" id="panel-scorers" role="tabpanel" aria-labelledby="tab-scorers" data-tab-panel="scorers" hidden>
                <article class="sc-panel">
                    <div class="sc-panel-head"><div><span class="sc-eyebrow">Gols por jogador</span><h3>Artilharia do campeonato</h3></div><span><?= count($topScorers) ?> artilheiros</span></div>
                    <?php if ($topScorers): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Posição</th><th>Jogador</th><th>Equipe</th><th>Gols</th></tr></thead>
                                <tbody>
                                <?php $rank = 0; $lastGoals = null; foreach ($topScorers as $index => $scorer): ?>
                                    <?php $goals = (int) ($scorer['goals'] ?? 0); if ($goals !== $lastGoals) { $rank = $index + 1; $lastGoals = $goals; } ?>
                                    <tr><td><?= $rank ?>º</td><td><?= e(($scorer['athlete_name'] ?? '') ?: 'Não informado') ?></td><td><?= e(($scorer['team_name'] ?? '') ?: 'Não informada') ?></td><td><strong><?= $goals ?></strong></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="small text-muted mb-0">Classificação baseada nos gols registrados individualmente, que podem não abranger todos os gols dos placares.</p>
                    <?php else: ?><p class="text-muted mb-0">Nenhum gol individual registrado.</p><?php endif; ?>
                </article>
            </section>
            <?php endif; ?>

            <section class="sc-tab-panel" id="panel-history" role="tabpanel" aria-labelledby="tab-history" data-tab-panel="history" hidden>
                <div class="sc-dashboard-grid">
                    <article class="sc-panel"><div class="sc-panel-head"><h3>Relatórios</h3></div><?php foreach ($reports as $report): ?><p class="sc-log"><strong><?= e($report['referee_name'] ?? 'Relatorio') ?></strong><span><?= e($report['summary'] ?? '') ?></span></p><?php endforeach; ?><?php if (!$reports): ?><p class="text-muted mb-0">Nenhum relatorio registrado.</p><?php endif; ?></article>
                    <article class="sc-panel"><div class="sc-panel-head"><h3>Operações</h3></div><p class="sc-log"><strong>Sets registrados</strong><span><?= count($sets) ?> registros</span></p><p class="sc-log"><strong>Reagendamentos</strong><span><?= count($reschedules) ?> registros</span></p><p class="sc-log"><strong>Partidas concluídas</strong><span><?= (int) ($counts['completed_matches'] ?? 0) ?> registros</span></p></article>
                </div>
            </section>
        </div>
    </section>
<?php endif; ?>
