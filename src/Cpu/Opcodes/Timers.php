<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait Timers
{
    /** LD Vx, DT — Set Vx = delay timer value. */
    private function opFX07(Opcode $op): void
    {
        $this->registers->setV($op->x, $this->delayTimer->getValue());
    }

    /** LD DT, Vx — Set delay timer = Vx. */
    private function opFX15(Opcode $op): void {}

    /** LD ST, Vx — Set sound timer = Vx. */
    private function opFX18(Opcode $op): void
    {
        $this->soundTimer->setValue($this->registers->getV($op->x));
    }
}
