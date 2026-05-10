<?php

declare(strict_types=1);

namespace Chip8\Timer;

final class SoundTimer extends AbstractTimer
{
    /** The sound hardware beeps whenever this timer is non-zero. */
    public function isBeeping(): bool
    {
        return $this->getValue() > 0;
    }
}
