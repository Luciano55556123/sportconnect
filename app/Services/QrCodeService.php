<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RuntimeException;

class QrCodeService
{
    private const LOW_ECC_DATA_CODEWORDS = [
        1 => 19,
        2 => 34,
        3 => 55,
        4 => 80,
        5 => 108,
        6 => 136,
        7 => 156,
        8 => 194,
        9 => 232,
    ];

    private const LOW_ECC_CODEWORDS_PER_BLOCK = [
        1 => 7,
        2 => 10,
        3 => 15,
        4 => 20,
        5 => 26,
        6 => 18,
        7 => 20,
        8 => 24,
        9 => 30,
    ];

    private const LOW_ECC_BLOCKS = [
        1 => 1,
        2 => 1,
        3 => 1,
        4 => 1,
        5 => 1,
        6 => 2,
        7 => 2,
        8 => 2,
        9 => 2,
    ];

    public function dataUri(string $pixPayload): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svg($pixPayload));
    }

    public function svg(string $pixPayload): string
    {
        $pixPayload = trim($pixPayload);

        if ($pixPayload === '') {
            throw new RuntimeException('Payload PIX vazio para QR Code.');
        }

        if (!class_exists(Writer::class)) {
            return $this->fallbackSvg($pixPayload);
        }

        $renderer = new ImageRenderer(
            new RendererStyle(350, 16),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString(
            $pixPayload,
            'UTF-8',
            ErrorCorrectionLevel::Q()
        );
    }

    private function fallbackSvg(string $text): string
    {
        $bytes = array_values(unpack('C*', $text) ?: []);
        $version = $this->versionForByteLength(count($bytes));
        $size = 17 + 4 * $version;
        $modules = array_fill(0, $size, array_fill(0, $size, false));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        $this->drawFunctionPatterns($modules, $reserved, $version);
        $codewords = $this->encodeCodewords($bytes, $version);
        $bits = [];
        foreach ($codewords as $codeword) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = (($codeword >> $i) & 1) === 1;
            }
        }

        $bitIndex = 0;
        $direction = -1;
        for ($right = $size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }
            for ($vertical = 0; $vertical < $size; $vertical++) {
                $row = $direction === 1 ? $vertical : $size - 1 - $vertical;
                for ($col = $right; $col >= $right - 1; $col--) {
                    if ($reserved[$row][$col]) {
                        continue;
                    }
                    $value = $bits[$bitIndex] ?? false;
                    if (($row + $col) % 2 === 0) {
                        $value = !$value;
                    }
                    $modules[$row][$col] = $value;
                    $bitIndex++;
                }
            }
            $direction *= -1;
        }

        $this->drawFormatBits($modules, $reserved);

        return $this->modulesToSvg($modules);
    }

    private function versionForByteLength(int $length): int
    {
        foreach (self::LOW_ECC_DATA_CODEWORDS as $version => $capacity) {
            $countBits = $version <= 9 ? 8 : 16;
            $neededBits = 4 + $countBits + ($length * 8);
            if ($neededBits <= $capacity * 8) {
                return $version;
            }
        }

        throw new RuntimeException('Payload PIX grande demais para o gerador de QR Code local.');
    }

    private function encodeCodewords(array $bytes, int $version): array
    {
        $capacity = self::LOW_ECC_DATA_CODEWORDS[$version];
        $dataBits = [false, true, false, false];
        $countBits = $version <= 9 ? 8 : 16;
        $length = count($bytes);
        for ($i = $countBits - 1; $i >= 0; $i--) {
            $dataBits[] = (($length >> $i) & 1) === 1;
        }
        foreach ($bytes as $byte) {
            for ($i = 7; $i >= 0; $i--) {
                $dataBits[] = (($byte >> $i) & 1) === 1;
            }
        }
        $terminator = min(4, ($capacity * 8) - count($dataBits));
        for ($i = 0; $i < $terminator; $i++) {
            $dataBits[] = false;
        }
        while (count($dataBits) % 8 !== 0) {
            $dataBits[] = false;
        }

        $dataCodewords = [];
        foreach (array_chunk($dataBits, 8) as $chunk) {
            $value = 0;
            foreach ($chunk as $bit) {
                $value = ($value << 1) | ($bit ? 1 : 0);
            }
            $dataCodewords[] = $value;
        }
        for ($pad = 0; count($dataCodewords) < $capacity; $pad++) {
            $dataCodewords[] = $pad % 2 === 0 ? 0xEC : 0x11;
        }

        $blocks = self::LOW_ECC_BLOCKS[$version];
        $ecLength = self::LOW_ECC_CODEWORDS_PER_BLOCK[$version];
        $dataBlocks = array_chunk($dataCodewords, (int) ($capacity / $blocks));
        $ecBlocks = array_map(fn(array $block): array => $this->reedSolomonRemainder($block, $ecLength), $dataBlocks);

        $result = [];
        $maxData = max(array_map('count', $dataBlocks));
        for ($i = 0; $i < $maxData; $i++) {
            foreach ($dataBlocks as $block) {
                if (isset($block[$i])) {
                    $result[] = $block[$i];
                }
            }
        }
        for ($i = 0; $i < $ecLength; $i++) {
            foreach ($ecBlocks as $block) {
                $result[] = $block[$i];
            }
        }

        return $result;
    }

    private function drawFunctionPatterns(array &$modules, array &$reserved, int $version): void
    {
        $size = count($modules);
        $this->drawFinder($modules, $reserved, 3, 3);
        $this->drawFinder($modules, $reserved, $size - 4, 3);
        $this->drawFinder($modules, $reserved, 3, $size - 4);

        for ($i = 0; $i < $size; $i++) {
            $this->setFunctionModule($modules, $reserved, 6, $i, $i % 2 === 0);
            $this->setFunctionModule($modules, $reserved, $i, 6, $i % 2 === 0);
        }

        foreach ($this->alignmentCenters($version) as $row) {
            foreach ($this->alignmentCenters($version) as $col) {
                if ($reserved[$row][$col]) {
                    continue;
                }
                $this->drawAlignment($modules, $reserved, $row, $col);
            }
        }

        $this->setFunctionModule($modules, $reserved, $size - 8, 8, true);
        for ($i = 0; $i < 9; $i++) {
            $this->reserve($reserved, 8, $i);
            $this->reserve($reserved, $i, 8);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->reserve($reserved, $size - 1 - $i, 8);
            $this->reserve($reserved, 8, $size - 1 - $i);
        }
    }

    private function drawFinder(array &$modules, array &$reserved, int $centerRow, int $centerCol): void
    {
        for ($row = $centerRow - 4; $row <= $centerRow + 4; $row++) {
            for ($col = $centerCol - 4; $col <= $centerCol + 4; $col++) {
                if ($row < 0 || $col < 0 || $row >= count($modules) || $col >= count($modules)) {
                    continue;
                }
                $dist = max(abs($row - $centerRow), abs($col - $centerCol));
                $this->setFunctionModule($modules, $reserved, $row, $col, $dist !== 4 && $dist !== 2);
            }
        }
    }

    private function drawAlignment(array &$modules, array &$reserved, int $centerRow, int $centerCol): void
    {
        for ($row = $centerRow - 2; $row <= $centerRow + 2; $row++) {
            for ($col = $centerCol - 2; $col <= $centerCol + 2; $col++) {
                $dist = max(abs($row - $centerRow), abs($col - $centerCol));
                $this->setFunctionModule($modules, $reserved, $row, $col, $dist !== 1);
            }
        }
    }

    private function drawFormatBits(array &$modules, array &$reserved): void
    {
        $size = count($modules);
        $bits = $this->formatBits();
        for ($i = 0; $i <= 5; $i++) {
            $this->setFunctionModule($modules, $reserved, 8, $i, (($bits >> $i) & 1) === 1);
        }
        $this->setFunctionModule($modules, $reserved, 8, 7, (($bits >> 6) & 1) === 1);
        $this->setFunctionModule($modules, $reserved, 8, 8, (($bits >> 7) & 1) === 1);
        $this->setFunctionModule($modules, $reserved, 7, 8, (($bits >> 8) & 1) === 1);
        for ($i = 9; $i < 15; $i++) {
            $this->setFunctionModule($modules, $reserved, 14 - $i, 8, (($bits >> $i) & 1) === 1);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->setFunctionModule($modules, $reserved, $size - 1 - $i, 8, (($bits >> $i) & 1) === 1);
        }
        for ($i = 8; $i < 15; $i++) {
            $this->setFunctionModule($modules, $reserved, 8, $size - 15 + $i, (($bits >> $i) & 1) === 1);
        }
        $this->setFunctionModule($modules, $reserved, $size - 8, 8, true);
    }

    private function formatBits(): int
    {
        $data = (1 << 3); // Error correction level L, mask 0.
        $remainder = $data;
        for ($i = 0; $i < 10; $i++) {
            $remainder = ($remainder << 1) ^ ((($remainder >> 9) & 1) * 0x537);
        }

        return (($data << 10) | ($remainder & 0x3FF)) ^ 0x5412;
    }

    private function alignmentCenters(int $version): array
    {
        return [
            1 => [],
            2 => [6, 18],
            3 => [6, 22],
            4 => [6, 26],
            5 => [6, 30],
            6 => [6, 34],
            7 => [6, 22, 38],
            8 => [6, 24, 42],
            9 => [6, 26, 46],
        ][$version];
    }

    private function setFunctionModule(array &$modules, array &$reserved, int $row, int $col, bool $dark): void
    {
        $modules[$row][$col] = $dark;
        $reserved[$row][$col] = true;
    }

    private function reserve(array &$reserved, int $row, int $col): void
    {
        $reserved[$row][$col] = true;
    }

    private function reedSolomonRemainder(array $data, int $degree): array
    {
        $generator = $this->reedSolomonGenerator($degree);
        $result = array_fill(0, $degree, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($result);
            $result[] = 0;
            for ($i = 0; $i < $degree; $i++) {
                $result[$i] ^= $this->gfMultiply($generator[$i], $factor);
            }
        }

        return $result;
    }

    private function reedSolomonGenerator(int $degree): array
    {
        $coefficients = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($coefficients) + 1, 0);
            foreach ($coefficients as $j => $coefficient) {
                $next[$j] ^= $this->gfMultiply($coefficient, 1);
                $next[$j + 1] ^= $this->gfMultiply($coefficient, $this->gfPow(2, $i));
            }
            $coefficients = $next;
        }
        array_shift($coefficients);

        return $coefficients;
    }

    private function gfPow(int $base, int $exponent): int
    {
        $result = 1;
        for ($i = 0; $i < $exponent; $i++) {
            $result = $this->gfMultiply($result, $base);
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

    private function modulesToSvg(array $modules): string
    {
        $size = count($modules);
        $scale = 8;
        $quiet = 4;
        $dimension = ($size + ($quiet * 2)) * $scale;
        $rects = '';
        foreach ($modules as $row => $cols) {
            foreach ($cols as $col => $dark) {
                if ($dark) {
                    $rects .= '<rect x="' . (($col + $quiet) * $scale) . '" y="' . (($row + $quiet) * $scale) . '" width="' . $scale . '" height="' . $scale . '"/>';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $dimension . ' ' . $dimension . '" width="350" height="350" shape-rendering="crispEdges">'
            . '<rect width="100%" height="100%" fill="#fff"/><g fill="#000">' . $rects . '</g></svg>';
    }
}
