<div class="sc-table-wrap" role="region" aria-label="Tabela de classificacao" tabindex="0">
    <table class="table sc-standings-table align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Equipe</th>
                <th>J</th>
                <th>V</th>
                <th>E</th>
                <th>D</th>
                <th title="Placar a favor">PF</th>
                <th title="Placar contra">PC</th>
                <th title="Diferenca de placar">Saldo</th>
                <th>PTS</th>
            </tr>
        </thead>
        <tbody>
            <?php $previousGroup = null; $groupPosition = 0; ?>
            <?php foreach (($standings ?? []) as $row): ?>
                <?php
                $group = (string) ($row['group_name'] ?? '');
                if ($previousGroup !== $group) {
                    $previousGroup = $group;
                    $groupPosition = 0;
                }
                $groupPosition++;
                $name = (string) (($row['team_name'] ?? '') ?: ($row['athlete_name'] ?? 'Competidor'));
                ?>
                <tr>
                    <td><span class="sc-position"><?= $groupPosition ?></span></td>
                    <td>
                        <strong><?= e($name) ?></strong>
                        <?php if (!empty($row['group_name'])): ?><small>Grupo <?= e($row['group_name']) ?></small><?php endif; ?>
                    </td>
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
    <p class="small text-muted mb-0 px-2 pb-2">Classificação calculada com regra genérica: 3 pontos por vitória e 1 por empate. Critérios específicos da modalidade seguem o regulamento do campeonato.</p>
</div>
