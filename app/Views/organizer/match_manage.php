<?php
$actionBase = '/organizador/campeonatos/' . $championship['id'] . '/gerenciar';
$matchUrl = '/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $match['id'] . '/gerenciar';
$returnTo = url($matchUrl);
$homeName = (string) (($match['home_team_name'] ?? '') ?: ($match['home_athlete_name'] ?? 'A definir'));
$awayName = (string) (($match['away_team_name'] ?? '') ?: ($match['away_athlete_name'] ?? 'A definir'));
$homeScore = $match['home_score'] !== null ? (int) $match['home_score'] : 0;
$awayScore = $match['away_score'] !== null ? (int) $match['away_score'] : 0;
$statusOptions = ['agendada' => 'Agendada', 'em_andamento' => 'Em andamento', 'finalizada' => 'Finalizada'];
$eventOptions = ['ponto' => 'Ponto', 'saque' => 'Saque', 'bloqueio' => 'Bloqueio', 'substituicao' => 'Substituicao', 'cartao_amarelo' => 'Cartao amarelo', 'cartao_vermelho' => 'Cartao vermelho', 'observacao' => 'Observacao'];
?>

<section class="sc-match-hero">
    <div class="container">
        <a class="sc-back-link" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar#partidas') ?>"><i class="fa-solid fa-arrow-left"></i> Voltar ao campeonato</a>
        <div class="sc-match-hero-card">
            <div class="sc-match-team-large"><span>Mandante</span><strong><?= e($homeName) ?></strong></div>
            <div class="sc-match-score-large" aria-label="Placar <?= e($homeName) ?> <?= $homeScore ?> x <?= $awayScore ?> <?= e($awayName) ?>"><b><?= $homeScore ?></b><span>x</span><b><?= $awayScore ?></b><small><?= e($statusOptions[$match['status'] ?? ''] ?? ($match['status'] ?? 'agendada')) ?></small></div>
            <div class="sc-match-team-large text-end"><span>Visitante</span><strong><?= e($awayName) ?></strong></div>
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
    <div class="sc-match-layout">
        <aside class="sc-action-panel">
            <h2>Registrar resultado</h2>
            <form class="d-flex flex-wrap gap-2 mb-3" method="post" action="<?= url($actionBase . '/resultados/' . $match['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <input type="hidden" name="home_score" value="<?= e((string) ($match['home_score'] ?? 0)) ?>">
                <input type="hidden" name="away_score" value="<?= e((string) ($match['away_score'] ?? 0)) ?>">
                <input type="hidden" name="notes" value="<?= e($match['notes'] ?? '') ?>">
                <button class="btn btn-sm btn-outline-warning" name="status" value="em_andamento"><i class="fa-solid fa-play"></i> Iniciar partida</button>
                <button class="btn btn-sm btn-outline-success" name="status" value="finalizada"><i class="fa-solid fa-flag-checkered"></i> Finalizar partida</button>
            </form>
            <form class="row g-2" method="post" action="<?= url($actionBase . '/resultados/' . $match['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <div class="col-6"><label class="form-label">Mandante</label><input class="form-control" type="number" min="0" name="home_score" value="<?= e((string) ($match['home_score'] ?? 0)) ?>"></div>
                <div class="col-6"><label class="form-label">Visitante</label><input class="form-control" type="number" min="0" name="away_score" value="<?= e((string) ($match['away_score'] ?? 0)) ?>"></div>
                <div class="col-12"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($statusOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= ($match['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><label class="form-label">Observacoes</label><textarea class="form-control" name="notes" rows="3"><?= e($match['notes'] ?? '') ?></textarea></div>
                <div class="col-12"><button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk"></i> Salvar resultado</button></div>
            </form>
            <div class="sc-panel mt-3">
                <h3>Resumo tecnico</h3>
                <p class="sc-log"><strong>Fase</strong><span><?= e($match['phase'] ?? 'Nao informada') ?></span></p>
                <p class="sc-log"><strong>Rodada</strong><span><?= e((string) ($match['round_number'] ?? 'Nao informada')) ?></span></p>
                <p class="sc-log"><strong>Grupo</strong><span><?= e($match['group_name'] ?? 'Sem grupo') ?></span></p>
            </div>
        </aside>

        <main class="sc-match-main">
            <section class="sc-panel">
                <div class="sc-panel-head"><div><span class="sc-eyebrow">Partida</span><h2>Editar dados do jogo</h2></div></div>
                <form class="row g-3" method="post" action="<?= url($actionBase . '/jogos') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $match['id'] ?>">
                    <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                    <div class="col-md-3"><label class="form-label">Fase</label><input class="form-control" name="phase" value="<?= e($match['phase'] ?? '') ?>"></div>
                    <div class="col-md-2"><label class="form-label">Rodada</label><input class="form-control" type="number" name="round_number" value="<?= e((string) ($match['round_number'] ?? '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Grupo</label><input class="form-control" name="group_name" value="<?= e($match['group_name'] ?? '') ?>"></div>
                    <div class="col-md-2"><label class="form-label">Data</label><input class="form-control" type="date" name="match_date" value="<?= e($match['match_date'] ?? '') ?>"></div>
                    <div class="col-md-2"><label class="form-label">Hora</label><input class="form-control" type="time" name="match_time" value="<?= e(!empty($match['match_time']) ? substr((string) $match['match_time'], 0, 5) : '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Mandante</label><select class="form-select" name="home_team_id"><option value="">A definir</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>" <?= (int) ($match['home_team_id'] ?? 0) === (int) $team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Visitante</label><select class="form-select" name="away_team_id"><option value="">A definir</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>" <?= (int) ($match['away_team_id'] ?? 0) === (int) $team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($statusOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= ($match['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Local</label><input class="form-control" name="venue" value="<?= e($match['venue'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Quadra/campo</label><input class="form-control" name="court_or_field" value="<?= e($match['court_or_field'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Arbitragem</label><input class="form-control" name="referee" value="<?= e($match['referee'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Proxima partida</label><input class="form-control" type="number" name="next_match_id" value="<?= e((string) ($match['next_match_id'] ?? '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Posicao na proxima</label><select class="form-select" name="next_match_position"><option value="">Nao avancar</option><option value="home" <?= ($match['next_match_position'] ?? '') === 'home' ? 'selected' : '' ?>>Mandante</option><option value="away" <?= ($match['next_match_position'] ?? '') === 'away' ? 'selected' : '' ?>>Visitante</option></select></div>
                    <div class="col-12"><label class="form-label">Notas</label><textarea class="form-control" rows="3" name="notes"><?= e($match['notes'] ?? '') ?></textarea></div>
                    <div class="col-12"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar partida</button></div>
                </form>
            </section>

            <div class="sc-dashboard-grid mt-3">
                <section class="sc-panel">
                    <div class="sc-panel-head"><h2>Sets</h2></div>
                    <form class="row g-2 mb-3" method="post" action="<?= url($actionBase . '/sets') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <div class="col-4"><input class="form-control" type="number" min="1" name="set_number" placeholder="Set" required></div>
                        <div class="col-4"><input class="form-control" type="number" min="0" name="home_score" placeholder="Mand." required></div>
                        <div class="col-4"><input class="form-control" type="number" min="0" name="away_score" placeholder="Visit." required></div>
                        <div class="col-12"><button class="btn btn-outline-primary w-100">Salvar set</button></div>
                    </form>
                    <?php foreach ($sets as $set): ?><p class="sc-log"><strong>Set <?= (int) $set['set_number'] ?></strong><span><?= (int) $set['home_score'] ?> x <?= (int) $set['away_score'] ?></span></p><?php endforeach; ?>
                    <?php if (!$sets): ?><p class="text-muted mb-0">Nenhum set registrado.</p><?php endif; ?>
                </section>

                <section class="sc-panel">
                    <div class="sc-panel-head"><h2>Sumula</h2></div>
                    <form class="row g-2" method="post" action="<?= url($actionBase . '/partidas/' . $match['id'] . '/sumula') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <div class="col-12"><input class="form-control" name="referee_name" placeholder="Arbitro responsavel" value="<?= e($matchReport['referee_name'] ?? '') ?>"></div>
                        <div class="col-12"><textarea class="form-control" name="summary" rows="3" placeholder="Resumo"><?= e($matchReport['summary'] ?? '') ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="incidents" rows="2" placeholder="Incidentes"><?= e($matchReport['incidents'] ?? '') ?></textarea></div>
                        <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="organizer_confirmation" value="1" <?= !empty($matchReport['organizer_confirmation']) ? 'checked' : '' ?>><label class="form-check-label">Confirmacao do organizador</label></div>
                        <div class="col-12"><button class="btn btn-outline-primary">Salvar sumula</button></div>
                    </form>
                </section>
            </div>

            <section class="sc-panel mt-3">
                <div class="sc-panel-head"><div><span class="sc-eyebrow">Timeline</span><h2>Eventos da partida</h2></div><span><?= count($events) ?> eventos</span></div>
                <form class="row g-2 mb-3" method="post" action="<?= url($actionBase . '/eventos') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">
                    <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                    <div class="col-md-3"><select class="form-select" name="event_type"><?php foreach ($eventOptions as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><select class="form-select" name="team_id"><option value="">Equipe</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><select class="form-select" name="athlete_id"><option value="">Atleta</option><?php foreach ($athletes as $athlete): ?><option value="<?= (int) $athlete['id'] ?>"><?= e($athlete['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-1"><input class="form-control" type="number" min="0" name="minute" placeholder="Min"></div>
                    <div class="col-md-1"><input class="form-control" type="number" name="value" placeholder="Valor"></div>
                    <div class="col-md-1"><button class="btn btn-primary w-100"><i class="fa-solid fa-plus"></i></button></div>
                    <div class="col-12"><input class="form-control" name="description" placeholder="Descricao"></div>
                </form>
                <div class="sc-timeline">
                    <?php foreach ($events as $event): ?>
                        <?php $eventType = (string) ($event['event_type'] ?? 'observacao'); ?>
                        <article class="sc-timeline-item">
                            <span class="sc-time"><?= (int) ($event['minute'] ?? 0) ?>'</span>
                            <i class="<?= str_contains($eventType, 'cartao') ? 'fa-solid fa-square' : 'fa-solid fa-volleyball' ?>" aria-hidden="true"></i>
                            <div>
                                <strong><?= e($eventOptions[$eventType] ?? $eventType) ?></strong>
                                <p><?= e($event['athlete_name'] ?? '') ?> <?= !empty($event['team_name']) ? '- ' . e($event['team_name']) : '' ?></p>
                                <?php if (!empty($event['description'])): ?><small><?= e($event['description']) ?></small><?php endif; ?>
                                <form class="mt-2" method="post" action="<?= url($actionBase . '/eventos/' . $event['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir evento da partida?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$events): ?><p class="text-muted mb-0">Nenhum evento registrado nesta partida.</p><?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</section>
