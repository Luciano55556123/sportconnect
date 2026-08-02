<?php
$home = $match['home_team_name'] ?? $match['home_athlete_name'] ?? 'A definir';
$away = $match['away_team_name'] ?? $match['away_athlete_name'] ?? 'A definir';
$base = '/organizador/campeonatos/' . $championship['id'];
$returnTo = $base . '/partidas/' . $match['id'] . '/gerenciar';
$teamOptions = static function (array $teams, ?int $selected = null): void {
    foreach ($teams as $team) {
        $isSelected = $selected === (int) $team['id'] ? 'selected' : '';
        echo '<option value="' . (int) $team['id'] . '" ' . $isSelected . '>' . e($team['name']) . '</option>';
    }
};
$athleteOptions = static function (array $athletes, ?int $selected = null): void {
    foreach ($athletes as $athlete) {
        $isSelected = $selected === (int) $athlete['id'] ? 'selected' : '';
        echo '<option value="' . (int) $athlete['id'] . '" ' . $isSelected . '>' . e($athlete['name']) . '</option>';
    }
};
?>
<section class="match-control-hero">
    <div class="container">
        <a class="btn btn-sm btn-light mb-3" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar#jogos') ?>"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
        <div class="scoreboard">
            <div class="score-side"><div class="team-shield large"><?= e(mb_strtoupper(mb_substr($home, 0, 2))) ?></div><h1><?= e($home) ?></h1></div>
            <div class="score-center"><strong><?= $match['home_score'] !== null ? (int) $match['home_score'] : '-' ?> x <?= $match['away_score'] !== null ? (int) $match['away_score'] : '-' ?></strong><span><?= e($match['status']) ?></span></div>
            <div class="score-side"><div class="team-shield large"><?= e(mb_strtoupper(mb_substr($away, 0, 2))) ?></div><h1><?= e($away) ?></h1></div>
        </div>
        <div class="match-meta">
            <span><?= e($match['phase']) ?></span><span>Rodada <?= e((string) ($match['round_number'] ?? '-')) ?></span><span><?= e($match['match_date'] ?? 'Data indefinida') ?></span><span><?= e($match['match_time'] ?? '') ?></span><span><?= e($match['venue'] ?? 'Local indefinido') ?></span>
        </div>
    </div>
</section>

