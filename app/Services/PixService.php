<?php

namespace App\Services;

class PixService
{
    public function payload(array $data): string
    {
        $key = $this->normalizeKey((string) ($data['pix_key'] ?? ''), (string) ($data['pix_key_type'] ?? ''));
        $amount = number_format((float) ($data['amount'] ?? 0), 2, '.', '');
        $name = $this->emvText((string) ($data['pix_holder_name'] ?? ''), 25);
        $city = $this->emvText((string) ($data['pix_receiver_city'] ?? $data['city'] ?? ''), 15);
        $txid = $this->emvText((string) ($data['txid'] ?? 'PONTOCOMPETITIVO'), 25, true);

        $merchantAccount = $this->field('00', 'br.gov.bcb.pix')
            . $this->field('01', $key);

        $payload = $this->field('00', '01')
            . $this->field('01', '12')
            . $this->field('26', $merchantAccount)
            . $this->field('52', '0000')
            . $this->field('53', '986')
            . $this->field('54', $amount)
            . $this->field('58', 'BR')
            . $this->field('59', $name)
            . $this->field('60', $city)
            . $this->field('62', $this->field('05', $txid))
            . '6304';

        return $payload . $this->crc16($payload);
    }

    public function crc16(string $payload): string
    {
        $crc = 0xFFFF;
        $length = strlen($payload);

        for ($offset = 0; $offset < $length; $offset++) {
            $crc ^= ord($payload[$offset]) << 8;
            for ($bit = 0; $bit < 8; $bit++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    public function normalizeKey(string $key, string $type): string
    {
        $key = trim($key);
        if (in_array($type, ['cpf', 'cnpj'], true)) {
            return preg_replace('/\D/', '', $key);
        }

        if ($type === 'telefone') {
            $digits = preg_replace('/\D/', '', $key);
            if (str_starts_with($digits, '55')) {
                return '+' . $digits;
            }
            return '+55' . $digits;
        }

        return $key;
    }

    public function validatePixData(array $data): array
    {
        $errors = [];
        $type = (string) ($data['pix_key_type'] ?? '');
        $key = trim((string) ($data['pix_key'] ?? ''));
        $holder = trim((string) ($data['pix_holder_name'] ?? ''));
        $city = trim((string) ($data['pix_receiver_city'] ?? ''));
        $amount = (float) ($data['registration_fee'] ?? 0);

        if ($amount <= 0) {
            $errors[] = 'Informe um valor de inscricao maior que zero para campeonatos pagos.';
        }

        if (!in_array($type, ['cpf', 'cnpj', 'email', 'telefone', 'aleatoria'], true)) {
            $errors[] = 'Selecione um tipo de chave PIX valido.';
        }

        if ($key === '') {
            $errors[] = 'Informe a chave PIX.';
        } elseif (strlen($key) > 77) {
            $errors[] = 'A chave PIX deve ter no maximo 77 caracteres.';
        } elseif ($type === 'cpf' && strlen(preg_replace('/\D/', '', $key)) !== 11) {
            $errors[] = 'Informe uma chave PIX CPF com 11 digitos.';
        } elseif ($type === 'cnpj' && strlen(preg_replace('/\D/', '', $key)) !== 14) {
            $errors[] = 'Informe uma chave PIX CNPJ com 14 digitos.';
        } elseif ($type === 'email' && !filter_var($key, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe uma chave PIX de e-mail valida.';
        } elseif ($type === 'telefone' && strlen(preg_replace('/\D/', '', $key)) < 10) {
            $errors[] = 'Informe uma chave PIX de telefone valida.';
        }

        if ($holder === '') {
            $errors[] = 'Informe o nome do recebedor PIX.';
        }

        if ($city === '') {
            $errors[] = 'Informe a cidade do recebedor PIX.';
        }

        return $errors;
    }

    private function field(string $id, string $value): string
    {
        return $id . str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    private function emvText(string $text, int $limit, bool $allowSymbols = false): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $pattern = $allowSymbols ? '/[^A-Za-z0-9 ._-]/' : '/[^A-Za-z0-9 ]/';
        $text = strtoupper(trim(preg_replace($pattern, '', $text) ?? ''));
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return substr($text, 0, $limit) ?: 'PONTOCOMPETITIVO';
    }
}
