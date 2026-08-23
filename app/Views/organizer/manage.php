<?php
$actionBase = '/organizador/campeonatos/' . $championship['id'] . '/gerenciar';
$currentUrl = url('/organizador/campeonatos/' . $championship['id'] . '/gerenciar');
$teamStatuses = ['aprovado' => 'Aprovada', 'pendente' => 'Pendente', 'rejeitado' => 'Rejeitada', 'cancelado' => 'Cancelada'];
$athleteStatuses = ['aprovado' => 'Aprovado', 'pendente' => 'Pendente', 'rejeitado' => 'Rejeitado', 'cancelado' => 'Cancelado'];
$matchStatuses = ['agendada' => 'Agendada', 'em_andamento' => 'Em andamento', 'finalizada' => 'Finalizada'];
$statusBadge = static fn (string $status): string => [
    'agendada' => 'text-bg-primary',
    'em_andamento' => 'text-bg-warning',
    'finalizada' => 'text-bg-success',
    'aprovado' => 'text-bg-success',
    'pendente' => 'text-bg-warning',
    'rejeitado' => 'text-bg-danger',
    'cancelado' => 'text-bg-secondary',
][$status] ?? 'text-bg-secondary';
$matchName = static function (array $match): string {
    return ($match['home_team_name'] ?? $match['home_athlete_name'] ?? 'A definir') . ' x ' . ($match['away_team_name'] ?? $match['away_athlete_name'] ?? 'A definir');
};
$athletesByTeam = [];
foreach ($athletes as $athlete) {
    $key = $athlete['team_name'] ?: 'Sem equipe';
    $athletesByTeam[$key][] = $athlete;
}
$matchesByRound = [];
foreach ($matches as $match) {
    $round = $match['round_number'] !== null ? 'Rodada ' . (int) $match['round_number'] : 'Sem rodada';
    $matchesByRound[$round][] = $match;
}
ksort($matchesByRound);
$standingsByGroup = [];
foreach ($standings as $row) {
    $standingsByGroup[$row['group_name'] ?: 'Geral'][] = $row;
}
?>

<section class="page-band">
    <div class="container">
        <h1>Gerenciar - <?= e($championship['name']) ?></h1>
        <p><?= e($championship['sport_name'] ?? 'Esporte') ?> · <?= e($championship['city'] ?? 'Cidade nao informada') ?> · <?= e($championship['status'] ?? 'ativo') ?></p>
    </div>
</section>

