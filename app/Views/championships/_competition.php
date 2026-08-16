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
$topScorers = array_values(array_filter($statistics, static fn(array $row): bool => (int) ($row['goals'] ?? 0) > 0));
$cards = array_values(array_filter($statistics, static fn(array $row): bool => ((int) ($row['yellow_cards'] ?? 0) + (int) ($row['red_cards'] ?? 0)) > 0));
$nextMatches = array_values(array_filter($matches, static fn(array $row): bool => !in_array((string) ($row['status'] ?? ''), ['finalizada', 'completed', 'encerrada'], true)));
$recentMatches = array_slice(array_reverse($matches), 0, 4);
$groupedRounds = [];
foreach ($matches as $match) {
    $round = (string) ($match['round_number'] ?? 'Sem rodada');
    $groupedRounds[$round][] = $match;
}
$statCards = [
    ['icon' => 'fa-solid fa-people-group', 'value' => (int) ($counts['teams'] ?? 0), 'label' => 'Equipes'],
    ['icon' => 'fa-solid fa-person-running', 'value' => (int) ($counts['athletes'] ?? 0), 'label' => 'Atletas'],
    ['icon' => 'fa-solid fa-calendar-days', 'value' => (int) ($counts['matches'] ?? 0), 'label' => 'Partidas'],
    ['icon' => 'fa-solid fa-circle-check', 'value' => (int) ($counts['completed_matches'] ?? 0), 'label' => 'Concluidas'],
    ['icon' => 'fa-solid fa-futbol', 'value' => (int) ($counts['goals'] ?? 0), 'label' => 'Gols'],
    ['icon' => 'fa-solid fa-id-card', 'value' => (int) ($championship['registrations_count'] ?? 0), 'label' => 'Inscricoes'],
];
?>