<section class="container py-4">
    <div class="competition-tabs">
        <ul class="nav nav-pills flex-nowrap">
            <?php foreach (['resumo' => 'Resumo', 'placar' => 'Placar', 'gols' => 'Gols e eventos', 'cartoes' => 'Cartoes', 'sets' => 'Sets', 'sumula' => 'Sumula', 'observacoes' => 'Observacoes'] as $key => $label): ?>
                <li class="nav-item"><button class="nav-link <?= $key === 'resumo' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#<?= $key ?>" type="button"><?= e($label) ?></button></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="resumo">
            <div class="row g-3">
                <div class="col-lg-7"><div class="panel h-100"><h2>Linha do tempo</h2><?php foreach ($events as $event): ?><div class="timeline-item"><div class="d-flex justify-content-between gap-2"><div><strong><?= e(($event['minute'] ?? '') . (!empty($event['additional_time']) ? '+' . $event['additional_time'] : '') . "'") ?></strong> <?= e($event['athlete_name'] ?? 'Evento') ?> - <?= e(str_replace('_', ' ', $event['event_type'])) ?> - <?= e($event['team_name'] ?? '') ?><br><small><?= e($event['description'] ?? '') ?></small></div><div class="d-flex gap-1"><button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editEvent<?= (int) $event['id'] ?>" type="button"><i class="fa-solid fa-pen"></i></button><form method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/eventos/' . $event['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir evento e recalcular estatisticas?')"><?= csrf_field() ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>"><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form></div></div><form id="editEvent<?= (int) $event['id'] ?>" class="collapse row g-2 mt-2" method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/eventos') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $event['id'] ?>"><input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>"><input type="hidden" name="return_to" value="<?= e($returnTo) ?>"><div class="col-md-3"><select class="form-select form-select-sm" name="event_type"><?php foreach (['gol','penalti_convertido','gol_contra','cartao_amarelo','cartao_vermelho','observacao'] as $type): ?><option value="<?= e($type) ?>" <?= $event['event_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><select class="form-select form-select-sm" name="team_id"><option value="">Equipe</option><?php $teamOptions($teams, $event['team_id'] ? (int) $event['team_id'] : null); ?></select></div><div class="col-md-3"><select class="form-select form-select-sm" name="athlete_id"><option value="">Atleta</option><?php $athleteOptions($athletes, $event['athlete_id'] ? (int) $event['athlete_id'] : null); ?></select></div><div class="col-md-1"><input class="form-control form-control-sm" type="number" name="minute" value="<?= e((string) ($event['minute'] ?? '')) ?>"></div><div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Salvar</button></div><div class="col-12"><input class="form-control form-control-sm" name="description" value="<?= e($event['description'] ?? '') ?>"></div></form></div><?php endforeach; ?><?php if (!$events): ?><p class="text-muted">Nenhum evento registrado.</p><?php endif; ?></div></div>
                <div class="col-lg-5"><div class="panel h-100"><h2>Resumo disciplinar</h2><?php $yellow = count(array_filter($events, fn ($e) => $e['event_type'] === 'cartao_amarelo')); $red = count(array_filter($events, fn ($e) => $e['event_type'] === 'cartao_vermelho')); ?><div class="metric-grid"><div><strong><?= $yellow ?></strong><span>Amarelos</span></div><div><strong><?= $red ?></strong><span>Vermelhos</span></div></div><hr><h2>Sets</h2><?php foreach ($sets as $set): ?><div class="timeline-item">Set <?= (int) $set['set_number'] ?>: <?= (int) $set['home_score'] ?> x <?= (int) $set['away_score'] ?></div><?php endforeach; ?><?php if (!$sets): ?><p class="text-muted">Nenhum set registrado.</p><?php endif; ?></div></div>
            </div>
        </div>

        <div class="tab-pane fade" id="placar">
            <form class="panel row g-3" method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/jogos/' . $match['id'] . '/resultado') ?>" <?= $match['status'] === 'finalizada' ? 'data-confirm="Alterar resultado de partida finalizada?"' : '' ?>>
                <?= csrf_field() ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <div class="col-md-3"><label class="form-label"><?= e($home) ?></label><input class="form-control score-input" type="number" name="home_score" value="<?= e((string) ($match['home_score'] ?? '0')) ?>"></div>
                <div class="col-md-3"><label class="form-label"><?= e($away) ?></label><input class="form-control score-input" type="number" name="away_score" value="<?= e((string) ($match['away_score'] ?? '0')) ?>"></div>
                <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="finalizada">Finalizada</option><option value="em_andamento">Em andamento</option><option value="agendada">Agendada</option><option value="adiada">Adiada</option><option value="cancelada">Cancelada</option></select></div>
                <div class="col-md-3"><label class="form-label">Vencedor calculado</label><input class="form-control" readonly value="<?= e($match['winner_team_name'] ?? $match['winner_athlete_name'] ?? 'Empate ou indefinido') ?>"></div>
                <div class="col-12"><label class="form-label">Observacoes</label><textarea class="form-control" name="notes" rows="3"><?= e($match['notes'] ?? '') ?></textarea></div>
                <div class="col-12"><button class="btn btn-primary">Salvar resultado</button></div>
            </form>
        </div>

        <div class="tab-pane fade" id="gols">
            <form class="panel row g-3" method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/eventos') ?>">
                <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>"><input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <div class="col-md-2"><select class="form-select" name="event_type"><option value="gol">Gol normal</option><option value="penalti_convertido">Penalti</option><option value="gol_contra">Gol contra</option></select></div>
                <div class="col-md-3"><select class="form-select" name="team_id"><option value="">Equipe</option><?php $teamOptions($teams); ?></select></div>
                <div class="col-md-3"><select class="form-select" name="athlete_id"><option value="">Atleta</option><?php $athleteOptions($athletes); ?></select></div>
                <div class="col-md-1"><input class="form-control" type="number" name="minute" placeholder="Min"></div>
                <div class="col-md-1"><input class="form-control" type="number" name="additional_time" placeholder="+"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Registrar gol</button></div>
                <div class="col-12"><input class="form-control" name="description" placeholder="Descricao do lance"></div>
            </form>
        </div>

        <div class="tab-pane fade" id="cartoes">
            <form class="panel row g-3" method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/eventos') ?>">
                <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>"><input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <div class="col-md-2"><select class="form-select" name="event_type"><option value="cartao_amarelo">Amarelo</option><option value="cartao_amarelo">Segundo amarelo</option><option value="cartao_vermelho">Vermelho</option></select></div>
                <div class="col-md-3"><select class="form-select" name="team_id"><option value="">Equipe</option><?php $teamOptions($teams); ?></select></div>
                <div class="col-md-3"><select class="form-select" name="athlete_id"><option value="">Atleta</option><?php $athleteOptions($athletes); ?></select></div>
                <div class="col-md-1"><input class="form-control" type="number" name="minute" placeholder="Min"></div>
                <div class="col-md-3"><input class="form-control" name="description" placeholder="Motivo/observacao"></div>
                <div class="col-12"><button class="btn btn-primary">Registrar cartao</button></div>
            </form>
        </div>

        <div class="tab-pane fade" id="sets">
            <form class="panel row g-3" method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/sets') ?>">
                <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>"><input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <div class="col-md-3"><input class="form-control" type="number" name="set_number" placeholder="Set" required></div>
                <div class="col-md-3"><input class="form-control" type="number" name="home_score" placeholder="<?= e($home) ?>" required></div>
                <div class="col-md-3"><input class="form-control" type="number" name="away_score" placeholder="<?= e($away) ?>" required></div>
                <div class="col-md-3"><button class="btn btn-primary w-100">Salvar set</button></div>
            </form>
        </div>

        <div class="tab-pane fade" id="sumula">
            <?php $finalized = !empty($matchReport['finalized_at']); ?>
            <form class="panel row g-3" method="post" action="<?= url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar/jogos/' . $match['id'] . '/sumula') ?>">
                <?= csrf_field() ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <div class="col-md-4"><label class="form-label">Arbitro</label><input class="form-control" name="referee_name" value="<?= e($matchReport['referee_name'] ?? '') ?>" <?= $finalized && empty($isAdmin) ? 'readonly' : '' ?>></div>
                <div class="col-md-4"><label class="form-label">Confirmacao organizador</label><select class="form-select" name="organizer_confirmation" <?= $finalized && empty($isAdmin) ? 'disabled' : '' ?>><option value="0">Pendente</option><option value="1" <?= !empty($matchReport['organizer_confirmation']) ? 'selected' : '' ?>>Confirmada</option></select></div>
                <div class="col-md-4"><label class="form-label">Status da sumula</label><input class="form-control" readonly value="<?= $finalized ? 'Finalizada em ' . e($matchReport['finalized_at']) : 'Rascunho' ?>"></div>
                <div class="col-md-6"><label class="form-label">Resumo tecnico</label><textarea class="form-control" name="summary" rows="5" <?= $finalized && empty($isAdmin) ? 'readonly' : '' ?>><?= e($matchReport['summary'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label class="form-label">Ocorrencias</label><textarea class="form-control" name="incidents" rows="5" <?= $finalized && empty($isAdmin) ? 'readonly' : '' ?>><?= e($matchReport['incidents'] ?? '') ?></textarea></div>
                <div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="home_team_confirmation" value="1" id="homeConfirm" <?= !empty($matchReport['home_team_confirmation']) ? 'checked' : '' ?> <?= $finalized && empty($isAdmin) ? 'disabled' : '' ?>><label class="form-check-label" for="homeConfirm">Responsavel mandante confirmou</label></div></div>
                <div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="away_team_confirmation" value="1" id="awayConfirm" <?= !empty($matchReport['away_team_confirmation']) ? 'checked' : '' ?> <?= $finalized && empty($isAdmin) ? 'disabled' : '' ?>><label class="form-check-label" for="awayConfirm">Responsavel visitante confirmou</label></div></div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <?php if (!$finalized || !empty($isAdmin)): ?>
                        <button class="btn btn-primary">Salvar rascunho</button>
                        <button class="btn btn-outline-success" name="finalize" value="1" data-confirm-submit="Finalizar a sumula desta partida?">Finalizar sumula</button>
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" type="button" data-print-page>Imprimir</button>
                </div>
            </form>
        </div>

        <div class="tab-pane fade" id="observacoes"><div class="panel"><h2>Observacoes da partida</h2><p><?= nl2br(e($match['notes'] ?? 'Nenhuma observacao registrada.')) ?></p></div></div>
    </div>
</section>
