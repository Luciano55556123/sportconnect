<?php

namespace App\Models;

class Recommendation extends Model
{
    public function forUser(array $user): array
    {
        $championships = (new Championship())->search(['status' => 'ativo'], 100);
        $favoriteSports = (new User())->favoriteSportIds((int) $user['id']);
        $history = (new Registration())->byUser((int) $user['id']);
        $favoriteEvents = (new Favorite())->byUser((int) $user['id']);
        $historySports = array_unique(array_column($history, 'sport_name'));
        $favoriteCategories = array_unique(array_filter(array_column($favoriteEvents, 'category')));

        $ranked = [];
        foreach ($championships as $event) {
            $score = 20;
            $reasons = [];

            if (in_array((int) $event['sport_id'], $favoriteSports, true)) {
                $score += 25;
                $reasons[] = 'Esporte favorito';
            }
            if (!empty($user['city']) && mb_strtolower($event['city']) === mb_strtolower($user['city'])) {
                $score += 20;
                $reasons[] = 'Cidade compativel';
            }
            if (($user['preferred_price_max'] ?? null) !== null && (float) $event['registration_fee'] <= (float) $user['preferred_price_max']) {
                $score += 15;
                $reasons[] = 'Valor dentro da preferencia';
            }
            if (in_array($event['sport_name'], $historySports, true)) {
                $score += 15;
                $reasons[] = 'Ja participou de eventos semelhantes';
            }
            if (in_array($event['category'], $favoriteCategories, true)) {
                $score += 10;
                $reasons[] = 'Categoria semelhante aos favoritos';
            }

            $event['compatibility'] = min(99, $score);
            $event['reasons'] = $reasons ?: ['Evento ativo na regiao'];
            $ranked[] = $event;
        }

        usort($ranked, fn ($a, $b) => $b['compatibility'] <=> $a['compatibility']);
        return array_slice($ranked, 0, 12);
    }
}