<?php if ($totalCompetitionRows > 0): ?>
    <section class="sc-board" aria-label="Andamento do campeonato">
        <div class="sc-stat-grid">
            <?php foreach ($statCards as $card): ?>
                <?php ['icon' => $icon, 'value' => $value, 'label' => $label] = $card; require BASE_PATH . '/app/Views/championships/_stat_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <section class="sc-progress-panel">
            <div>
                <span class="sc-eyebrow">Progresso do campeonato</span>
                <h2><?= (int) ($counts['completed_matches'] ?? 0) ?> de <?= (int) ($counts['matches'] ?? 0) ?> partidas concluidas</h2>
                <p><?= count($nextMatches) ?> pendencias ou partidas futuras acompanhadas pelo painel.</p>
            </div>
            <div class="sc-progress-meter">
                <strong><?= $progress ?>%</strong>
                <div class="sc-progress-track" aria-label="Progresso <?= $progress ?>%">
                    <span style="--progress: <?= $progress ?>%"></span>
                </div>
            </div>
        </section>

        <nav class="sc-tabs" aria-label="Secoes do campeonato">
            <button class="active" data-bs-toggle="pill" data-bs-target="#comp-overview" type="button"><i class="fa-solid fa-chart-line"></i>Visao geral</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-teams" type="button"><i class="fa-solid fa-shield-halved"></i>Equipes</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-matches" type="button"><i class="fa-solid fa-calendar-days"></i>Partidas</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-standings" type="button"><i class="fa-solid fa-ranking-star"></i>Classificacao</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-bracket" type="button"><i class="fa-solid fa-sitemap"></i>Chaveamento</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-stats" type="button"><i class="fa-solid fa-chart-simple"></i>Estatisticas</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-events" type="button"><i class="fa-solid fa-bolt"></i>Eventos</button>
            <button data-bs-toggle="pill" data-bs-target="#comp-history" type="button"><i class="fa-solid fa-clock-rotate-left"></i>Historico</button>
        </nav>

        <div class="tab-content sc-tab-content">
            <section class="tab-pane fade show active" id="comp-overview">
                <div class="sc-dashboard-grid">
                    <article class="sc-panel sc-panel-wide">
                        <div class="sc-panel-head"><div><span class="sc-eyebrow">Agenda</span><h3>Proximas e ultimas partidas</h3></div><span><?= count($matches) ?> jogos</span></div>
                        <div class="sc-match-list">
                            <?php foreach (array_slice($matches, 0, 5) as $match): ?>
                                <?php require BASE_PATH . '/app/Views/championships/_match_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="sc-panel">
                        <div class="sc-panel-head"><div><span class="sc-eyebrow">Destaque</span><h3>Artilharia</h3></div></div>
                        <div class="sc-rank-list">
                            <?php foreach (array_slice($topScorers, 0, 5) as $index => $stat): ?>
                                <div class="sc-rank-row"><span><?= $index + 1 ?></span><div><strong><?= e($stat['athlete_name'] ?? 'Atleta') ?></strong><small><?= e($stat['team_name'] ?? '') ?></small></div><b><?= (int) ($stat['goals'] ?? 0) ?></b></div>
                            <?php endforeach; ?>
                            <?php if (!$topScorers): ?><p class="text-muted mb-0">Nenhum gol registrado.</p><?php endif; ?>
                        </div>
                    </article>

                    <article class="sc-panel">
                        <div class="sc-panel-head"><div><span class="sc-eyebrow">Situacao</span><h3>Atividades recentes</h3></div></div>
                        <div class="sc-activity-list">
                            <?php foreach (array_slice($events, 0, 5) as $event): ?>
                                <p><strong><?= e($event['event_type'] ?? 'Evento') ?></strong><span><?= (int) ($event['minute'] ?? 0) ?>' - <?= e($event['athlete_name'] ?? '') ?></span></p>
                            <?php endforeach; ?>
                            <?php if (!$events): ?><p class="text-muted mb-0">Nenhuma atividade registrada.</p><?php endif; ?>
                        </div>
                    </article>
                </div>
            </section>

            <section class="tab-pane fade" id="comp-teams">
                <div class="sc-team-grid">
                    <?php foreach ($teams as $team): ?>
                        <?php require BASE_PATH . '/app/Views/championships/_team_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="tab-pane fade" id="comp-matches">
                <div class="sc-match-list">
                    <?php foreach ($matches as $match): ?>
                        <?php require BASE_PATH . '/app/Views/championships/_match_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="tab-pane fade" id="comp-standings">
                <article class="sc-panel">
                    <div class="sc-panel-head"><div><span class="sc-eyebrow">Tabela</span><h3>Classificacao</h3></div></div>
                    <?php require BASE_PATH . '/app/Views/championships/_standings_table.php'; ?>
                </article>
            </section>

            <section class="tab-pane fade" id="comp-bracket">
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

            <section class="tab-pane fade" id="comp-stats">
                <div class="sc-dashboard-grid">
                    <article class="sc-panel"><div class="sc-panel-head"><h3>Artilheiro</h3></div><?php foreach (array_slice($topScorers, 0, 8) as $stat): ?><div class="sc-rank-row"><div><strong><?= e($stat['athlete_name'] ?? '') ?></strong><small><?= e($stat['team_name'] ?? '') ?></small></div><b><?= (int) ($stat['goals'] ?? 0) ?> gols</b></div><?php endforeach; ?></article>
                    <article class="sc-panel"><div class="sc-panel-head"><h3>Cartoes</h3></div><?php foreach (array_slice($cards, 0, 8) as $stat): ?><div class="sc-rank-row"><div><strong><?= e($stat['athlete_name'] ?? '') ?></strong><small><?= e($stat['team_name'] ?? '') ?></small></div><b><?= (int) ($stat['yellow_cards'] ?? 0) ?>A / <?= (int) ($stat['red_cards'] ?? 0) ?>V</b></div><?php endforeach; ?><?php if (!$cards): ?><p class="text-muted mb-0">Nenhum cartao registrado.</p><?php endif; ?></article>
                </div>
            </section>

            <section class="tab-pane fade" id="comp-events">
                <div class="sc-filter-row" aria-label="Filtros visuais de eventos"><span>Todos</span><span>Gols</span><span>Cartoes</span><span>Observacoes</span></div>
                <div class="sc-timeline">
                    <?php foreach ($events as $event): ?>
                        <?php $eventType = (string) ($event['event_type'] ?? 'Evento'); $eventIcon = str_contains($eventType, 'cartao') ? 'fa-solid fa-square' : (str_contains($eventType, 'gol') || str_contains($eventType, 'penalti') ? 'fa-solid fa-futbol' : 'fa-solid fa-circle-info'); ?>
                        <article class="sc-timeline-item">
                            <span class="sc-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <i class="<?= e($eventIcon) ?>" aria-hidden="true"></i>
                            <div><strong><?= e($eventType) ?></strong><p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p><?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="tab-pane fade" id="comp-history">
                <div class="sc-dashboard-grid">
                    <article class="sc-panel"><div class="sc-panel-head"><h3>Relatorios</h3></div><?php foreach ($reports as $report): ?><p class="sc-log"><strong><?= e($report['referee_name'] ?? 'Relatorio') ?></strong><span><?= e($report['summary'] ?? '') ?></span></p><?php endforeach; ?><?php if (!$reports): ?><p class="text-muted mb-0">Nenhum relatorio registrado.</p><?php endif; ?></article>
                    <article class="sc-panel"><div class="sc-panel-head"><h3>Operacoes</h3></div><p class="sc-log"><strong>Sets registrados</strong><span><?= count($sets) ?> registros</span></p><p class="sc-log"><strong>Reagendamentos</strong><span><?= count($reschedules) ?> registros</span></p><p class="sc-log"><strong>Partidas concluidas</strong><span><?= (int) ($counts['completed_matches'] ?? 0) ?> registros</span></p></article>
                </div>
            </section>
        </div>
    </section>
<?php endif; ?>
