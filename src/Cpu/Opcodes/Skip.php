<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait Skip
{
    /** SE Vx, byte — Skip next instruction if Vx == KK. */
    private function op3XKK(Opcode $op): void
    {
        if ($this->registers->getV($op->x) === $op->kk) {
            $this->registers->incrementPc();
        }
    }

    /** SNE Vx, byte — Skip next instruction if Vx != KK. */
    private function op4XKK(Opcode $op): void
    {
        if ($this->registers->getV($op->x) !== $op->kk) {
            $this->registers->incrementPc();
        }
    }

    /** SE Vx, Vy — Skip next instruction if Vx == Vy. */
    private function op5XY0(Opcode $op): void
    {
        if ($this->registers->getV($op->x) === $this->registers->getV($op->y)) {
            $this->registers->incrementPc();
        }
    }

    /** SNE Vx, Vy — Skip next instruction if Vx != Vy. */
    private function op9XY0(Opcode $op): void
    {
        if ($this->registers->getV($op->x) !== $this->registers->getV($op->y)) {
            $this->registers->incrementPc();
        }
    }
}
