<?php
$match = $matchData['match'];
$events = $matchData['events'] ?? [];
$sets = $matchData['sets'] ?? [];
$lineups = $matchData['lineups'] ?? [];
$reports = $matchData['reports'] ?? [];
$athletes = $matchData['athletes'] ?? [];
$homeName = (string) (($match['home_team'] ?? '') ?: ($match['home_athlete'] ?? 'A definir'));
$awayName = (string) (($match['away_team'] ?? '') ?: ($match['away_athlete'] ?? 'A definir'));
$homeScore = is_numeric($match['home_score'] ?? null) ? (int) $match['home_score'] : 0;
$awayScore = is_numeric($match['away_score'] ?? null) ? (int) $match['away_score'] : 0;
?>

<section class="match-manage-hero">
    <div class="container">
        <a class="btn btn-sm btn-light mb-3" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar') ?>">Voltar ao campeonato</a>
        <div class="match-manage-score">
            <div class="match-team-large"><span>Mandante</span><strong><?= e($homeName) ?></strong></div>
            <div class="score-editor">
                <b><?= $homeScore ?></b><span>x</span><b><?= $awayScore ?></b>
                <small><?= e($match['status'] ?? 'pendente') ?></small>
            </div>
            <div class="match-team-large text-end"><span>Visitante</span><strong><?= e($awayName) ?></strong></div>
        </div>
        <div class="manage-meta mt-3">
            <span><i class="fa-solid fa-calendar"></i><?= !empty($match['match_date']) ? e(date('d/m/Y', strtotime($match['match_date']))) : 'Data a definir' ?></span>
            <span><i class="fa-solid fa-clock"></i><?= !empty($match['match_time']) ? e(substr((string) $match['match_time'], 0, 5)) : 'Horario a definir' ?></span>
            <span><i class="fa-solid fa-location-dot"></i><?= e($match['venue'] ?? 'Local a definir') ?><?= !empty($match['court_or_field']) ? ' - ' . e($match['court_or_field']) : '' ?></span>
            <?php if (!empty($match['referee'])): ?><span><i class="fa-solid fa-user-check"></i><?= e($match['referee']) ?></span><?php endif; ?>
        </div>
    </div>
</section>

<section class="container py-4">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="panel match-tool-panel">
                <h2>Confirmar resultado</h2>
                <form class="stack-form">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Mandante</label><input class="form-control" type="number" value="<?= $homeScore ?>" disabled></div>
                        <div class="col-6"><label class="form-label">Visitante</label><input class="form-control" type="number" value="<?= $awayScore ?>" disabled></div>
                    </div>
                    <textarea class="form-control" rows="3" disabled placeholder="Observacao da partida"><?= e($match['notes'] ?? '') ?></textarea>
                    <button class="btn btn-primary" type="button" disabled>Confirmar resultado</button>
                </form>
                <p class="text-muted small mt-2 mb-0">A interface foi restaurada em modo seguro; nenhuma gravacao e feita sem actions de persistencia definidas.</p>
            </div>

            <div class="panel match-tool-panel mt-3">
                <h2>Adicionar evento</h2>
                <form class="stack-form">
                    <select class="form-select" disabled>
                        <option>Gol</option>
                        <option>Cartao amarelo</option>
                        <option>Cartao vermelho</option>
                    </select>
                    <select class="form-select" disabled>
                        <option>Selecionar atleta</option>
                        <?php foreach ($athletes as $athlete): ?><option><?= e($athlete['name'] ?? '') ?></option><?php endforeach; ?>
                    </select>
                    <input class="form-control" type="number" disabled placeholder="Minuto">
                    <textarea class="form-control" rows="2" disabled placeholder="Descricao"></textarea>
                    <button class="btn btn-outline-primary" type="button" disabled>Adicionar evento</button>
                </form>
            </div>

            <div class="panel match-tool-panel mt-3">
                <h2>Adicionar set</h2>
                <form class="stack-form">
                    <div class="row g-2">
                        <div class="col-4"><input class="form-control" type="number" disabled placeholder="Set"></div>
                        <div class="col-4"><input class="form-control" type="number" disabled placeholder="Casa"></div>
                        <div class="col-4"><input class="form-control" type="number" disabled placeholder="Fora"></div>
                    </div>
                    <button class="btn btn-outline-primary" type="button" disabled>Adicionar set</button>
                </form>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="panel">
                <div class="section-heading"><h2>Linha do tempo</h2><span><?= count($events) ?> eventos</span></div>
                <div class="timeline match-timeline">
                    <?php foreach ($events as $event): ?>
                        <article class="timeline-item">
                            <span class="timeline-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <div>
                                <strong><?= e($event['event_type'] ?? 'Evento') ?></strong>
                                <p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p>
                                <?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?>
                                <div class="event-actions"><button class="btn btn-sm btn-outline-secondary" disabled>Editar</button><button class="btn btn-sm btn-outline-danger" disabled>Excluir</button></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$events): ?><p class="text-muted mb-0">Nenhum evento registrado nesta partida.</p><?php endif; ?>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <div class="panel h-100">
                        <h2>Escalacao</h2>
                        <?php foreach ($lineups as $lineup): ?>
                            <p class="note"><strong><?= e($lineup['athlete_name'] ?? '') ?></strong><br><?= e($lineup['team_name'] ?? '') ?> <?= !empty($lineup['is_captain']) ? '- capitao' : '' ?></p>
                        <?php endforeach; ?>
                        <?php if (!$lineups): ?><p class="text-muted mb-0">Nenhuma escalacao registrada.</p><?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="panel h-100">
                        <h2>Sets e relatorios</h2>
                        <?php foreach ($sets as $set): ?><p class="note">Set <?= (int) $set['set_number'] ?>: <?= (int) $set['home_score'] ?> x <?= (int) $set['away_score'] ?></p><?php endforeach; ?>
                        <?php foreach ($reports as $report): ?><p class="note"><strong><?= e($report['referee_name'] ?? 'Relatorio') ?></strong><br><?= e($report['summary'] ?? '') ?></p><?php endforeach; ?>
                        <?php if (!$sets && !$reports): ?><p class="text-muted mb-0">Nenhum set ou relatorio registrado.</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
