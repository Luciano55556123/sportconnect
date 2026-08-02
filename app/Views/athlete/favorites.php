<section class="page-band"><div class="container"><h1>Meus favoritos</h1></div></section>
<section class="container py-4"><div class="row g-4"><?php foreach ($favorites as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?><?php if (!$favorites): ?><p class="text-muted">Voce ainda nao salvou campeonatos.</p><?php endif; ?></div></section>
