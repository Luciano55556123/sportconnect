<?php
$competitionData = $competitionData ?? [];
$competitionCounts = $competitionCounts ?? [];
$championship = $championship ?? [];
$isOrganizerManage = !empty($isOrganizerManage);
$teams = $competitionData['teams'] ?? [];
$athletes = $competitionData['athletes'] ?? [];
$matches = $competitionData['matches'] ?? [];
$standings = $competitionData['standings'] ?? [];
$events = $competitionData['events'] ?? [];
$statistics = $competitionData['statistics'] ?? [];
$sets = $competitionData['sets'] ?? [];
$reports = $competitionData['reports'] ?? [];
$reschedules = $competitionData['reschedules'] ?? [];
$summary = $competitionData['summary'] ?? ['progress' => 0, 'counts' => $competitionCounts];
$counts = $summary['counts'] ?? $competitionCounts;
$totalCompetitionRows = array_sum(array_map('intval', $competitionCounts));
$teamName = static fn(array $row): string => (string) (($row['team_name'] ?? '') ?: ($row['athlete_name'] ?? 'Competidor'));
$matchSide = static fn(array $row, string $side): string => (string) (($row[$side . '_team'] ?? '') ?: ($row[$side . '_athlete'] ?? 'A definir'));
$matchScore = static fn(array $row, string $side): string => is_numeric($row[$side . '_score'] ?? null) ? (string) (int) $row[$side . '_score'] : '-';
$topScorers = array_values(array_filter($statistics, static fn(array $row): bool => (int) ($row['goals'] ?? 0) > 0));
$cards = array_values(array_filter($statistics, static fn(array $row): bool => ((int) ($row['yellow_cards'] ?? 0) + (int) ($row['red_cards'] ?? 0)) > 0));
$groupedRounds = [];
foreach ($matches as $match) {
    $round = (string) ($match['round_number'] ?? 'Sem rodada');
    $groupedRounds[$round][] = $match;
}
?>

