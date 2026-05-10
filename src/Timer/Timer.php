<?php

declare(strict_types=1);

namespace Chip8\Timer;

interface Timer
{
    /** Decrements the value by 1 if > 0. Must be called at 60 Hz. */
    public function tick(): void;

    /** @return int<0, 255> */
    public function getValue(): int;

    public function setValue(int $value): void;
}
