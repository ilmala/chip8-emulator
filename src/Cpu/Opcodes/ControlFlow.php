<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait ControlFlow
{
    /** RET — Return from subroutine: pop PC from stack. */
    private function op00EE(): void
    {
        $this->registers->setPc($this->stack->pop());
    }

    /** JP addr — Jump to address NNN. */
    private function op1NNN(Opcode $op): void
    {
        $this->registers->setPc($op->nnn);
    }

    /** CALL addr — Push current PC onto stack, then jump to NNN. */
    private function op2NNN(Opcode $op): void
    {
        $this->stack->push($this->registers->getPc());
        $this->registers->setPc($op->nnn);
    }

    /** JP V0, addr — Jump to address NNN + V0. */
    private function opBNNN(Opcode $op): void {}
}
