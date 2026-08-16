<?php
$match = $matchData['match'];
$events = $matchData['events'] ?? [];
$sets = $matchData['sets'] ?? [];
$lineups = $matchData['lineups'] ?? [];
$reports = $matchData['reports'] ?? [];
$homeName = (string) (($match['home_team'] ?? '') ?: ($match['home_athlete'] ?? 'A definir'));
$awayName = (string) (($match['away_team'] ?? '') ?: ($match['away_athlete'] ?? 'A definir'));
$homeScore = is_numeric($match['home_score'] ?? null) ? (int) $match['home_score'] : 0;
$awayScore = is_numeric($match['away_score'] ?? null) ? (int) $match['away_score'] : 0;
?>

<section class="sc-match-hero">
    <div class="container">
        <a class="sc-back-link" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar') ?>"><i class="fa-solid fa-arrow-left"></i> Voltar ao campeonato</a>
        <div class="sc-match-hero-card">
            <div class="sc-match-team-large">
                <span>Mandante</span>
                <strong><?= e($homeName) ?></strong>
            </div>
            <div class="sc-match-score-large" aria-label="Placar <?= e($homeName) ?> <?= $homeScore ?> x <?= $awayScore ?> <?= e($awayName) ?>">
                <b><?= $homeScore ?></b>
                <span>x</span>
                <b><?= $awayScore ?></b>
                <small><?= e($match['status'] ?? 'pendente') ?></small>
            </div>
            <div class="sc-match-team-large text-end">
                <span>Visitante</span>
                <strong><?= e($awayName) ?></strong>
            </div>
        </div>
        <div class="sc-match-meta">
            <span><i class="fa-solid fa-calendar"></i><?= !empty($match['match_date']) ? e(date('d/m/Y', strtotime($match['match_date']))) : 'Data a definir' ?></span>
            <span><i class="fa-solid fa-clock"></i><?= !empty($match['match_time']) ? e(substr((string) $match['match_time'], 0, 5)) : 'Horario a definir' ?></span>
            <span><i class="fa-solid fa-location-dot"></i><?= e($match['venue'] ?? 'Local a definir') ?><?= !empty($match['court_or_field']) ? ' - ' . e($match['court_or_field']) : '' ?></span>
            <?php if (!empty($match['referee'])): ?><span><i class="fa-solid fa-user-check"></i><?= e($match['referee']) ?></span><?php endif; ?>
        </div>
    </div>
</section>

<section class="container py-4">
    <div class="sc-view-mode-alert" role="status">
        <i class="fa-solid fa-eye"></i>
        <div><strong>Recurso em modo de visualizacao</strong><span>As actions antigas de gravacao nao estao conectadas nesta versao; os dados reais continuam preservados.</span></div>
    </div>

    <div class="sc-match-layout">
        <aside class="sc-action-panel">
            <h2>Acoes rapidas</h2>
            <div class="sc-action-grid">
                <button type="button" disabled><i class="fa-solid fa-futbol"></i><span>Adicionar gol</span></button>
                <button type="button" disabled><i class="fa-solid fa-square"></i><span>Cartao amarelo</span></button>
                <button type="button" disabled><i class="fa-solid fa-square-full"></i><span>Cartao vermelho</span></button>
                <button type="button" disabled><i class="fa-solid fa-rotate"></i><span>Atualizar placar</span></button>
                <button type="button" disabled><i class="fa-solid fa-flag-checkered"></i><span>Finalizar partida</span></button>
                <button type="button" disabled><i class="fa-solid fa-note-sticky"></i><span>Adicionar observacao</span></button>
            </div>

            <div class="sc-panel mt-3">
                <h3>Resumo tecnico</h3>
                <p class="sc-log"><strong>Fase</strong><span><?= e($match['phase'] ?? 'Nao informada') ?></span></p>
                <p class="sc-log"><strong>Rodada</strong><span><?= e((string) ($match['round_number'] ?? 'Nao informada')) ?></span></p>
                <p class="sc-log"><strong>Observacoes</strong><span><?= e($match['notes'] ?? 'Nenhuma observacao') ?></span></p>
            </div>
        </aside>

        <main class="sc-match-main">
            <section class="sc-panel">
                <div class="sc-panel-head"><div><span class="sc-eyebrow">Timeline</span><h2>Linha do tempo da partida</h2></div><span><?= count($events) ?> eventos</span></div>
                <div class="sc-timeline">
                    <?php foreach ($events as $event): ?>
                        <?php $eventType = (string) ($event['event_type'] ?? 'Evento'); $eventIcon = str_contains($eventType, 'cartao') ? 'fa-solid fa-square' : (str_contains($eventType, 'gol') || str_contains($eventType, 'penalti') ? 'fa-solid fa-futbol' : 'fa-solid fa-circle-info'); ?>
                        <article class="sc-timeline-item">
                            <span class="sc-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <i class="<?= e($eventIcon) ?>" aria-hidden="true"></i>
                            <div>
                                <strong><?= e($eventType) ?></strong>
                                <p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p>
                                <?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?>
                                <div class="sc-admin-actions"><button class="btn btn-sm btn-outline-secondary" disabled>Editar</button><button class="btn btn-sm btn-outline-danger" disabled>Excluir</button></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$events): ?><p class="text-muted mb-0">Nenhum evento registrado nesta partida.</p><?php endif; ?>
                </div>
            </section>

            <div class="sc-dashboard-grid mt-3">
                <section class="sc-panel">
                    <div class="sc-panel-head"><h2>Escalacao</h2></div>
                    <?php foreach ($lineups as $lineup): ?>
                        <p class="sc-log"><strong><?= e($lineup['athlete_name'] ?? '') ?></strong><span><?= e($lineup['team_name'] ?? '') ?> <?= !empty($lineup['is_captain']) ? '- capitao' : '' ?></span></p>
                    <?php endforeach; ?>
                    <?php if (!$lineups): ?><p class="text-muted mb-0">Nenhuma escalacao registrada.</p><?php endif; ?>
                </section>

                <section class="sc-panel">
                    <div class="sc-panel-head"><h2>Sets e relatorios</h2></div>
                    <?php foreach ($sets as $set): ?><p class="sc-log"><strong>Set <?= (int) $set['set_number'] ?></strong><span><?= (int) $set['home_score'] ?> x <?= (int) $set['away_score'] ?></span></p><?php endforeach; ?>
                    <?php foreach ($reports as $report): ?><p class="sc-log"><strong><?= e($report['referee_name'] ?? 'Relatorio') ?></strong><span><?= e($report['summary'] ?? '') ?></span></p><?php endforeach; ?>
                    <?php if (!$sets && !$reports): ?><p class="text-muted mb-0">Nenhum set ou relatorio registrado.</p><?php endif; ?>
                </section>
            </div>
        </main>
    </div>
</section>
