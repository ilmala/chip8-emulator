<?php

declare(strict_types=1);

namespace Chip8\Opcode;

use RangeException;

/**
 * Immutable value object representing a decoded CHIP-8 instruction word.
 *
 * All fields are derived from the single 16-bit $word in the constructor.
 * Using `readonly class` here is semantically correct: an opcode is never mutated.
 */
final readonly class Opcode
{
    /** Raw 16-bit instruction word. */
    public int $word;

    /**
     * Most-significant nibble — identifies the instruction category.
     *
     * @var int<0, 15>
     */
    public int $type;

    /**
     * Second nibble — first register operand (Vx).
     *
     * @var int<0, 15>
     */
    public int $x;

    /**
     * Third nibble — second register operand (Vy).
     *
     * @var int<0, 15>
     */
    public int $y;

    /**
     * Least-significant nibble — opcode qualifier or 4-bit immediate.
     *
     * @var int<0, 15>
     */
    public int $n;

    /**
     * Lower byte — 8-bit immediate value.
     *
     * @var int<0, 255>
     */
    public int $kk;

    /**
     * Lower 12 bits — 12-bit memory address.
     *
     * @var int<0, 4095>
     */
    public int $nnn;

    public function __construct(int $word)
    {
        if ($word < 0 || $word > 0xFFFF) {
            throw new RangeException(
                sprintf('Opcode word must be 0x0000–0xFFFF, got 0x%X.', $word),
            );
        }

        $this->word = $word;
        $this->type = ($word >> 12) & 0xF;
        $this->x = ($word >> 8) & 0xF;
        $this->y = ($word >> 4) & 0xF;
        $this->n = $word & 0xF;
        $this->kk = $word & 0xFF;
        $this->nnn = $word & 0xFFF;
    }

    public function __toString(): string
    {
        return sprintf('0x%04X', $this->word);
    }
}
