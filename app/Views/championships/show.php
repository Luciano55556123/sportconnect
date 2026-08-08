<section class="event-detail">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <img class="detail-img" src="<?= url($championship['image']) ?>" alt="">
                <h1 class="mt-4"><?= e($championship['name']) ?></h1>
                <p class="lead"><?= e($championship['description']) ?></p>
                <div class="info-grid">
                    <span><i class="fa-solid fa-city"></i><?= e($championship['city']) ?></span>
                    <span><i class="fa-solid fa-map-pin"></i><?= e($championship['location']) ?></span>
                    <span><i class="fa-solid fa-calendar"></i><?= e(date('d/m/Y', strtotime($championship['event_date']))) ?></span>
                    <span><i class="fa-solid fa-clock"></i><?= e(substr($championship['event_time'], 0, 5)) ?></span>
                    <span><i class="fa-solid fa-users"></i><?= (int) $championship['registrations_count'] ?> / <?= (int) $championship['max_participants'] ?></span>
                    <span><i class="fa-solid fa-award"></i><?= e($championship['prize']) ?></span>
                </div>
                <?php if ($championship['map_link']): ?><a class="btn btn-outline-secondary mt-3" target="_blank" href="<?= e($championship['map_link']) ?>"><i class="fa-solid fa-map-location-dot"></i> Abrir mapa</a><?php endif; ?>
                <?php if ($championship['rules_file']): ?><a class="btn btn-outline-secondary mt-3" href="<?= url($championship['rules_file']) ?>"><i class="fa-solid fa-file-pdf"></i> Regulamento</a><?php endif; ?>
                <hr>
                <h2>Comentarios e avaliacoes</h2>
                <?php foreach ($reviews as $review): ?><div class="comment"><strong><?= e($review['name']) ?></strong><span><?= str_repeat('★', (int) $review['rating']) ?></span><p><?= e($review['comment']) ?></p></div><?php endforeach; ?>
            </div>
            <div class="col-lg-5">
                <div class="side-panel">
                    <div class="d-flex justify-content-between align-items-center"><span>Inscricao</span><strong>R$ <?= number_format((float) $championship['registration_fee'], 2, ',', '.') ?></strong></div>
                    <?php if (!empty($championship['requires_payment']) || (float) ($championship['registration_fee'] ?? 0) > 0): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            Pagamento via PIX apos a inscricao. Status inicial: aguardando pagamento.
                        </div>
                    <?php endif; ?>
                    <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/favoritar') ?>" class="my-3"><?= csrf_field() ?><button class="btn btn-outline-danger w-100"><i class="fa-solid fa-heart"></i> Favoritar</button></form>
                    <button class="btn btn-warning w-100 mb-3" data-bs-toggle="collapse" data-bs-target="#registerForm"><i class="fa-solid fa-user-plus"></i> Participar</button>
                    <a class="btn btn-success w-100 mb-3" target="_blank" href="https://wa.me/55<?= preg_replace('/\D/', '', $championship['organizer_phone'] ?? '') ?>"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                    <button class="btn btn-outline-primary w-100" onclick="navigator.share ? navigator.share({title: document.title, url: location.href}) : navigator.clipboard.writeText(location.href)"><i class="fa-solid fa-share-nodes"></i> Compartilhar</button>
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
                            <label class="form-label">Documento opcional</label><input class="form-control" type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png">
                            <button class="btn btn-primary">Enviar inscricao</button>
                        </form>
                    </div>
                    <hr>
                    <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/avaliar') ?>" class="stack-form">
                        <?= csrf_field() ?><select class="form-select" name="rating"><option value="5">5 estrelas</option><option value="4">4 estrelas</option><option value="3">3 estrelas</option></select><textarea class="form-control" name="comment" placeholder="Comentario"></textarea><button class="btn btn-outline-primary">Avaliar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
