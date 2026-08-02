<?php
$actionBase = '/organizador/campeonatos/' . $championship['id'] . '/gerenciar';
$matchName = static function (array $match): string {
    return ($match['home_team_name'] ?? $match['home_athlete_name'] ?? 'A definir') . ' x ' . ($match['away_team_name'] ?? $match['away_athlete_name'] ?? 'A definir');
};
$badge = static function (string $status): string {
    return [
        'agendada' => 'text-bg-primary',
        'em_andamento' => 'text-bg-warning',
        'finalizada' => 'text-bg-success',
        'adiada' => 'text-bg-secondary',
        'cancelada' => 'text-bg-danger',
    ][$status] ?? 'text-bg-secondary';
};
$filteredMatches = array_filter($matches, function (array $match): bool {
    foreach (['phase' => 'phase', 'status' => 'status', 'round' => 'round_number', 'team' => 'team'] as $query => $field) {
        if (($_GET[$query] ?? '') === '') {
            continue;
        }
        if ($query === 'team') {
            $needle = mb_strtolower($_GET[$query]);
            $haystack = mb_strtolower(($match['home_team_name'] ?? '') . ' ' . ($match['away_team_name'] ?? ''));
            if (!str_contains($haystack, $needle)) {
                return false;
            }
            continue;
        }
        if ((string) ($match[$field] ?? '') !== (string) $_GET[$query]) {
            return false;
        }
    }
    if (($_GET['date'] ?? '') !== '' && ($_GET['date'] ?? '') !== ($match['match_date'] ?? '')) {
        return false;
    }
    return true;
});
$flatEvents = array_merge(...array_values($eventsByMatch ?: [[]]));
$goalsByRound = [];
$cardsByTeam = [];
foreach ($flatEvents as $event) {
    $match = null;
    foreach ($matches as $candidate) {
        if ((int) $candidate['id'] === (int) $event['match_id']) {
            $match = $candidate;
            break;
        }
    }
    if (in_array($event['event_type'] ?? '', ['gol', 'gol_contra', 'penalti_convertido'], true)) {
        $round = 'Rodada ' . ($match['round_number'] ?? '-');
        $goalsByRound[$round] = ($goalsByRound[$round] ?? 0) + 1;
    }
    if (in_array($event['event_type'] ?? '', ['cartao_amarelo', 'cartao_vermelho'], true)) {
        $team = $event['team_name'] ?? 'Sem equipe';
        $cardsByTeam[$team] = ($cardsByTeam[$team] ?? 0) + 1;
    }
}
?>
<section class="page-band"><div class="container"><h1>Gerenciar competicao</h1><p><?= e($championship['name']) ?></p></div></section>
<section class="container py-4">
    <div class="manager-hero">
        <div>
            <span class="badge text-bg-light mb-2"><?= e($championship['sport_name'] ?? 'Esporte') ?></span>
            <h2><?= e($championship['name']) ?></h2>
            <p><?= e($championship['city'] ?? '') ?> · <?= e($championship['status'] ?? '') ?></p>
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
        <div><strong><?= (int) $summary['finished_matches'] ?></strong><span>Finalizadas</span></div>
        <div><strong><?= (int) $summary['pending_registrations'] ?></strong><span>Inscricoes pendentes</span></div>
        <div><strong><?= (int) $summary['goals_count'] ?></strong><span>Gols registrados</span></div>
    </div>

    <div class="competition-tabs mt-4">
        <ul class="nav nav-pills flex-nowrap">
            <?php foreach ([
                'resumo' => 'Resumo', 'equipes' => 'Equipes', 'atletas' => 'Atletas', 'inscricoes' => 'Inscricoes',
                'jogos' => 'Jogos', 'resultados' => 'Resultados', 'classificacao' => 'Classificacao',
                'chaveamento' => 'Chaveamento', 'estatisticas' => 'Estatisticas', 'regulamento' => 'Regulamento',
                'configuracoes' => 'Configuracoes',
            ] as $key => $label): ?>
                <li class="nav-item"><button class="nav-link <?= $key === 'resumo' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#<?= $key ?>" type="button"><?= e($label) ?></button></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="resumo">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="panel h-100">
                        <div class="section-heading"><h2>Progresso do campeonato</h2><span><?= e($summary['status']) ?></span></div>
                        <div class="progress-steps">
                            <?php foreach ($summary['steps'] as $step): ?>
                                <div class="progress-step <?= e($step['state']) ?>"><i class="fa-solid <?= $step['state'] === 'done' ? 'fa-check' : ($step['state'] === 'problem' ? 'fa-triangle-exclamation' : 'fa-clock') ?>"></i><span><?= e($step['label']) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="quick-actions mt-4">
                            <button class="btn btn-primary" data-bs-toggle="pill" data-bs-target="#equipes"><i class="fa-solid fa-shield-halved"></i> Nova equipe</button>
                            <button class="btn btn-primary" data-bs-toggle="pill" data-bs-target="#atletas"><i class="fa-solid fa-user-plus"></i> Novo atleta</button>
                            <button class="btn btn-primary" data-bs-toggle="pill" data-bs-target="#jogos"><i class="fa-solid fa-calendar-plus"></i> Nova partida</button>
                            <button class="btn btn-outline-primary" data-bs-toggle="pill" data-bs-target="#resultados"><i class="fa-solid fa-square-poll-vertical"></i> Registrar resultado</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel h-100">
                        <h2>Proxima partida</h2>
                        <?php if ($summary['next_match']): ?>
                            <p class="lead mb-1"><?= e($matchName($summary['next_match'])) ?></p>
                            <p class="text-muted"><?= e($summary['next_match']['match_date'] ?? 'Data indefinida') ?> <?= e($summary['next_match']['match_time'] ?? '') ?></p>
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
            <div class="section-heading"><div><h2>Equipes</h2><span>Cadastre equipes e acompanhe seus elencos.</span></div><button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#teamForm">Nova equipe</button></div>
            <form id="teamForm" class="panel row g-3 collapse show" method="post" action="<?= url($actionBase . '/equipes') ?>"><?= csrf_field() ?><div class="col-md-3"><input class="form-control" name="name" required placeholder="Nome da equipe"></div><div class="col-md-2"><input class="form-control" name="city" placeholder="Cidade"></div><div class="col-md-3"><input class="form-control" name="responsible_name" placeholder="Responsavel"></div><div class="col-md-2"><input class="form-control" name="responsible_phone" placeholder="Telefone"></div><div class="col-md-2"><select class="form-select" name="status"><option value="aprovado">Aprovada</option><option value="pendente">Pendente</option><option value="rejeitado">Rejeitada</option></select></div><div class="col-12"><button class="btn btn-primary">Salvar equipe</button></div></form>
            <div class="row g-3 mt-1"><?php foreach ($teams as $team): ?><div class="col-md-4"><div class="team-card"><div class="team-shield"><?= e(mb_strtoupper(mb_substr($team['name'], 0, 2))) ?></div><h3><?= e($team['name']) ?></h3><p><?= e($team['city'] ?? 'Cidade nao informada') ?></p><span class="badge text-bg-secondary"><?= e($team['status']) ?></span><p class="mt-2"><?= (int) $team['athletes_count'] ?> atletas</p><form method="post" action="<?= url($actionBase . '/equipes/' . $team['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir equipe sem jogos vinculados?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form></div></div><?php endforeach; ?><?php if (!$teams): ?><div class="col-12"><div class="panel"><p class="text-muted mb-0">Nenhuma equipe cadastrada.</p></div></div><?php endif; ?></div>
        </div>

        <div class="tab-pane fade" id="atletas">
            <div class="section-heading"><div><h2>Atletas</h2><span>Gerencie elenco e participantes individuais.</span></div><a class="btn btn-outline-secondary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/relatorios/participantes') ?>">Exportar CSV</a></div>
            <form class="panel row g-3" method="post" action="<?= url($actionBase . '/atletas') ?>"><?= csrf_field() ?><div class="col-md-3"><input class="form-control" name="name" required placeholder="Nome"></div><div class="col-md-2"><select class="form-select" name="team_id"><option value="">Sem equipe</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><input class="form-control" name="city" placeholder="Cidade"></div><div class="col-md-1"><input class="form-control" type="number" name="shirt_number" placeholder="#"></div><div class="col-md-2"><input class="form-control" name="position" placeholder="Posicao"></div><div class="col-md-2"><input class="form-control" name="category" placeholder="Categoria"></div><div class="col-md-2"><select class="form-select" name="status"><option value="aprovado">Aprovado</option><option value="pendente">Pendente</option><option value="rejeitado">Rejeitado</option></select></div><div class="col-12"><button class="btn btn-primary">Salvar atleta</button></div></form>
            <div class="panel mt-3 table-responsive"><?php if (!$athletes): ?><p class="text-muted">Nenhum atleta cadastrado.</p><?php else: ?><table class="table align-middle"><thead><tr><th>Nome</th><th>Equipe</th><th>Cidade</th><th>#</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($athletes as $athlete): ?><tr><td><?= e($athlete['name']) ?></td><td><?= e($athlete['team_name'] ?? '') ?></td><td><?= e($athlete['city'] ?? '') ?></td><td><?= e((string) ($athlete['shirt_number'] ?? '')) ?></td><td><span class="badge text-bg-secondary"><?= e($athlete['status']) ?></span></td><td><form method="post" action="<?= url($actionBase . '/atletas/' . $athlete['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir participante sem jogos vinculados?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div>
        </div>

        <div class="tab-pane fade" id="inscricoes"><div class="panel"><h2>Inscricoes</h2><p>Revise solicitacoes pendentes, aprove participantes e acompanhe comprovantes.</p><a class="btn btn-primary" href="<?= url('/organizador/inscricoes') ?>">Gerenciar inscricoes</a></div></div>

        <div class="tab-pane fade" id="jogos">
            <div class="section-heading"><div><h2>Jogos</h2><span>Filtre, cadastre e gerencie a tabela de partidas.</span></div><a class="btn btn-outline-secondary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/relatorios/jogos') ?>">Exportar CSV</a></div>
            <form class="filter-box row g-2 mb-3" method="get"><div class="col-md-2"><input class="form-control" name="phase" value="<?= e($_GET['phase'] ?? '') ?>" placeholder="Fase"></div><div class="col-md-2"><input class="form-control" name="round" value="<?= e($_GET['round'] ?? '') ?>" placeholder="Rodada"></div><div class="col-md-2"><select class="form-select" name="status"><option value="">Status</option><?php foreach (['agendada','em_andamento','finalizada','adiada','cancelada'] as $s): ?><option value="<?= e($s) ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><input class="form-control" type="date" name="date" value="<?= e($_GET['date'] ?? '') ?>"></div><div class="col-md-3"><input class="form-control" name="team" value="<?= e($_GET['team'] ?? '') ?>" placeholder="Equipe"></div><div class="col-md-1"><button class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i></button></div></form>
            <form class="panel row g-3" method="post" action="<?= url($actionBase . '/jogos') ?>"><?= csrf_field() ?><div class="col-md-2"><input class="form-control" name="phase" placeholder="Fase"></div><div class="col-md-1"><input class="form-control" type="number" name="round_number" placeholder="Rod."></div><div class="col-md-2"><input class="form-control" name="group_name" placeholder="Grupo"></div><div class="col-md-2"><input class="form-control" type="date" name="match_date"></div><div class="col-md-1"><input class="form-control" type="time" name="match_time"></div><div class="col-md-2"><input class="form-control" name="venue" placeholder="Local"></div><div class="col-md-2"><input class="form-control" name="court_or_field" placeholder="Quadra/campo"></div><div class="col-md-3"><select class="form-select" name="home_team_id"><option value="">Mandante equipe</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><select class="form-select" name="away_team_id"><option value="">Visitante equipe</option><?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><select class="form-select" name="next_match_id"><option value="">Proxima partida</option><?php foreach ($matches as $match): ?><option value="<?= (int) $match['id'] ?>"><?= e($matchName($match)) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="next_match_position"><option value="">Posicao</option><option value="home">Mandante</option><option value="away">Visitante</option></select></div><div class="col-12"><button class="btn btn-primary">Nova partida</button></div></form>
            <div class="row g-3 mt-1"><?php foreach ($filteredMatches as $match): ?><div class="col-lg-6"><div class="match-card"><div><span class="badge <?= e($badge($match['status'])) ?>"><?= e($match['status']) ?></span><h3><?= e($matchName($match)) ?></h3><p><?= e($match['phase']) ?> · Rodada <?= e((string) ($match['round_number'] ?? '-')) ?> · <?= e($match['group_name'] ?? 'Sem grupo') ?></p></div><div class="match-card-score"><?= $match['home_score'] !== null ? (int) $match['home_score'] : '-' ?> x <?= $match['away_score'] !== null ? (int) $match['away_score'] : '-' ?></div><p><i class="fa-solid fa-calendar"></i> <?= e($match['match_date'] ?? 'Data indefinida') ?> <?= e($match['match_time'] ?? '') ?> · <?= e($match['venue'] ?? 'Local indefinido') ?></p><div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-primary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $match['id'] . '/gerenciar') ?>">Gerenciar</a><a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . $championship['id'] . '/partidas/' . $match['id']) ?>">Ver detalhes</a><form method="post" action="<?= url($actionBase . '/jogos/' . $match['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir jogo sem resultado?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form></div></div></div><?php endforeach; ?><?php if (!$filteredMatches): ?><div class="col-12"><div class="panel"><p class="text-muted mb-0">Nenhum jogo encontrado.</p></div></div><?php endif; ?></div>
        </div>

        <div class="tab-pane fade" id="resultados"><div class="panel"><h2>Resultados</h2><p>Abra uma partida para registrar placar, eventos, cartoes e sets no centro de controle.</p><?php foreach ($matches as $match): ?><a class="btn btn-outline-primary me-2 mb-2" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/partidas/' . $match['id'] . '/gerenciar') ?>"><?= e($matchName($match)) ?></a><?php endforeach; ?><?php if (!$matches): ?><p class="text-muted">Nenhum jogo cadastrado.</p><?php endif; ?></div></div>

        <div class="tab-pane fade" id="classificacao">
            <div class="section-heading"><div><h2>Classificacao</h2><span>Atualizada automaticamente ao salvar resultados.</span></div><div><a class="btn btn-outline-secondary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/relatorios/classificacao') ?>">CSV</a></div></div>
            <div class="panel mb-3"><form method="post" action="<?= url($actionBase . '/classificacao/recalcular') ?>"><?= csrf_field() ?><button class="btn btn-primary">Recalcular classificacao</button></form></div>
            <div class="panel table-responsive"><?php if (!$standings): ?><p class="text-muted">Classificacao ainda nao disponivel.</p><?php else: ?><table class="table standings-table"><thead><tr><th>#</th><th>Equipe</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GM</th><th>GS</th><th>SG</th><th>PTS</th></tr></thead><tbody><?php foreach ($standings as $i => $row): ?><tr class="<?= $i < 2 ? 'zone-qualified' : ($i > count($standings) - 3 ? 'zone-out' : '') ?>"><td><?= $i + 1 ?></td><td><?= e($row['team_name'] ?? $row['athlete_name'] ?? '') ?><br><small><?= e($row['group_name'] ?? '') ?></small></td><td><?= (int) $row['played'] ?></td><td><?= (int) $row['wins'] ?></td><td><?= (int) $row['draws'] ?></td><td><?= (int) $row['losses'] ?></td><td><?= (int) $row['score_for'] ?></td><td><?= (int) $row['score_against'] ?></td><td><?= (int) $row['score_difference'] ?></td><td><strong><?= (int) $row['points'] ?></strong></td></tr><?php endforeach; ?></tbody></table><p class="text-muted mb-0">Ultima atualizacao: <?= e(date('d/m/Y H:i')) ?></p><?php endif; ?></div>
        </div>

        <div class="tab-pane fade" id="chaveamento"><div class="bracket-scroll professional-bracket"><?php if (!$matches): ?><div class="panel"><p class="text-muted mb-0">Chaveamento ainda nao definido.</p></div><?php else: ?><?php foreach (['Oitavas','Quartas','Semifinal','Final'] as $phase): ?><div class="bracket-column"><h3><?= e($phase) ?></h3><?php foreach ($matches as $match): ?><?php if (mb_strtolower($match['phase']) !== mb_strtolower($phase)) continue; ?><div class="bracket-match"><span><?= e($matchName($match)) ?></span><strong><?= $match['home_score'] ?? '-' ?> x <?= $match['away_score'] ?? '-' ?></strong><small><?= e($match['winner_team_name'] ?? 'Vencedor a definir') ?></small></div><?php endforeach; ?></div><?php endforeach; ?><div class="bracket-column champion-column"><h3>Campeao</h3><div class="bracket-match"><strong><?= e($championship['status'] === 'encerrado' ? 'Definido pela final' : 'A definir') ?></strong></div></div><?php endif; ?></div></div>

        <div class="tab-pane fade" id="estatisticas">
            <div class="section-heading"><div><h2>Estatisticas</h2><span>Indicadores para apresentacao e leitura tecnica.</span></div><a class="btn btn-outline-secondary" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/relatorios/artilharia') ?>">CSV</a></div>
            <div class="metric-grid mb-3"><div><strong><?= (int) $summary['stats_cards']['total_goals'] ?></strong><span>Total de gols</span></div><div><strong><?= e((string) $summary['stats_cards']['avg_goals']) ?></strong><span>Media por partida</span></div><div><strong><?= (int) $summary['stats_cards']['yellow_cards'] ?></strong><span>Amarelos</span></div><div><strong><?= (int) $summary['stats_cards']['red_cards'] ?></strong><span>Vermelhos</span></div><div><strong><?= (int) $summary['stats_cards']['played_matches'] ?></strong><span>Partidas realizadas</span></div></div>
            <div class="row g-3">
                <div class="col-lg-4"><div class="panel h-100"><h2>Artilheiros</h2><?php if ($statistics): ?><canvas data-competition-chart="goals" data-labels="<?= e(implode('|', array_column($statistics, 'athlete_name'))) ?>" data-values="<?= e(implode('|', array_column($statistics, 'goals'))) ?>"></canvas><?php else: ?><p class="text-muted">Sem dados para grafico.</p><?php endif; ?></div></div>
                <div class="col-lg-4"><div class="panel h-100"><h2>Gols por rodada</h2><?php if ($goalsByRound): ?><canvas data-competition-chart="goals" data-labels="<?= e(implode('|', array_keys($goalsByRound))) ?>" data-values="<?= e(implode('|', array_values($goalsByRound))) ?>"></canvas><?php else: ?><p class="text-muted">Sem gols por rodada.</p><?php endif; ?></div></div>
                <div class="col-lg-4"><div class="panel h-100"><h2>Cartoes por equipe</h2><?php if ($cardsByTeam): ?><canvas data-competition-chart="goals" data-labels="<?= e(implode('|', array_keys($cardsByTeam))) ?>" data-values="<?= e(implode('|', array_values($cardsByTeam))) ?>"></canvas><?php else: ?><p class="text-muted">Sem cartoes por equipe.</p><?php endif; ?></div></div>
                <div class="col-12"><div class="panel table-responsive"><?php if (!$statistics): ?><p class="text-muted">Nenhuma estatistica registrada.</p><?php else: ?><table class="table"><thead><tr><th>#</th><th>Atleta</th><th>Equipe</th><th>Gols</th><th>Amarelos</th><th>Vermelhos</th></tr></thead><tbody><?php foreach ($statistics as $i => $stat): ?><tr><td><?= $i + 1 ?></td><td><?= e($stat['athlete_name']) ?></td><td><?= e($stat['team_name'] ?? '') ?></td><td><?= (int) $stat['goals'] ?></td><td><?= (int) $stat['yellow_cards'] ?></td><td><?= (int) $stat['red_cards'] ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div></div>
            </div>
        </div>

        <div class="tab-pane fade" id="regulamento">
            <form class="panel row g-3" method="post" action="<?= url($actionBase . '/informacoes') ?>"><?= csrf_field() ?><?php foreach (['rules' => 'Regulamento', 'tiebreak_rules' => 'Desempate', 'qualification_rules' => 'Classificacao', 'elimination_rules' => 'Eliminacao', 'required_documents' => 'Documentos', 'cancellation_policy' => 'Cancelamento'] as $field => $label): ?><div class="col-md-6"><label class="form-label"><?= e($label) ?></label><textarea class="form-control" rows="4" name="<?= e($field) ?>"><?= e($championship[$field] ?? '') ?></textarea></div><?php endforeach; ?><div class="col-12"><button class="btn btn-primary">Salvar regulamento</button></div></form>
        </div>

        <div class="tab-pane fade" id="configuracoes">
            <form class="panel row g-3" method="post" action="<?= url($actionBase . '/informacoes') ?>"><?= csrf_field() ?><div class="col-md-4"><label class="form-label">Formato</label><input class="form-control" name="competition_format" value="<?= e($championship['competition_format'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Encerramento</label><input class="form-control" type="date" name="end_date" value="<?= e($championship['end_date'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Limite inscricao</label><input class="form-control" type="date" name="registration_deadline" value="<?= e($championship['registration_deadline'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Inscricoes</label><select class="form-select" name="registrations_open"><option value="1" <?= !empty($championship['registrations_open']) ? 'selected' : '' ?>>Abertas</option><option value="0" <?= empty($championship['registrations_open']) ? 'selected' : '' ?>>Fechadas</option></select></div><div class="col-md-5"><label class="form-label">Endereco</label><input class="form-control" name="address" value="<?= e($championship['address'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Bairro</label><input class="form-control" name="neighborhood" value="<?= e($championship['neighborhood'] ?? '') ?>"></div><div class="col-md-2"><label class="form-label">Estado</label><input class="form-control" name="state" maxlength="2" value="<?= e($championship['state'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">CEP</label><input class="form-control" name="zip_code" value="<?= e($championship['zip_code'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Referencia</label><input class="form-control" name="reference_point" value="<?= e($championship['reference_point'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Campo/quadra/pista</label><input class="form-control" name="court_or_field" value="<?= e($championship['court_or_field'] ?? '') ?>"></div><div class="col-12"><button class="btn btn-primary">Salvar configuracoes</button></div></form>
        </div>
    </div>
</section>
