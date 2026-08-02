<?php
$whatsappDigits = preg_replace('/\D+/', '', $championship['whatsapp_contato'] ?? '') ?? '';
$whatsappMessage = urlencode('Ola! Encontrei o campeonato ' . ($championship['name'] ?? '') . ' no SportConnect e gostaria de mais informacoes.');
$whatsappUrl = $whatsappDigits !== '' ? 'https://wa.me/55' . $whatsappDigits . '?text=' . $whatsappMessage : '';
$addressParts = array_filter([
    $championship['address'] ?? null,
    $championship['neighborhood'] ?? null,
    $championship['city'] ?? null,
    $championship['state'] ?? null,
    $championship['zip_code'] ?? null,
]);
$mapsQuery = urlencode(implode(', ', $addressParts) ?: (($championship['location'] ?? '') . ', ' . ($championship['city'] ?? '')));
$generatedMapUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery;
$fmtDate = static fn ($date) => $date ? date('d/m/Y', strtotime($date)) : 'Nao informado';
$empty = static fn ($value, string $fallback = 'Nao informado') => ($value !== null && $value !== '') ? $value : $fallback;
$playedMatches = count(array_filter($matches, static fn ($match) => ($match['status'] ?? '') === 'finalizada'));
$totalMatches = count($matches);
$progressPercent = $totalMatches > 0 ? min(100, (int) round(($playedMatches / $totalMatches) * 100)) : 0;
?>
<section class="public-event-hero" style="background-image: linear-gradient(90deg, rgba(15,39,71,.92), rgba(18,103,216,.62)), url('<?= e(championship_image_url($championship)) ?>')">
    <div class="container">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8">
                <span class="badge text-bg-warning mb-3"><?= e($championship['status'] ?? 'ativo') ?></span>
                <h1><?= e($championship['name']) ?></h1>
                <p class="lead"><?= e($championship['sport_name'] ?? '') ?> em <?= e($championship['city'] ?? '') ?> · <?= e($fmtDate($championship['event_date'] ?? null)) ?></p>
                <p><?= e($championship['description']) ?></p>
                <div class="public-hero-metrics">
                    <span><strong><?= (int) ($championship['registrations_count'] ?? 0) ?></strong> inscritos</span>
                    <span><strong><?= (int) ($championship['max_participants'] ?? 0) ?></strong> vagas</span>
                    <span><strong>R$ <?= number_format((float) $championship['registration_fee'], 2, ',', '.') ?></strong> inscricao</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="side-panel public-actions">
                    <div class="d-flex justify-content-between align-items-center"><span>Inscricao</span><strong>R$ <?= number_format((float) $championship['registration_fee'], 2, ',', '.') ?></strong></div>
                    <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/favoritar') ?>" class="my-3"><?= csrf_field() ?><button class="btn btn-outline-danger w-100"><i class="fa-solid fa-heart"></i> Favoritar</button></form>
                    <button class="btn btn-warning w-100 mb-3" data-bs-toggle="collapse" data-bs-target="#registerForm"><i class="fa-solid fa-user-plus"></i> Participar</button>
                    <?php if ($whatsappUrl): ?><a class="btn btn-success w-100 mb-3" target="_blank" rel="noopener" href="<?= e($whatsappUrl) ?>"><i class="fa-brands fa-whatsapp"></i> WhatsApp do organizador</a><?php endif; ?>
                    <button class="btn btn-outline-primary w-100" type="button" data-share-current><i class="fa-solid fa-share-nodes"></i> Compartilhar</button>
                    <?php if ($currentUser): ?><button class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="collapse" data-bs-target="#reportForm"><i class="fa-solid fa-flag"></i> Denunciar</button><?php endif; ?>
                    <div class="collapse mt-3" id="registerForm">
                        <form method="post" enctype="multipart/form-data" action="<?= url('/campeonatos/' . $championship['id'] . '/inscrever') ?>" class="stack-form">
                            <?= csrf_field() ?>
                            <input class="form-control" name="name" required placeholder="Nome">
                            <input class="form-control" name="phone" required placeholder="Telefone">
                            <input class="form-control" type="email" name="email" required placeholder="Email">
                            <input class="form-control" name="team" placeholder="Equipe">
                            <input class="form-control" name="category" placeholder="Categoria">
                            <input class="form-control" name="city" required placeholder="Cidade">
                            <input class="form-control" name="cpf" placeholder="CPF opcional">
                            <textarea class="form-control" name="notes" placeholder="Observacoes"></textarea>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="accepted_terms" value="1" required> <span class="form-check-label">Li e aceito o regulamento e os termos do campeonato.</span></label>
                            <label class="form-label">Comprovante</label><input class="form-control" type="file" name="proof_file">
                            <button class="btn btn-primary">Enviar inscricao</button>
                        </form>
                    </div>
                    <?php if ($currentUser): ?><div class="collapse mt-3" id="reportForm"><form class="stack-form" method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/denunciar') ?>"><?= csrf_field() ?><select class="form-select" name="report_type"><option value="incorrect_information">Informacao incorreta</option><option value="improper_charge">Cobranca indevida</option><option value="incorrect_result">Resultado incorreto</option><option value="fraud">Fraude</option><option value="other">Outro</option></select><input class="form-control" name="title" required placeholder="Titulo"><textarea class="form-control" name="description" required placeholder="Descreva o problema"></textarea><button class="btn btn-danger">Enviar denuncia</button></form></div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="event-detail">
    <div class="container">

        <div class="competition-tabs mt-4">
            <ul class="nav nav-pills flex-nowrap" role="tablist">
                <?php foreach ([
                    'geral' => 'Visao geral',
                    'jogos' => 'Jogos',
                    'classificacao' => 'Classificacao',
                    'chaveamento' => 'Chaveamento',
                    'artilharia' => 'Artilharia',
                    'participantes' => 'Participantes',
                    'regulamento' => 'Regulamento',
                    'localizacao' => 'Localizacao',
                ] as $key => $label): ?>
                    <li class="nav-item" role="presentation"><button class="nav-link <?= $key === 'geral' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tab-<?= $key ?>" type="button"><?= e($label) ?></button></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="tab-geral">
                <div class="championship-overview-dashboard">
                    <div class="overview-main-card">
                        <div class="section-heading mb-0">
                            <div>
                                <h2>Visao geral</h2>
                                <span><?= e($empty($championship['competition_format'] ?? null, 'Formato nao informado')) ?> em <?= e($empty($championship['city'] ?? null)) ?></span>
                            </div>
                            <span class="status-chip"><?= e($championship['status'] ?? 'ativo') ?></span>
                        </div>
                        <p class="overview-description"><?= e($championship['description']) ?></p>
                        <div class="overview-progress">
                            <div class="d-flex justify-content-between">
                                <strong>Progresso da competicao</strong>
                                <span><?= $playedMatches ?> de <?= $totalMatches ?> jogos</span>
                            </div>
                            <div class="progress"><span style="width: <?= $progressPercent ?>%"></span></div>
                        </div>
                        <div class="info-grid mt-3">
                            <span><i class="fa-solid fa-medal"></i><?= e($empty($championship['sport_name'] ?? null)) ?></span>
                            <span><i class="fa-solid fa-layer-group"></i><?= e($empty($championship['category'] ?? null)) ?></span>
                            <span><i class="fa-solid fa-people-group"></i><?= e($empty($championship['modality'] ?? null)) ?></span>
                            <span><i class="fa-solid fa-calendar"></i><?= e($fmtDate($championship['event_date'] ?? null)) ?></span>
                            <span><i class="fa-solid fa-flag-checkered"></i><?= e($fmtDate($championship['end_date'] ?? null)) ?></span>
                            <span><i class="fa-solid fa-door-open"></i><?= !empty($championship['registrations_open']) ? 'Inscricoes abertas' : 'Inscricoes fechadas' ?></span>
                            <span><i class="fa-solid fa-user-tie"></i><?= e($empty($championship['organizer_name'] ?? null)) ?></span>
                            <span><i class="fa-brands fa-whatsapp"></i><?= e($empty($championship['whatsapp_contato'] ?? null)) ?></span>
                        </div>
                    </div>
                    <div class="overview-side-stack">
                        <div class="overview-stat-card"><i class="fa-solid fa-users"></i><strong><?= (int) ($championship['registrations_count'] ?? 0) ?></strong><span>inscritos</span></div>
                        <div class="overview-stat-card"><i class="fa-solid fa-table-list"></i><strong><?= $totalMatches ?></strong><span>jogos</span></div>
                        <div class="overview-stat-card"><i class="fa-solid fa-award"></i><strong><?= e($empty($championship['prize'] ?? null, '-')) ?></strong><span>premiacao</span></div>
                    </div>
                    <div class="overview-chart-card">
                        <h2>Leitura rapida</h2>
                        <canvas id="publicOverviewChart" data-played="<?= $playedMatches ?>" data-pending="<?= max(0, $totalMatches - $playedMatches) ?>"></canvas>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-jogos">
                <?php
                $statusKey = static function (string $status): string {
                    return match ($status) {
                        'agendada' => 'scheduled',
                        'em_andamento' => 'live',
                        'finalizada' => 'finished',
                        'adiada' => 'postponed',
                        'cancelada' => 'cancelled',
                        default => 'scheduled',
                    };
                };
                $statusLabel = static function (string $status): string {
                    return match ($status) {
                        'agendada' => 'Agendada',
                        'em_andamento' => 'Em andamento',
                        'finalizada' => 'Finalizada',
                        'adiada' => 'Adiada',
                        'cancelada' => 'Cancelada',
                        default => ucfirst(str_replace('_', ' ', $status)),
                    };
                };
                $phaseIcon = static function (?string $phase): array {
                    $slug = sport_slug((string) $phase);
                    if (str_contains($slug, 'semifinal')) {
                        return ['phase-semifinal', 'fa-medal'];
                    }
                    if (str_contains($slug, 'final')) {
                        return ['phase-final', 'fa-trophy'];
                    }
                    if (str_contains($slug, 'quartas')) {
                        return ['phase-quarter', 'fa-bullseye'];
                    }
                    if (str_contains($slug, 'oitavas')) {
                        return ['phase-round16', 'fa-circle-nodes'];
                    }
                    if (str_contains($slug, 'grupo')) {
                        return ['phase-group', 'fa-people-group'];
                    }
                    return ['phase-default', 'fa-calendar-days'];
                };
                $phaseTitle = static function (array $match): string {
                    $phase = trim((string) ($match['phase'] ?? 'Fase'));
                    $group = trim((string) ($match['group_name'] ?? ''));
                    return $group !== '' ? $phase . ' ' . $group : $phase;
                };
                $statusCounts = ['all' => count($matches), 'scheduled' => 0, 'live' => 0, 'finished' => 0];
                $phases = [];
                foreach ($matches as $match) {
                    $key = $statusKey((string) ($match['status'] ?? ''));
                    if (isset($statusCounts[$key])) {
                        $statusCounts[$key]++;
                    }
                    $phase = $phaseTitle($match);
                    if ($phase !== '') {
                        $phases[$phase] = $phase;
                    }
                }
                ?>
                <section class="championship-games-section" data-games-section>
                    <div class="games-section-header">
                        <div class="games-title-block">
                            <span class="games-header-icon" aria-hidden="true"><i class="fa-solid fa-calendar-days"></i></span>
                            <div>
                                <h2>Jogos</h2>
                                <p>Confira todos os jogos do campeonato</p>
                            </div>
                        </div>
                        <?php if ($matches): ?>
                            <div class="games-filter-bar" aria-label="Filtros de jogos">
                                <label class="visually-hidden" for="gamesPhaseFilter">Todas as fases</label>
                                <select id="gamesPhaseFilter" class="form-select games-phase-select" data-games-phase-filter>
                                    <option value="">Todas as fases</option>
                                    <?php foreach ($phases as $phase): ?>
                                        <option value="<?= e($phase) ?>"><?= e($phase) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="games-filter-button active" type="button" data-games-status-filter="all" aria-pressed="true">Todos <span><?= (int) $statusCounts['all'] ?></span></button>
                                <button class="games-filter-button" type="button" data-games-status-filter="scheduled" aria-pressed="false">Agendados <span><?= (int) $statusCounts['scheduled'] ?></span></button>
                                <button class="games-filter-button" type="button" data-games-status-filter="live" aria-pressed="false">Em andamento <span><?= (int) $statusCounts['live'] ?></span></button>
                                <button class="games-filter-button" type="button" data-games-status-filter="finished" aria-pressed="false">Finalizados <span><?= (int) $statusCounts['finished'] ?></span></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$matches): ?>
                        <div class="games-empty-state">
                            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                            <h3>Nenhum jogo cadastrado</h3>
                            <p>As partidas deste campeonato ainda nao foram definidas.</p>
                        </div>
                    <?php else: ?>
                        <div class="games-table-header" aria-hidden="true">
                            <span>Fase</span>
                            <span>Rodada</span>
                            <span>Data e hora</span>
                            <span>Confronto</span>
                            <span>Local</span>
                            <span>Status</span>
                            <span>Acoes</span>
                        </div>

                        <div class="games-list" data-games-list>
                            <?php foreach ($matches as $match): ?>
                                <?php
                                $home = $match['home_team_name'] ?? $match['home_athlete_name'] ?? 'A definir';
                                $away = $match['away_team_name'] ?? $match['away_athlete_name'] ?? 'A definir';
                                $homeScore = $match['home_score'] !== null ? (string) (int) $match['home_score'] : '-';
                                $awayScore = $match['away_score'] !== null ? (string) (int) $match['away_score'] : '-';
                                $phase = $phaseTitle($match);
                                [$phaseClass, $phaseIconClass] = $phaseIcon($match['phase'] ?? '');
                                $status = (string) ($match['status'] ?? 'agendada');
                                $statusClass = 'status-' . $statusKey($status);
                                $statusIcon = match ($statusKey($status)) {
                                    'finished' => 'fa-circle-check',
                                    'live' => 'fa-circle',
                                    'postponed' => 'fa-triangle-exclamation',
                                    'cancelled' => 'fa-circle-xmark',
                                    default => 'fa-calendar-days',
                                };
                                ?>
                                <article class="game-row-card"
                                         data-game-card
                                         data-phase="<?= e($phase) ?>"
                                         data-status="<?= e($statusKey($status)) ?>">
                                    <div class="game-phase-cell">
                                        <span class="game-phase-icon <?= e($phaseClass) ?>" aria-hidden="true"><i class="fa-solid <?= e($phaseIconClass) ?>"></i></span>
                                        <strong><?= e($phase) ?></strong>
                                    </div>
                                    <div class="game-round-cell">
                                        <span class="game-round-badge"><?= e((string) ($match['round_number'] ?? '-')) ?></span>
                                    </div>
                                    <div class="game-datetime">
                                        <span><i class="fa-solid fa-calendar-days" aria-hidden="true"></i><?= e($fmtDate($match['match_date'] ?? null)) ?></span>
                                        <span><i class="fa-solid fa-clock" aria-hidden="true"></i><?= e(!empty($match['match_time']) ? substr($match['match_time'], 0, 5) : 'Horario a definir') ?></span>
                                    </div>
                                    <div class="game-matchup">
                                        <div class="game-team game-team-home">
                                            <span><?= e($home) ?></span>
                                            <img class="game-team-shield" src="<?= e(team_shield_url(['name' => $home])) ?>" alt="Escudo de <?= e($home) ?>">
                                        </div>
                                        <div class="game-score-box"><strong><?= e($homeScore) ?></strong><span>x</span><strong><?= e($awayScore) ?></strong></div>
                                        <div class="game-team game-team-away">
                                            <img class="game-team-shield" src="<?= e(team_shield_url(['name' => $away])) ?>" alt="Escudo de <?= e($away) ?>">
                                            <span><?= e($away) ?></span>
                                        </div>
                                    </div>
                                    <div class="game-location">
                                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                        <div>
                                            <strong><?= e($empty($match['venue'] ?? null, 'Local a definir')) ?></strong>
                                            <?php if (!empty($match['court_or_field'])): ?><span><?= e($match['court_or_field']) ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="game-status-badge <?= e($statusClass) ?>"><i class="fa-solid <?= e($statusIcon) ?>" aria-hidden="true"></i><?= e($statusLabel($status)) ?></span>
                                    </div>
                                    <div class="game-actions">
                                        <a class="game-details-button" href="<?= url('/campeonatos/' . $championship['id'] . '/partidas/' . $match['id']) ?>">Detalhes <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <div class="games-empty-state d-none" data-games-filter-empty>
                            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                            <h3>Nenhum jogo encontrado</h3>
                            <p>Nao ha partidas para os filtros selecionados.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <div class="tab-pane fade" id="tab-classificacao">
                <div class="standings-premium table-responsive">
                    <?php if (!$standings): ?><p class="text-muted mb-0">Classificacao ainda nao disponivel.</p><?php else: ?>
                        <table class="table align-middle"><thead><tr><th>#</th><th>Participante</th><th>J</th><th>V</th><th>E</th><th>D</th><th>SG</th><th>PTS</th></tr></thead><tbody>
                            <?php foreach ($standings as $i => $row): ?><tr><td><span class="rank-badge"><?= $i + 1 ?></span></td><td><strong><?= e($row['team_name'] ?? $row['athlete_name'] ?? 'A definir') ?></strong><br><small><?= e($row['group_name'] ?? '') ?></small></td><td><?= (int) $row['played'] ?></td><td><?= (int) $row['wins'] ?></td><td><?= (int) $row['draws'] ?></td><td><?= (int) $row['losses'] ?></td><td><?= (int) $row['score_difference'] ?></td><td><strong><?= (int) $row['points'] ?></strong></td></tr><?php endforeach; ?>
                        </tbody></table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-chaveamento">
                <div class="bracket-scroll bracket-premium">
                    <?php if (!$matches): ?><div class="panel"><p class="text-muted mb-0">Chaveamento ainda nao definido.</p></div><?php else: ?>
                        <?php foreach (array_unique(array_column($matches, 'phase')) as $phase): ?>
                            <div class="bracket-column"><h3><?= e($phase) ?></h3><?php foreach ($matches as $match): ?><?php if ($match['phase'] !== $phase) continue; ?>
                                <div class="bracket-match"><span><?= e($match['home_team_name'] ?? $match['home_athlete_name'] ?? 'A definir') ?> <?= $match['home_score'] !== null ? (int) $match['home_score'] : '' ?></span><span><?= e($match['away_team_name'] ?? $match['away_athlete_name'] ?? 'A definir') ?> <?= $match['away_score'] !== null ? (int) $match['away_score'] : '' ?></span><small><?= e($match['status']) ?></small></div>
                            <?php endforeach; ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-artilharia">
                <div class="ranking-list">
                    <?php if (!$statistics): ?><p class="text-muted mb-0">Nenhuma estatistica registrada.</p><?php else: ?>
                        <?php foreach ($statistics as $i => $stat): ?><article class="ranking-row <?= $i < 3 ? 'is-podium' : '' ?>"><span class="rank-badge"><?= $i + 1 ?></span><div><strong><?= e($stat['athlete_name']) ?></strong><small><?= e($stat['team_name'] ?? 'Sem equipe') ?></small></div><div class="ranking-metrics"><span><strong><?= (int) $stat['goals'] ?></strong> gols</span><span><?= (int) $stat['yellow_cards'] ?> amarelos</span><span><?= (int) $stat['red_cards'] ?> vermelhos</span><span><?= (int) $stat['points'] ?> pts</span></div></article><?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-participantes">
                <div class="participant-grid">
                    <?php foreach ($teams as $team): ?><article class="participant-card team-participant"><div class="team-shield"><?= e(mb_strtoupper(mb_substr($team['name'], 0, 2))) ?></div><div><h2><?= e($team['name']) ?></h2><p><?= e($team['city'] ?? 'Cidade nao informada') ?></p><span><?= (int) $team['athletes_count'] ?> atletas · <?= e($team['status']) ?></span></div><a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . $championship['id'] . '/equipes/' . $team['id']) ?>">Ver elenco</a></article><?php endforeach; ?>
                    <?php foreach ($athletes as $athlete): ?><?php if ($athlete['team_id']) continue; ?><article class="participant-card"><div class="team-shield"><?= e(mb_strtoupper(mb_substr($athlete['name'], 0, 2))) ?></div><div><h2><?= e($athlete['name']) ?></h2><p><?= e($athlete['city'] ?? 'Cidade nao informada') ?></p><span><?= e($athlete['category'] ?? 'Categoria nao informada') ?> · <?= e($athlete['status']) ?></span></div><a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . $championship['id'] . '/atletas/' . $athlete['id']) ?>">Ver participante</a></article><?php endforeach; ?>
                    <?php if (!$teams && !$athletes): ?><div class="col-12"><div class="panel"><p class="text-muted mb-0">Nenhum participante aprovado.</p></div></div><?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-regulamento">
                <div class="panel">
                    <?php foreach ([
                        'Regulamento' => $championship['rules'] ?? $championship['description'] ?? null,
                        'Criterios de desempate' => $championship['tiebreak_rules'] ?? null,
                        'Classificacao' => $championship['qualification_rules'] ?? null,
                        'Eliminacao' => $championship['elimination_rules'] ?? null,
                        'Documentos' => $championship['required_documents'] ?? null,
                        'Cancelamento' => $championship['cancellation_policy'] ?? null,
                        'Premiacao' => $championship['prize'] ?? null,
                    ] as $label => $value): ?><h2 class="mt-3"><?= e($label) ?></h2><p><?= nl2br(e($empty($value, 'Informacao ainda nao cadastrada.'))) ?></p><?php endforeach; ?>
                    <?php if ($championship['rules_file']): ?><a class="btn btn-outline-secondary" href="<?= url($championship['rules_file']) ?>"><i class="fa-solid fa-file-pdf"></i> Abrir PDF</a><?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-localizacao">
                <div class="panel">
                    <div class="info-grid">
                        <span><i class="fa-solid fa-location-dot"></i><?= e($empty($championship['location'] ?? null)) ?></span>
                        <span><i class="fa-solid fa-road"></i><?= e($empty($championship['address'] ?? null)) ?></span>
                        <span><i class="fa-solid fa-map"></i><?= e($empty($championship['neighborhood'] ?? null)) ?></span>
                        <span><i class="fa-solid fa-city"></i><?= e($empty($championship['city'] ?? null)) ?> / <?= e($empty($championship['state'] ?? null, '--')) ?></span>
                        <span><i class="fa-solid fa-envelopes-bulk"></i><?= e($empty($championship['zip_code'] ?? null)) ?></span>
                        <span><i class="fa-solid fa-flag"></i><?= e($empty($championship['reference_point'] ?? null)) ?></span>
                        <span><i class="fa-solid fa-table-tennis-paddle-ball"></i><?= e($empty($championship['court_or_field'] ?? null)) ?></span>
                    </div>
                    <a class="btn btn-outline-secondary mt-3" target="_blank" rel="noopener" href="<?= e($championship['map_link'] ?: $generatedMapUrl) ?>"><i class="fa-solid fa-map-location-dot"></i> Abrir mapa</a>
                </div>
            </div>
        </div>

        <div class="panel mt-4">
            <h2>Comentarios e avaliacoes</h2>
            <?php foreach ($reviews as $review): ?><div class="comment"><strong><?= e($review['name']) ?></strong><span><?= str_repeat('*', (int) $review['rating']) ?></span><p><?= e($review['comment']) ?></p></div><?php endforeach; ?>
            <?php if (!$reviews): ?><p class="text-muted mb-0">Nenhum comentario publicado.</p><?php endif; ?>
            <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/avaliar') ?>" class="stack-form mt-3">
                <?= csrf_field() ?><select class="form-select" name="rating"><option value="5">5 estrelas</option><option value="4">4 estrelas</option><option value="3">3 estrelas</option></select><textarea class="form-control" name="comment" placeholder="Comentario"></textarea><button class="btn btn-outline-primary">Avaliar</button>
            </form>
        </div>
    </div>
</section>
