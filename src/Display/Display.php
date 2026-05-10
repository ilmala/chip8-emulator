<?php

declare(strict_types=1);

namespace Chip8\Display;

final class Display
{
    public const int WIDTH = 64;

    public const int HEIGHT = 32;

    /**
     * Flat row-major array: index = y * WIDTH + x.
     * Each cell is 0 (off) or 1 (on).
     *
     * @var array<int, int<0, 1>>
     */
    private array $pixels;

    public function __construct()
    {
        $this->pixels = array_fill(0, self::WIDTH * self::HEIGHT, 0);
    }

    public function clear(): void
    {
        $this->pixels = array_fill(0, self::WIDTH * self::HEIGHT, 0);
    }

    /**
     * XOR-draws a sprite at position (x, y).
     *
     * Each byte in $spriteBytes is one row of 8 pixels: bit 7 is the leftmost pixel.
     * Coordinates wrap around the screen boundaries.
     * Returns true if any lit pixel was turned off (collision flag for VF).
     *
     * @param array<int, int> $spriteBytes
     */
    public function drawSprite(int $x, int $y, array $spriteBytes): bool
    {
        $collision = false;

        foreach ($spriteBytes as $row => $byte) {
            for ($col = 0; $col < 8; $col++) {
                // Extract bit from MSB to LSB
                $bit = ($byte >> (7 - $col)) & 0x1;

                if ($bit !== 1) {
                    continue;
                }

                $px = ($x + $col) % self::WIDTH;
                $py = ($y + $row) % self::HEIGHT;
                $index = $py * self::WIDTH + $px;

                if ($this->pixels[$index] === 1) {
                    $collision = true;
                }

                $this->pixels[$index] = $this->pixels[$index] === 0 ? 1 : 0;
            }
        }

        return $collision;
    }

    /**
     * @param int<0, 63> $x
     * @param int<0, 31> $y
     * @return int<0, 1>
     */
    public function getPixel(int $x, int $y): int
    {
        return $this->pixels[$y * self::WIDTH + $x];
    }

    /** @return array<int, int<0, 1>> */
    public function getPixels(): array
    {
        return $this->pixels;
    }
}
