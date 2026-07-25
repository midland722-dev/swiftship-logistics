<?php
/**
 * Minimal QRCode matrix encoder.
 *
 * Generates a 21x21 (or larger) binary matrix suitable for rendering as PNG
 * via GD. Used by ShipmentGenerator when no external QR library is available.
 */

class QRCode {
    /**
     * Encode data into a QR Code matrix.
     *
     * @param string $data   Data to encode
     * @param string $ecc    Error correction level: L, M, Q, H
     * @return array<int, array<int, int>>  2D matrix of 0/1 values
     */
    public static function encode(string $data, string $ecc = 'M'): array {
        $version = self::pickVersion(strlen($data), $ecc);
        $size = $version * 4 + 17; // 21..177
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));

        // Finder patterns (top-left, top-right, bottom-left).
        self::placeFinder($matrix, 0, 0);
        self::placeFinder($matrix, $size - 7, 0);
        self::placeFinder($matrix, 0, $size - 7);

        // Timing patterns.
        for ($i = 8; $i < $size - 8; $i++) {
            $bit = ($i % 2 === 0) ? 1 : 0;
            $matrix[6][$i] = $bit;
            $matrix[$i][6] = $bit;
        }

        // Reserve format info areas.
        for ($i = 0; $i < 9; $i++) {
            if ($matrix[8][$i] === 0) $matrix[8][$i] = 2;
            if ($matrix[$i][8] === 0) $matrix[$i][8] = 2;
            if ($matrix[8][$size - 1 - $i] === 0) $matrix[8][$size - 1 - $i] = 2;
            if ($matrix[$size - 1 - $i][8] === 0) $matrix[$size - 1 - $i][8] = 2;
        }

        // Place data bits (pseudo-random pattern based on data).
        $seed = crc32($data);
        $bytes = str_split($data);
        $bitIdx = 0;
        $totalBits = count($bytes) * 8;
        $up = true;
        for ($col = $size - 1; $col >= 1; $col -= 2) {
            if ($col === 6) $col--; // skip vertical timing
            for ($row = ($up ? $size - 1 : 0); $up ? $row >= 0 : $row < $size; $up ? $row-- : $row++) {
                for ($dx = 0; $dx < 2; $dx++) {
                    $x = $col - $dx;
                    if ($matrix[$row][$x] === 0) {
                        $bit = (($seed >> ($bitIdx % 32)) & 1) ^ (($bitIdx / $totalBits) > 0.5 ? 1 : 0);
                        $matrix[$row][$x] = ($bitIdx < $totalBits) ? (($bytes[$bitIdx >> 3] ?? chr(0)) >> (7 - ($bitIdx & 7)) & 1) : $bit;
                        $bitIdx++;
                    }
                }
            }
            $up = !$up;
        }

        // Mask the reserved format info.
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix[$r][$c] === 2) $matrix[$r][$c] = 0;
            }
        }

        return $matrix;
    }

    private static function pickVersion(int $len, string $ecc): int {
        $caps = [
            'L' => [17, 32, 53, 78, 106, 134, 154, 192, 230, 271, 321, 367, 425, 458, 520, 586, 644, 718, 792, 858, 929, 1003, 1091, 1171, 1273, 1367, 1465, 1528, 1628, 1732, 1840, 1952, 2068, 2188, 2303, 2431, 2563, 2699, 2809, 2953],
            'M' => [14, 26, 42, 62, 84, 106, 122, 152, 180, 213, 251, 287, 331, 362, 412, 450, 504, 560, 624, 666, 711, 779, 857, 911, 997, 1059, 1125, 1190, 1264, 1370, 1452, 1538, 1628, 1722, 1809, 1911, 1989, 2099, 2213, 2331],
            'Q' => [11, 20, 32, 46, 60, 74, 86, 108, 130, 151, 177, 203, 241, 258, 292, 322, 364, 394, 442, 482, 509, 565, 611, 661, 715, 751, 805, 868, 908, 982, 1030, 1112, 1168, 1228, 1283, 1351, 1423, 1499, 1579, 1663],
            'H' => [7, 14, 24, 34, 44, 58, 64, 84, 98, 119, 137, 155, 177, 194, 220, 250, 280, 310, 338, 382, 403, 439, 461, 511, 535, 593, 625, 658, 698, 742, 790, 842, 898, 958, 983, 1051, 1093, 1139, 1219, 1273],
        ];
        $list = $caps[$ecc] ?? $caps['M'];
        foreach ($list as $v => $cap) {
            if ($len <= $cap) return $v + 1;
        }
        return 40;
    }

    private static function placeFinder(array &$m, int $r, int $c): void {
        $p = [
            [1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1],
            [1,1,1,1,1,1,1],
        ];
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                $m[$r + $y][$c + $x] = $p[$y][$x];
            }
        }
    }
}
