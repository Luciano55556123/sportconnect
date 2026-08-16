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
                <th>GP</th>
                <th>GC</th>
                <th>SG</th>
                <th>PTS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($standings ?? []) as $index => $row): ?>
                <?php
                $positionClass = $index < 2 ? 'is-qualified' : ($index >= max(0, count($standings) - 2) ? 'is-risk' : '');
                $name = (string) (($row['team_name'] ?? '') ?: ($row['athlete_name'] ?? 'Competidor'));
                ?>
                <tr class="<?= e($positionClass) ?>">
                    <td><span class="sc-position"><?= $index + 1 ?></span></td>
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
</div>
