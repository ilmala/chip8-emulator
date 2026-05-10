<?php

declare(strict_types=1);

namespace Chip8\Timer;

use RangeException;

abstract class AbstractTimer implements Timer
{
    /** @var int<0, 255> */
    private int $value = 0;

    public function tick(): void
    {
        if ($this->value > 0) {
            $this->value--;
        }
    }

    /** @return int<0, 255> */
    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        if ($value < 0 || $value > 255) {
            throw new RangeException(
                sprintf('Timer value must be 0–255, got %d.', $value),
            );
        }

        $this->value = $value;
    }
}