<section class="container py-4">
    <div class="manager-hero">
        <div>
            <span class="badge text-bg-light mb-2"><?= e($championship['competition_format'] ?? 'Competicao') ?></span>
            <h2><?= e($championship['name']) ?></h2>
            <p><?= e($championship['court_or_field'] ?? 'Quadra/campo a definir') ?></p>
        </div>
        <div class="manager-actions">
            <a class="btn btn-light" href="<?= url('/campeonatos/' . $championship['id']) ?>"><i class="fa-solid fa-eye"></i> Pagina publica</a>
            <a class="btn btn-warning" href="<?= url('/organizador/inscricoes') ?>"><i class="fa-solid fa-clipboard-check"></i> Inscricoes</a>
        </div>
    </div>

    <div class="metric-grid mt-3">
        <div><strong><?= (int) $summary['teams_count'] ?></strong><span>Equipes</span></div>
        <div><strong><?= (int) $summary['athletes_count'] ?></strong><span>Atletas</span></div>
        <div><strong><?= (int) $summary['matches_count'] ?></strong><span>Partidas</span></div>
        <div><strong><?= (int) ($summary['in_progress_matches'] ?? 0) ?></strong><span>Em andamento</span></div>
    </div>

    <div class="competition-tabs mt-4">
        <ul class="nav nav-pills flex-nowrap" role="tablist">
            <?php foreach (['resumo' => 'Visao geral', 'equipes' => 'Equipes', 'atletas' => 'Atletas', 'partidas' => 'Partidas', 'classificacao' => 'Classificacao', 'estatisticas' => 'Estatisticas'] as $key => $label): ?>
                <li class="nav-item"><button class="nav-link <?= $key === 'resumo' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#<?= e($key) ?>" type="button"><?= e($label) ?></button></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="resumo">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="panel h-100">
                        <div class="section-heading"><h2>Visao geral</h2><span><?= e($summary['status']) ?></span></div>
                        <div class="progress-steps">
                            <?php foreach ($summary['steps'] as $step): ?>
                                <div class="progress-step <?= e($step['state']) ?>"><i class="fa-solid <?= $step['state'] === 'done' ? 'fa-check' : ($step['state'] === 'problem' ? 'fa-triangle-exclamation' : 'fa-clock') ?>"></i><span><?= e($step['label']) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="quick-actions mt-4">
                            <button class="btn btn-primary" data-pc-open-tab="#equipes" data-pc-open-collapse="#teamCreateForm"><i class="fa-solid fa-shield-halved"></i> + Adicionar equipe</button>
                            <button class="btn btn-primary" data-pc-open-tab="#atletas" data-pc-open-collapse="#athleteCreateForm"><i class="fa-solid fa-user-plus"></i> + Adicionar atleta</button>
                            <button class="btn btn-primary" data-pc-open-tab="#partidas" data-pc-open-collapse="#matchCreateForm"><i class="fa-solid fa-calendar-plus"></i> + Adicionar partida</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel h-100">
                        <h2>Proxima partida</h2>
                        <?php if ($summary['next_match']): ?>
                            <p class="lead mb-1"><?= e($matchName($summary['next_match'])) ?></p>
                            <p class="text-muted"><?= e($summary['next_match']['match_date'] ?? 'Data indefinida') ?> <?= e($summary['next_match']['match_time'] ?? '') ?></p>
                            <a class="btn btn-sm btn-primary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $summary['next_match']['id'] . '/gerenciar') ?>">Gerenciar partida</a>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma partida agendada.</p>
                        <?php endif; ?>
                        <hr>
                        <h2>Historico</h2>
                        <?php foreach ($activityLogs as $log): ?><div class="activity-item"><strong><?= e($log['action']) ?></strong><span><?= e($log['description']) ?></span><small><?= e(date('d/m/Y H:i', strtotime($log['created_at']))) ?></small></div><?php endforeach; ?>
                        <?php if (!$activityLogs): ?><p class="text-muted">Nenhuma atividade registrada.</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="equipes">
            <div class="section-heading"><div><h2>Equipes</h2><span><?= count($teams) ?> cadastradas</span></div><button class="btn btn-primary" data-pc-open-tab="#equipes" data-pc-open-collapse="#teamCreateForm">+ Adicionar equipe</button></div>
            <form id="teamCreateForm" class="panel row g-3 collapse mb-3" method="post" action="<?= url($actionBase . '/equipes') ?>">
                <?= csrf_field() ?><input type="hidden" name="return_to" value="<?= e($currentUrl . '#equipes') ?>">
                <div class="col-md-3"><label class="form-label">Nome</label><input class="form-control" name="name" required></div>
                <div class="col-md-2"><label class="form-label">Cidade</label><input class="form-control" name="city"></div>
                <div class="col-md-2"><label class="form-label">Responsavel</label><input class="form-control" name="responsible_name"></div>
                <div class="col-md-2"><label class="form-label">Telefone</label><input class="form-control" name="responsible_phone"></div>
                <div class="col-md-2"><label class="form-label">Escudo</label><input class="form-control" name="shield" placeholder="URL/caminho"></div>
                <div class="col-md-1"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($teamStatuses as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><button class="btn btn-primary">Salvar equipe</button></div>
            </form>
            <div class="row g-3">
                <?php foreach ($teams as $team): ?>
                    <div class="col-lg-4">
                        <div class="team-card">
                            <?php if (!empty($team['shield'])): ?><img class="team-shield" src="<?= e($team['shield']) ?>" alt=""><?php else: ?><div class="team-shield"><?= e(mb_strtoupper(mb_substr($team['name'], 0, 2))) ?></div><?php endif; ?>
                            <h3><?= e($team['name']) ?></h3>
                            <p><?= e($team['city'] ?? 'Cidade nao informada') ?></p>
                            <span class="badge <?= e($statusBadge($team['status'])) ?>"><?= e($teamStatuses[$team['status']] ?? $team['status']) ?></span>
                            <p class="mt-2"><?= (int) $team['athletes_count'] ?> atletas</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#teamView<?= (int) $team['id'] ?>">Visualizar</button>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#teamEdit<?= (int) $team['id'] ?>">Editar</button>
                                <form method="post" action="<?= url($actionBase . '/equipes/' . $team['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir equipe sem jogos vinculados?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form>
                            </div>
                            <div id="teamView<?= (int) $team['id'] ?>" class="collapse mt-3"><p class="sc-log"><strong>Responsavel</strong><span><?= e($team['responsible_name'] ?? '-') ?></span></p><p class="sc-log"><strong>Telefone</strong><span><?= e($team['responsible_phone'] ?? '-') ?></span></p></div>
                            <form id="teamEdit<?= (int) $team['id'] ?>" class="collapse row g-2 mt-3" method="post" action="<?= url($actionBase . '/equipes') ?>">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $team['id'] ?>"><input type="hidden" name="return_to" value="<?= e($currentUrl . '#equipes') ?>">
                                <div class="col-12"><input class="form-control" name="name" value="<?= e($team['name']) ?>" required></div>
                                <div class="col-md-6"><input class="form-control" name="city" value="<?= e($team['city'] ?? '') ?>" placeholder="Cidade"></div>
                                <div class="col-md-6"><input class="form-control" name="responsible_name" value="<?= e($team['responsible_name'] ?? '') ?>" placeholder="Responsavel"></div>
                                <div class="col-md-6"><input class="form-control" name="responsible_phone" value="<?= e($team['responsible_phone'] ?? '') ?>" placeholder="Telefone"></div>
                                <div class="col-md-6"><input class="form-control" name="shield" value="<?= e($team['shield'] ?? '') ?>" placeholder="Escudo"></div>
                                <div class="col-md-6"><select class="form-select" name="status"><?php foreach ($teamStatuses as $value => $label): ?><option value="<?= e($value) ?>" <?= ($team['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                                <div class="col-12"><button class="btn btn-sm btn-primary">Salvar alteracoes</button></div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$teams): ?><div class="col-12"><div class="panel"><p class="text-muted mb-0">Nenhuma equipe cadastrada.</p></div></div><?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="atletas">
            <div class="section-heading"><div><h2>Atletas</h2><span><?= count($athletes) ?> cadastrados</span></div><button class="btn btn-primary" data-pc-open-tab="#atletas" data-pc-open-collapse="#athleteCreateForm">+ Adicionar atleta</button></div>
            <form id="athleteCreateForm" class="panel row g-3 collapse mb-3" method="post" action="<?= url($actionBase . '/atletas') ?>">
                <?= csrf_field() ?><input type="hidden" name="return_to" value="<?= e($currentUrl . '#atletas') ?>">
                <div class="col-md-3"><label class="form-label">Nome</label><input class="form-control" name="name" required></div>
                <div class="col-md-2"><label class="form-label">Equipe</label><select class="form-select" name="team_id"><option value="">Sem equipe</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-1"><label class="form-label">Camisa</label><input class="form-control" type="number" name="shirt_number"></div>
                <div class="col-md-2"><label class="form-label">Posicao</label><input class="form-control" name="position"></div>
                <div class="col-md-2"><label class="form-label">Categoria</label><input class="form-control" name="category"></div>
                <div class="col-md-2"><label class="form-label">Cidade</label><input class="form-control" name="city"></div>
                <div class="col-md-2"><label class="form-label">Nascimento</label><input class="form-control" type="date" name="birth_date"></div>
                <div class="col-md-4"><label class="form-label">Foto</label><input class="form-control" name="photo" placeholder="URL/caminho"></div>
                <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($athleteStatuses as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><button class="btn btn-primary">Salvar atleta</button></div>
            </form>
            <?php foreach ($athletesByTeam as $teamName => $group): ?>
                <div class="panel table-responsive mb-3">
                    <div class="section-heading"><h2><?= e($teamName) ?></h2><span><?= count($group) ?> atletas</span></div>
                    <table class="table align-middle">
                        <thead><tr><th>Nome</th><th>#</th><th>Posicao</th><th>Categoria</th><th>Cidade</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($group as $athlete): ?>
                                <tr>
                                    <td><?= e($athlete['name']) ?></td><td><?= e((string) ($athlete['shirt_number'] ?? '')) ?></td><td><?= e($athlete['position'] ?? '') ?></td><td><?= e($athlete['category'] ?? '') ?></td><td><?= e($athlete['city'] ?? '') ?></td><td><span class="badge <?= e($statusBadge($athlete['status'])) ?>"><?= e($athleteStatuses[$athlete['status']] ?? $athlete['status']) ?></span></td>
                                    <td class="text-end d-flex justify-content-end gap-2"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#athleteView<?= (int) $athlete['id'] ?>">Visualizar</button> <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#athleteEdit<?= (int) $athlete['id'] ?>">Editar</button><form method="post" action="<?= url($actionBase . '/atletas/' . $athlete['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir participante sem jogos vinculados?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form></td>
                                </tr>
                                <tr class="collapse" id="athleteView<?= (int) $athlete['id'] ?>"><td colspan="7"><p class="mb-0">Nascimento: <?= e($athlete['birth_date'] ?? '-') ?> · Foto: <?= e($athlete['photo'] ?? '-') ?></p></td></tr>
                                <tr class="collapse" id="athleteEdit<?= (int) $athlete['id'] ?>"><td colspan="7">
                                    <form class="row g-2" method="post" action="<?= url($actionBase . '/atletas') ?>">
                                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $athlete['id'] ?>"><input type="hidden" name="return_to" value="<?= e($currentUrl . '#atletas') ?>">
                                        <div class="col-md-3"><input class="form-control" name="name" value="<?= e($athlete['name']) ?>" required></div>
                                        <div class="col-md-2"><select class="form-select" name="team_id"><option value="">Sem equipe</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>" <?= (int) ($athlete['team_id'] ?? 0) === (int) $team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                                        <div class="col-md-1"><input class="form-control" type="number" name="shirt_number" value="<?= e((string) ($athlete['shirt_number'] ?? '')) ?>"></div>
                                        <div class="col-md-2"><input class="form-control" name="position" value="<?= e($athlete['position'] ?? '') ?>" placeholder="Posicao"></div>
                                        <div class="col-md-2"><input class="form-control" name="category" value="<?= e($athlete['category'] ?? '') ?>" placeholder="Categoria"></div>
                                        <div class="col-md-2"><input class="form-control" name="city" value="<?= e($athlete['city'] ?? '') ?>" placeholder="Cidade"></div>
                                        <div class="col-md-2"><input class="form-control" type="date" name="birth_date" value="<?= e($athlete['birth_date'] ?? '') ?>"></div>
                                        <div class="col-md-4"><input class="form-control" name="photo" value="<?= e($athlete['photo'] ?? '') ?>" placeholder="Foto"></div>
                                        <div class="col-md-2"><select class="form-select" name="status"><?php foreach ($athleteStatuses as $value => $label): ?><option value="<?= e($value) ?>" <?= ($athlete['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                                        <div class="col-md-4"><button class="btn btn-sm btn-primary">Salvar</button></div>
                                    </form>
                                </td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
            <?php if (!$athletes): ?><div class="panel"><p class="text-muted mb-0">Nenhum atleta cadastrado.</p></div><?php endif; ?>
        </div>

        <div class="tab-pane fade" id="partidas">
            <div class="section-heading"><div><h2>Partidas</h2><span><?= count($matches) ?> cadastradas</span></div><button class="btn btn-primary" data-pc-open-tab="#partidas" data-pc-open-collapse="#matchCreateForm">+ Adicionar partida</button></div>
            <form id="matchCreateForm" class="panel row g-3 collapse mb-3" method="post" action="<?= url($actionBase . '/jogos') ?>">
                <?= csrf_field() ?><input type="hidden" name="return_to" value="<?= e($currentUrl . '#partidas') ?>">
                <div class="col-md-2"><label class="form-label">Fase</label><input class="form-control" name="phase" value="Fase unica"></div>
                <div class="col-md-2"><label class="form-label">Grupo</label><input class="form-control" name="group_name"></div>
                <div class="col-md-1"><label class="form-label">Rodada</label><input class="form-control" type="number" name="round_number"></div>
                <div class="col-md-3"><label class="form-label">Mandante</label><select class="form-select" name="home_team_id"><option value="">A definir</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Visitante</label><select class="form-select" name="away_team_id"><option value="">A definir</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">Data</label><input class="form-control" type="date" name="match_date"></div>
                <div class="col-md-2"><label class="form-label">Horario</label><input class="form-control" type="time" name="match_time"></div>
                <div class="col-md-3"><label class="form-label">Local</label><input class="form-control" name="venue"></div>
                <div class="col-md-2"><label class="form-label">Quadra/campo</label><input class="form-control" name="court_or_field"></div>
                <div class="col-md-2"><label class="form-label">Arbitro</label><input class="form-control" name="referee"></div>
                <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($matchStatuses as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><label class="form-label">Observacoes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                <div class="col-12"><button class="btn btn-primary">Salvar partida</button></div>
            </form>
            <?php foreach ($matchesByRound as $round => $roundMatches): ?>
                <div class="panel mb-3">
                    <div class="section-heading"><h2><?= e($round) ?></h2><span><?= count($roundMatches) ?> partidas</span></div>
                    <?php foreach ($roundMatches as $match): ?>
                        <div class="match-card mb-3">
                            <div><span class="badge <?= e($statusBadge($match['status'])) ?>"><?= e($matchStatuses[$match['status']] ?? $match['status']) ?></span><h3><?= e($matchName($match)) ?></h3><p><?= e($match['phase']) ?> · <?= e($match['group_name'] ?? 'Sem grupo') ?> · <?= e($match['match_date'] ?? 'Data indefinida') ?> <?= e($match['match_time'] ?? '') ?></p></div>
                            <div class="match-card-score"><?= $match['home_score'] !== null ? (int) $match['home_score'] : '-' ?> x <?= $match['away_score'] !== null ? (int) $match['away_score'] : '-' ?></div>
                            <p><i class="fa-solid fa-location-dot"></i> <?= e($match['venue'] ?? 'Local indefinido') ?><?= !empty($match['court_or_field']) ? ' · ' . e($match['court_or_field']) : '' ?></p>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-primary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $match['id'] . '/gerenciar') ?>">Gerenciar</a>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#matchEdit<?= (int) $match['id'] ?>">Editar</button>
                                <a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . $championship['id'] . '/partidas/' . $match['id']) ?>">Visualizar</a>
                                <form method="post" action="<?= url($actionBase . '/jogos/' . $match['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir partida sem resultado, sets ou eventos?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form>
                            </div>
                            <form id="matchEdit<?= (int) $match['id'] ?>" class="collapse row g-2 mt-3" method="post" action="<?= url($actionBase . '/jogos') ?>">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $match['id'] ?>"><input type="hidden" name="return_to" value="<?= e($currentUrl . '#partidas') ?>">
                                <div class="col-md-2"><input class="form-control" name="phase" value="<?= e($match['phase'] ?? '') ?>" placeholder="Fase"></div>
                                <div class="col-md-2"><input class="form-control" name="group_name" value="<?= e($match['group_name'] ?? '') ?>" placeholder="Grupo"></div>
                                <div class="col-md-1"><input class="form-control" type="number" name="round_number" value="<?= e((string) ($match['round_number'] ?? '')) ?>" placeholder="Rod."></div>
                                <div class="col-md-3"><select class="form-select" name="home_team_id"><option value="">A definir</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>" <?= (int) ($match['home_team_id'] ?? 0) === (int) $team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                                <div class="col-md-3"><select class="form-select" name="away_team_id"><option value="">A definir</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>" <?= (int) ($match['away_team_id'] ?? 0) === (int) $team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option><?php endforeach; ?></select></div>
                                <div class="col-md-2"><input class="form-control" type="date" name="match_date" value="<?= e($match['match_date'] ?? '') ?>"></div>
                                <div class="col-md-2"><input class="form-control" type="time" name="match_time" value="<?= e(!empty($match['match_time']) ? substr((string) $match['match_time'], 0, 5) : '') ?>"></div>
                                <div class="col-md-3"><input class="form-control" name="venue" value="<?= e($match['venue'] ?? '') ?>" placeholder="Local"></div>
                                <div class="col-md-2"><input class="form-control" name="court_or_field" value="<?= e($match['court_or_field'] ?? '') ?>" placeholder="Quadra"></div>
                                <div class="col-md-2"><input class="form-control" name="referee" value="<?= e($match['referee'] ?? '') ?>" placeholder="Arbitro"></div>
                                <div class="col-md-2"><select class="form-select" name="status"><?php foreach ($matchStatuses as $value => $label): ?><option value="<?= e($value) ?>" <?= ($match['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                                <div class="col-12"><textarea class="form-control" name="notes" rows="2" placeholder="Observacoes"><?= e($match['notes'] ?? '') ?></textarea></div>
                                <div class="col-12"><button class="btn btn-sm btn-primary">Salvar partida</button></div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$matches): ?><div class="panel"><p class="text-muted mb-0">Nenhuma partida cadastrada.</p></div><?php endif; ?>
        </div>

        <div class="tab-pane fade" id="classificacao">
            <div class="section-heading"><div><h2>Classificacao</h2><span>Baseada nas partidas finalizadas</span></div><form method="post" action="<?= url($actionBase . '/classificacao/recalcular') ?>"><?= csrf_field() ?><button class="btn btn-primary">Recalcular classificacao</button></form></div>
            <?php foreach ($standingsByGroup as $group => $rows): ?>
                <div class="panel table-responsive mb-3">
                    <h2><?= e($group) ?></h2>
                    <table class="table standings-table"><thead><tr><th>#</th><th>Equipe</th><th>J</th><th>V</th><th>E</th><th>D</th><th>PF</th><th>PC</th><th>Saldo</th><th>PTS</th></tr></thead><tbody><?php foreach ($rows as $i => $row): ?><tr><td><?= $i + 1 ?></td><td><?= e($row['team_name'] ?? $row['athlete_name'] ?? '') ?></td><td><?= (int) $row['played'] ?></td><td><?= (int) $row['wins'] ?></td><td><?= (int) $row['draws'] ?></td><td><?= (int) $row['losses'] ?></td><td><?= (int) $row['score_for'] ?></td><td><?= (int) $row['score_against'] ?></td><td><?= (int) $row['score_difference'] ?></td><td><strong><?= (int) $row['points'] ?></strong></td></tr><?php endforeach; ?></tbody></table>
                </div>
            <?php endforeach; ?>
            <?php if (!$standings): ?><div class="panel"><p class="text-muted mb-0">Classificacao ainda nao disponivel.</p></div><?php endif; ?>
        </div>

        <div class="tab-pane fade" id="estatisticas">
            <div class="section-heading"><div><h2>Estatisticas</h2><span>Atletas com eventos registrados</span></div><a class="btn btn-outline-secondary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/relatorios/artilharia') ?>">Exportar CSV</a></div>
            <div class="panel table-responsive">
                <?php if (!$statistics): ?><p class="text-muted mb-0">Nenhuma estatistica registrada.</p><?php else: ?>
                    <table class="table align-middle"><thead><tr><th>#</th><th>Atleta</th><th>Equipe</th><th>Pontos</th><th>Aces</th><th>Bloqueios</th><th>Gols</th><th>Amarelos</th><th>Vermelhos</th></tr></thead><tbody><?php foreach ($statistics as $i => $stat): ?><tr><td><?= $i + 1 ?></td><td><?= e($stat['athlete_name']) ?></td><td><?= e($stat['team_name'] ?? '') ?></td><td><?= (int) ($stat['points'] ?? 0) ?></td><td><?= (int) ($stat['aces'] ?? 0) ?></td><td><?= (int) ($stat['blocks'] ?? 0) ?></td><td><?= (int) ($stat['goals'] ?? 0) ?></td><td><?= (int) ($stat['yellow_cards'] ?? 0) ?></td><td><?= (int) ($stat['red_cards'] ?? 0) ?></td></tr><?php endforeach; ?></tbody></table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.bootstrap || !window.location.hash) {
        return;
    }
    var trigger = document.querySelector('[data-bs-target="' + window.location.hash + '"]');
    if (trigger) {
        window.bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
});
document.addEventListener('DOMContentLoaded', function () {
    if (!window.bootstrap) {
        return;
    }
    document.querySelectorAll('[data-pc-open-tab][data-pc-open-collapse]').forEach(function (button) {
        button.addEventListener('click', function () {
            var tabTarget = button.getAttribute('data-pc-open-tab');
            var collapseTarget = button.getAttribute('data-pc-open-collapse');
            var tabButton = document.querySelector('[data-bs-target="' + tabTarget + '"]');
            var collapseElement = document.querySelector(collapseTarget);
            if (tabButton) {
                window.bootstrap.Tab.getOrCreateInstance(tabButton).show();
            }
            if (collapseElement) {
                window.bootstrap.Collapse.getOrCreateInstance(collapseElement, { toggle: false }).show();
                window.location.hash = tabTarget;
            }
        });
    });
});
</script>