<?php if ($totalCompetitionRows > 0): ?>
    <section class="competition-board mt-4">
        <div class="competition-hero">
            <div>
                <span class="badge text-bg-primary"><?= e($championship['status'] ?? 'ativo') ?></span>
                <h2><?= $isOrganizerManage ? 'Gerenciamento do campeonato' : 'Andamento do campeonato' ?></h2>
                <p><?= e($championship['modality'] ?? 'misto') ?> - <?= e($championship['sport_name'] ?? '') ?> - <?= e($championship['city'] ?? '') ?></p>
            </div>
            <div class="competition-progress">
                <strong><?= (int) ($summary['progress'] ?? 0) ?>%</strong>
                <span>concluido</span>
            </div>
        </div>

        <div class="progress competition-progress-bar" role="progressbar" aria-valuenow="<?= (int) ($summary['progress'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: <?= (int) ($summary['progress'] ?? 0) ?>%"></div>
        </div>

        <div class="competition-kpis">
            <div><i class="fa-solid fa-people-group"></i><strong><?= (int) ($counts['teams'] ?? 0) ?></strong><span>Equipes</span></div>
            <div><i class="fa-solid fa-person-running"></i><strong><?= (int) ($counts['athletes'] ?? 0) ?></strong><span>Atletas</span></div>
            <div><i class="fa-solid fa-calendar-days"></i><strong><?= (int) ($counts['matches'] ?? 0) ?></strong><span>Partidas</span></div>
            <div><i class="fa-solid fa-circle-check"></i><strong><?= (int) ($counts['completed_matches'] ?? 0) ?></strong><span>Concluidas</span></div>
            <div><i class="fa-solid fa-futbol"></i><strong><?= (int) ($counts['goals'] ?? 0) ?></strong><span>Gols</span></div>
            <div><i class="fa-solid fa-id-card"></i><strong><?= (int) ($championship['registrations_count'] ?? 0) ?></strong><span>Inscricoes</span></div>
        </div>

        <ul class="nav nav-pills competition-tabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#comp-overview" type="button">Visao geral</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-teams" type="button">Equipes</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-matches" type="button">Partidas</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-standings" type="button">Classificacao</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-bracket" type="button">Rodadas</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-stats" type="button">Estatisticas</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-events" type="button">Eventos</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#comp-history" type="button">Historico</button></li>
        </ul>

        <div class="tab-content competition-tab-content">
            <div class="tab-pane fade show active" id="comp-overview">
                <div class="row g-3">
                    <div class="col-xl-7">
                        <div class="panel h-100">
                            <div class="section-heading"><h3>Proximas e ultimas partidas</h3><span><?= count($matches) ?> jogos</span></div>
                            <div class="match-card-list">
                                <?php foreach (array_slice($matches, 0, 6) as $match): ?>
                                    <?php require BASE_PATH . '/app/Views/championships/_match_card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="panel h-100">
                            <div class="section-heading"><h3>Destaques</h3><span>artilharia e disciplina</span></div>
                            <div class="highlight-list">
                                <?php foreach (array_slice($topScorers, 0, 5) as $stat): ?>
                                    <div class="highlight-row">
                                        <div><strong><?= e($stat['athlete_name'] ?? 'Atleta') ?></strong><span><?= e($stat['team_name'] ?? '') ?></span></div>
                                        <b><?= (int) ($stat['goals'] ?? 0) ?> gols</b>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (!$topScorers): ?><p class="text-muted mb-0">Nenhum gol registrado.</p><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-teams">
                <div class="team-card-grid">
                    <?php foreach ($teams as $team): ?>
                        <article class="team-card-pro">
                            <div class="team-shield">
                                <?php if (!empty($team['shield'])): ?><img src="<?= url($team['shield']) ?>" alt=""><?php else: ?><i class="fa-solid fa-shield-halved"></i><?php endif; ?>
                            </div>
                            <div>
                                <strong><?= e($team['name'] ?? '') ?></strong>
                                <span><?= e($team['city'] ?? '') ?></span>
                                <small><?= (int) ($team['athletes_count'] ?? 0) ?> atletas - <?= e($team['status'] ?? '') ?></small>
                            </div>
                            <a class="btn btn-sm btn-outline-primary" href="#comp-stats" data-bs-toggle="pill" data-bs-target="#comp-stats">Ver detalhes</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-matches">
                <div class="match-card-list">
                    <?php foreach ($matches as $match): ?>
                        <?php require BASE_PATH . '/app/Views/championships/_match_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-standings">
                <div class="panel">
                    <div class="table-responsive">
                        <table class="table standings-table align-middle">
                            <thead><tr><th>Pos</th><th>Equipe</th><th>Pts</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th></tr></thead>
                            <tbody>
                                <?php foreach ($standings as $index => $row): ?>
                                    <tr class="<?= $index < 2 ? 'zone-qualified' : '' ?>">
                                        <td><strong><?= $index + 1 ?></strong></td>
                                        <td><span class="table-team"><?= e($teamName($row)) ?></span><small><?= e($row['group_name'] ?? '') ?></small></td>
                                        <td><strong><?= (int) ($row['points'] ?? 0) ?></strong></td>
                                        <td><?= (int) ($row['played'] ?? 0) ?></td>
                                        <td><?= (int) ($row['wins'] ?? 0) ?></td>
                                        <td><?= (int) ($row['draws'] ?? 0) ?></td>
                                        <td><?= (int) ($row['losses'] ?? 0) ?></td>
                                        <td><?= (int) ($row['score_for'] ?? 0) ?></td>
                                        <td><?= (int) ($row['score_against'] ?? 0) ?></td>
                                        <td><?= (int) ($row['score_difference'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-bracket">
                <div class="rounds-board">
                    <?php foreach ($groupedRounds as $round => $roundMatches): ?>
                        <section class="round-column">
                            <h3>Rodada <?= e($round) ?></h3>
                            <?php foreach ($roundMatches as $match): ?>
                                <div class="round-match">
                                    <span><?= e($matchSide($match, 'home')) ?></span>
                                    <strong><?= $matchScore($match, 'home') ?> x <?= $matchScore($match, 'away') ?></strong>
                                    <span><?= e($matchSide($match, 'away')) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-stats">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="panel h-100">
                            <h3>Artilharia</h3>
                            <?php foreach (array_slice($topScorers, 0, 10) as $stat): ?>
                                <div class="highlight-row"><div><strong><?= e($stat['athlete_name'] ?? '') ?></strong><span><?= e($stat['team_name'] ?? '') ?></span></div><b><?= (int) ($stat['goals'] ?? 0) ?></b></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="panel h-100">
                            <h3>Cartoes</h3>
                            <?php foreach (array_slice($cards, 0, 10) as $stat): ?>
                                <div class="highlight-row"><div><strong><?= e($stat['athlete_name'] ?? '') ?></strong><span><?= e($stat['team_name'] ?? '') ?></span></div><b><?= (int) ($stat['yellow_cards'] ?? 0) ?>A / <?= (int) ($stat['red_cards'] ?? 0) ?>V</b></div>
                            <?php endforeach; ?>
                            <?php if (!$cards): ?><p class="text-muted mb-0">Nenhum cartao registrado.</p><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-events">
                <div class="timeline">
                    <?php foreach ($events as $event): ?>
                        <article class="timeline-item">
                            <span class="timeline-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <div>
                                <strong><?= e($event['event_type'] ?? 'Evento') ?></strong>
                                <p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p>
                                <?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="comp-history">
                <div class="row g-3">
                    <div class="col-lg-6"><div class="panel h-100"><h3>Relatorios</h3><?php foreach ($reports as $report): ?><p class="note"><strong><?= e($report['referee_name'] ?? 'Relatorio') ?></strong><br><?= e($report['summary'] ?? '') ?></p><?php endforeach; ?><?php if (!$reports): ?><p class="text-muted mb-0">Nenhum relatorio registrado.</p><?php endif; ?></div></div>
                    <div class="col-lg-6"><div class="panel h-100"><h3>Reagendamentos e sets</h3><p class="text-muted"><?= count($reschedules) ?> reagendamentos registrados.</p><p class="text-muted mb-0"><?= count($sets) ?> sets registrados.</p></div></div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
