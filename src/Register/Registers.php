<?php

declare(strict_types=1);

namespace Chip8\Register;

use RangeException;

final class Registers
{
    private const int V_COUNT = 16;

    private const int PC_START = 0x200;

    /** @var array<int<0, 15>, int<0, 255>> */
    private array $v;

    /** @var int<0, 65535> */
    private int $i = 0;

    /** @var int<0, 65535> */
    private int $pc;

    public function __construct()
    {
        $this->v = array_fill(0, self::V_COUNT, 0);
        $this->pc = self::PC_START;
    }

    /** @return int<0, 255> */
    public function getV(int $index): int
    {
        $this->assertVIndex($index);

        return $this->v[$index];
    }

    /** @param Uint8 $value */
    public function setV(int $index, int $value): void
    {
        $this->assertVIndex($index);

        $this->v[$index] = $value;
    }

    /** @return int<0, 65535> */
    public function getI(): int
    {
        return $this->i;
    }

    /** @param Uint16 $value */
    public function setI(int $value): void
    {
        $this->i = $value;
    }

    /** @return int<0, 65535> */
    public function getPc(): int
    {
        return $this->pc;
    }

    /** @param Uint16 $value */
    public function setPc(int $value): void
    {
        $this->pc = $value;
    }

    // Each opcode is 2 bytes wide
    public function incrementPc(): void
    {
        $this->pc = ($this->pc + 2) & 0xFFFF;
    }

    /**
     * @phpstan-assert int<0, 15> $index
     */
    private function assertVIndex(int $index): void
    {
        if ($index < 0 || $index >= self::V_COUNT) {
            throw new RangeException(
                sprintf('Register index must be 0–15, got %d.', $index),
            );
        }
    }
}
