<?php

namespace App\Services;

class QrCodeService
{
    private const VERSION = 10;
    private const SIZE = 57;
    private const DATA_CODEWORDS = 274;
    private const ECC_CODEWORDS = 18;
    private const BLOCKS = 4;

    private array $modules = [];
    private array $reserved = [];

    public function svg(string $text, int $scale = 4, int $border = 4): string
    {
        $matrix = $this->matrix($text);
        $size = self::SIZE;
        $dimension = ($size + ($border * 2)) * $scale;
        $paths = [];

        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                if ($matrix[$row][$col]) {
                    $x = ($col + $border) * $scale;
                    $y = ($row + $border) * $scale;
                    $paths[] = "M{$x},{$y}h{$scale}v{$scale}h-{$scale}z";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $dimension . '" height="' . $dimension . '" viewBox="0 0 ' . $dimension . ' ' . $dimension . '" role="img" aria-label="QR Code PIX">'
            . '<rect width="100%" height="100%" fill="#fff"/>'
            . '<path fill="#111827" d="' . implode('', $paths) . '"/>'
            . '</svg>';
    }

    public function dataUri(string $text): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svg($text));
    }

    public function matrix(string $text): array
    {
        $this->modules = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));
        $this->reserved = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));

        $this->drawFunctionPatterns();
        $codewords = $this->addErrorCorrection($this->dataCodewords($text));
        $this->drawCodewords($codewords);
        $this->applyMask0();
        $this->drawFormatBits();
        $this->drawVersionBits();

        return $this->modules;
    }

    private function dataCodewords(string $text): array
    {
        $bytes = array_values(unpack('C*', $text) ?: []);
        if (count($bytes) > 220) {
            throw new \InvalidArgumentException('Payload PIX muito longo para o QR Code configurado.');
        }

        $bits = [0, 1, 0, 0];
        $length = count($bytes);
        for ($i = 15; $i >= 0; $i--) {
            $bits[] = ($length >> $i) & 1;
        }

        foreach ($bytes as $byte) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($byte >> $i) & 1;
            }
        }

        $capacity = self::DATA_CODEWORDS * 8;
        for ($i = 0; $i < 4 && count($bits) < $capacity; $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];
        foreach (array_chunk($bits, 8) as $chunk) {
            $value = 0;
            foreach ($chunk as $bit) {
                $value = ($value << 1) | $bit;
            }
            $codewords[] = $value;
        }

        for ($pad = 0xEC; count($codewords) < self::DATA_CODEWORDS; $pad ^= 0xFD) {
            $codewords[] = $pad;
        }

        return $codewords;
    }

    private function addErrorCorrection(array $data): array
    {
        $blocks = [];
        $offset = 0;
        $shortBlocks = 2;
        for ($i = 0; $i < self::BLOCKS; $i++) {
            $length = $i < $shortBlocks ? 68 : 69;
            $block = array_slice($data, $offset, $length);
            $offset += $length;
            $blocks[] = ['data' => $block, 'ecc' => $this->reedSolomonRemainder($block, self::ECC_CODEWORDS)];
        }

        $result = [];
        for ($i = 0; $i < 69; $i++) {
            foreach ($blocks as $block) {
                if ($i < count($block['data'])) {
                    $result[] = $block['data'][$i];
                }
            }
        }

        for ($i = 0; $i < self::ECC_CODEWORDS; $i++) {
            foreach ($blocks as $block) {
                $result[] = $block['ecc'][$i];
            }
        }

        return $result;
    }

    private function reedSolomonRemainder(array $data, int $degree): array
    {
        $generator = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($generator) + 1, 0);
            foreach ($generator as $j => $coef) {
                $next[$j] ^= $this->gfMultiply($coef, 1);
                $next[$j + 1] ^= $this->gfMultiply($coef, $this->gfPow(2, $i));
            }
            $generator = $next;
        }

        $result = array_fill(0, $degree, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $result[0];
            array_shift($result);
            $result[] = 0;
            for ($i = 0; $i < $degree; $i++) {
                $result[$i] ^= $this->gfMultiply($generator[$i + 1], $factor);
            }
        }

        return $result;
    }

    private function gfMultiply(int $x, int $y): int
    {
        $result = 0;
        while ($y > 0) {
            if (($y & 1) !== 0) {
                $result ^= $x;
            }
            $x <<= 1;
            if (($x & 0x100) !== 0) {
                $x ^= 0x11D;
            }
            $y >>= 1;
        }
        return $result & 0xFF;
    }

    private function gfPow(int $x, int $power): int
    {
        $result = 1;
        while ($power-- > 0) {
            $result = $this->gfMultiply($result, $x);
        }
        return $result;
    }

    private function drawFunctionPatterns(): void
    {
        $this->drawFinder(0, 0);
        $this->drawFinder(self::SIZE - 7, 0);
        $this->drawFinder(0, self::SIZE - 7);

        for ($i = 0; $i < self::SIZE; $i++) {
            $this->setFunction(6, $i, $i % 2 === 0);
            $this->setFunction($i, 6, $i % 2 === 0);
        }

        foreach ([6, 28, 50] as $row) {
            foreach ([6, 28, 50] as $col) {
                if (($row === 6 && $col === 6) || ($row === 6 && $col === 50) || ($row === 50 && $col === 6)) {
                    continue;
                }
                $this->drawAlignment($col, $row);
            }
        }

        $this->setFunction(8, 49, true);
        for ($i = 0; $i < 9; $i++) {
            $this->reserve(8, $i);
            $this->reserve($i, 8);
            $this->reserve(8, self::SIZE - 1 - $i);
            $this->reserve(self::SIZE - 1 - $i, 8);
        }

        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $this->reserve($i, self::SIZE - 11 + $j);
                $this->reserve(self::SIZE - 11 + $j, $i);
            }
        }
    }

    private function drawFinder(int $x, int $y): void
    {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $row = $y + $dy;
                $col = $x + $dx;
                if ($row < 0 || $row >= self::SIZE || $col < 0 || $col >= self::SIZE) {
                    continue;
                }
                $dark = $dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6
                    && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
                $this->setFunction($row, $col, $dark);
            }
        }
    }

    private function drawAlignment(int $x, int $y): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $dark = max(abs($dx), abs($dy)) !== 1;
                $this->setFunction($y + $dy, $x + $dx, $dark);
            }
        }
    }

    private function drawCodewords(array $codewords): void
    {
        $bits = [];
        foreach ($codewords as $byte) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = (($byte >> $i) & 1) !== 0;
            }
        }

        $index = 0;
        $upward = true;
        for ($right = self::SIZE - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }
            for ($vert = 0; $vert < self::SIZE; $vert++) {
                $row = $upward ? self::SIZE - 1 - $vert : $vert;
                for ($j = 0; $j < 2; $j++) {
                    $col = $right - $j;
                    if (!$this->reserved[$row][$col]) {
                        $this->modules[$row][$col] = $bits[$index++] ?? false;
                    }
                }
            }
            $upward = !$upward;
        }
    }

    private function applyMask0(): void
    {
        for ($row = 0; $row < self::SIZE; $row++) {
            for ($col = 0; $col < self::SIZE; $col++) {
                if (!$this->reserved[$row][$col] && (($row + $col) % 2 === 0)) {
                    $this->modules[$row][$col] = !$this->modules[$row][$col];
                }
            }
        }
    }

    private function drawFormatBits(): void
    {
        $bits = $this->formatBits(1, 0);
        for ($i = 0; $i <= 5; $i++) {
            $this->setFunction(8, $i, (($bits >> $i) & 1) !== 0);
        }
        $this->setFunction(8, 7, (($bits >> 6) & 1) !== 0);
        $this->setFunction(8, 8, (($bits >> 7) & 1) !== 0);
        $this->setFunction(7, 8, (($bits >> 8) & 1) !== 0);
        for ($i = 9; $i < 15; $i++) {
            $this->setFunction(14 - $i, 8, (($bits >> $i) & 1) !== 0);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->setFunction(self::SIZE - 1 - $i, 8, (($bits >> $i) & 1) !== 0);
        }
        for ($i = 8; $i < 15; $i++) {
            $this->setFunction(8, self::SIZE - 15 + $i, (($bits >> $i) & 1) !== 0);
        }
        $this->setFunction(8, self::SIZE - 8, true);
    }

    private function formatBits(int $ecl, int $mask): int
    {
        $data = ($ecl << 3) | $mask;
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 9) * 0x537);
        }
        return (($data << 10) | $rem) ^ 0x5412;
    }

    private function drawVersionBits(): void
    {
        $rem = self::VERSION;
        for ($i = 0; $i < 12; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 11) * 0x1F25);
        }
        $bits = (self::VERSION << 12) | $rem;
        for ($i = 0; $i < 18; $i++) {
            $bit = (($bits >> $i) & 1) !== 0;
            $a = self::SIZE - 11 + ($i % 3);
            $b = intdiv($i, 3);
            $this->setFunction($b, $a, $bit);
            $this->setFunction($a, $b, $bit);
        }
    }

    private function setFunction(int $row, int $col, bool $dark): void
    {
        $this->modules[$row][$col] = $dark;
        $this->reserve($row, $col);
    }

    private function reserve(int $row, int $col): void
    {
        if ($row >= 0 && $row < self::SIZE && $col >= 0 && $col < self::SIZE) {
            $this->reserved[$row][$col] = true;
        }
    }
}
