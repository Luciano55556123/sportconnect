<?php
$competitionData = $competitionData ?? [];
$competitionCounts = $competitionCounts ?? [];
$teams = $competitionData['teams'] ?? [];
$athletes = $competitionData['athletes'] ?? [];
$matches = $competitionData['matches'] ?? [];
$standings = $competitionData['standings'] ?? [];
$events = $competitionData['events'] ?? [];
$statistics = $competitionData['statistics'] ?? [];
$sets = $competitionData['sets'] ?? [];
$reports = $competitionData['reports'] ?? [];
$reschedules = $competitionData['reschedules'] ?? [];
$totalCompetitionRows = array_sum(array_map('intval', $competitionCounts));
$teamLabel = static fn(array $row): string => (string) (($row['team_name'] ?? '') ?: ($row['athlete_name'] ?? 'Competidor'));
$matchTitle = static function (array $row): string {
    $home = (string) (($row['home_team'] ?? '') ?: ($row['home_athlete'] ?? 'A definir'));
    $away = (string) (($row['away_team'] ?? '') ?: ($row['away_athlete'] ?? 'A definir'));
    return $home . ' x ' . $away;
};
?>

<?php if ($totalCompetitionRows > 0): ?>
    <section class="competition-section mt-4">
        <div class="section-heading">
            <div>
                <h2>Dados do campeonato</h2>
                <span>Equipes, partidas, classificacao e eventos carregados do banco.</span>
            </div>
        </div>

        <div class="competition-summary">
            <div><strong><?= (int) ($competitionCounts['teams'] ?? 0) ?></strong><span>Equipes</span></div>
            <div><strong><?= (int) ($competitionCounts['athletes'] ?? 0) ?></strong><span>Atletas</span></div>
            <div><strong><?= (int) ($competitionCounts['matches'] ?? 0) ?></strong><span>Partidas</span></div>
            <div><strong><?= (int) ($competitionCounts['events'] ?? 0) ?></strong><span>Eventos</span></div>
        </div>

        <?php if ($standings): ?>
            <div class="panel mt-3">
                <h3>Classificacao</h3>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Grupo</th><th>Equipe/Atleta</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th><th>Pts</th></tr></thead>
                        <tbody>
                            <?php foreach ($standings as $row): ?>
                                <tr>
                                    <td><?= e($row['group_name'] ?? '-') ?></td>
                                    <td><?= e($teamLabel($row)) ?></td>
                                    <td><?= (int) ($row['played'] ?? 0) ?></td>
                                    <td><?= (int) ($row['wins'] ?? 0) ?></td>
                                    <td><?= (int) ($row['draws'] ?? 0) ?></td>
                                    <td><?= (int) ($row['losses'] ?? 0) ?></td>
                                    <td><?= (int) ($row['score_for'] ?? 0) ?></td>
                                    <td><?= (int) ($row['score_against'] ?? 0) ?></td>
                                    <td><?= (int) ($row['score_difference'] ?? 0) ?></td>
                                    <td><strong><?= (int) ($row['points'] ?? 0) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($matches): ?>
            <div class="panel mt-3">
                <h3>Partidas</h3>
                <div class="competition-list">
                    <?php foreach ($matches as $match): ?>
                        <article class="competition-row">
                            <div>
                                <strong><?= e($matchTitle($match)) ?></strong>
                                <span>
                                    <?= e($match['phase'] ?? '') ?>
                                    <?php if (!empty($match['group_name'])): ?> Grupo <?= e($match['group_name']) ?><?php endif; ?>
                                    <?php if (!empty($match['round_number'])): ?> Rodada <?= (int) $match['round_number'] ?><?php endif; ?>
                                </span>
                                <small>
                                    <?= !empty($match['match_date']) ? e(date('d/m/Y', strtotime($match['match_date']))) : 'Data a definir' ?>
                                    <?= !empty($match['match_time']) ? e(substr((string) $match['match_time'], 0, 5)) : '' ?>
                                    <?= !empty($match['venue']) ? ' - ' . e($match['venue']) : '' ?>
                                    <?= !empty($match['court_or_field']) ? ' - ' . e($match['court_or_field']) : '' ?>
                                </small>
                            </div>
                            <div class="score-pill">
                                <?= is_numeric($match['home_score'] ?? null) ? (int) $match['home_score'] : '-' ?>
                                x
                                <?= is_numeric($match['away_score'] ?? null) ? (int) $match['away_score'] : '-' ?>
                            </div>
                            <span class="badge text-bg-light"><?= e($match['status'] ?? 'pendente') ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($teams): ?>
            <div class="panel mt-3">
                <h3>Equipes</h3>
                <div class="team-grid">
                    <?php foreach ($teams as $team): ?>
                        <article>
                            <strong><?= e($team['name'] ?? '') ?></strong>
                            <span><?= e($team['city'] ?? '') ?></span>
                            <small><?= e($team['responsible_name'] ?? '') ?> <?= !empty($team['status']) ? '- ' . e($team['status']) : '' ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($events || $statistics): ?>
            <div class="row g-3 mt-1">
                <?php if ($events): ?>
                    <div class="col-lg-6">
                        <div class="panel h-100">
                            <h3>Eventos de partida</h3>
                            <?php foreach (array_slice($events, 0, 12) as $event): ?>
                                <p class="note mb-2">
                                    <strong><?= e($event['event_type'] ?? 'Evento') ?></strong>
                                    <span><?= (int) ($event['minute'] ?? 0) ?>' - <?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '(' . e($event['team_name']) . ')' : '' ?></span>
                                    <?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($statistics): ?>
                    <div class="col-lg-6">
                        <div class="panel h-100">
                            <h3>Estatisticas</h3>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Atleta</th><th>Equipe</th><th>Gols</th><th>Cartoes</th></tr></thead>
                                    <tbody>
                                        <?php foreach (array_slice($statistics, 0, 12) as $stat): ?>
                                            <tr>
                                                <td><?= e($stat['athlete_name'] ?? '') ?></td>
                                                <td><?= e($stat['team_name'] ?? '') ?></td>
                                                <td><?= (int) ($stat['goals'] ?? 0) ?></td>
                                                <td><?= (int) ($stat['yellow_cards'] ?? 0) ?>A / <?= (int) ($stat['red_cards'] ?? 0) ?>V</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($athletes || $sets || $reports || $reschedules): ?>
            <div class="panel mt-3">
                <h3>Outros dados de gestao</h3>
                <div class="competition-summary compact">
                    <div><strong><?= count($athletes) ?></strong><span>Atletas listados</span></div>
                    <div><strong><?= count($sets) ?></strong><span>Sets</span></div>
                    <div><strong><?= count($reports) ?></strong><span>Relatorios</span></div>
                    <div><strong><?= count($reschedules) ?></strong><span>Reagendamentos</span></div>
                </div>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
