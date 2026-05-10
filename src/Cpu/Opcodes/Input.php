<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait Input
{
    /** SKP Vx — Skip next instruction if key Vx is pressed. */
    private function opEX9E(Opcode $op): void
    {
        if ($this->keyboard->isPressed($this->registers->getV($op->x))) {
            $this->registers->incrementPc();
        }
    }

    /** SKNP Vx — Skip next instruction if key Vx is NOT pressed. */
    private function opEXA1(Opcode $op): void
    {
        if ( ! $this->keyboard->isPressed($this->registers->getV($op->x))) {
            $this->registers->incrementPc();
        }
    }

    /**
     * LD Vx, K — Wait for key press, store key value in Vx.
     * Execution is halted until a key is pressed (re-executes this opcode by decrementing PC).
     */
    private function opFX0A(Opcode $op): void
    {
        $key = $this->keyboard->getFirstPressedKey();

        if ($key === null) {
            $this->registers->decrementPc();

            return;
        }

        $this->registers->setV($op->x, $key);
    }
}
