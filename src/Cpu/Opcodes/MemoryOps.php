<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait MemoryOps
{
    /** LD I, addr — Set I = NNN. */
    private function opANNN(Opcode $op): void
    {
        $this->registers->setI($op->nnn);
    }

    /** ADD I, Vx — Set I = I + Vx. */
    private function opFX1E(Opcode $op): void
    {
        $this->registers->setI(($this->registers->getI() + $this->registers->getV($op->x)) & 0xFFFF);
    }

    /** LD F, Vx — Set I = address of built-in font sprite for digit Vx. */
    private function opFX29(Opcode $op): void {}

    /** LD B, Vx — Store BCD of Vx in memory at I, I+1, I+2. */
    private function opFX33(Opcode $op): void
    {
        $vx = $this->registers->getV($op->x);
        $i = $this->registers->getI();
        $this->memory->write($i & 0xFFFF, intdiv($vx, 100) & 0xFF);
        $this->memory->write(($i + 1) & 0xFFFF, intdiv($vx % 100, 10) & 0xFF);
        $this->memory->write(($i + 2) & 0xFFFF, ($vx % 10) & 0xFF);
    }

    /** LD [I], Vx — Store V0 through Vx in memory starting at address I. */
    private function opFX55(Opcode $op): void
    {
        $base = $this->registers->getI();
        for ($r = 0; $r <= $op->x; $r++) {
            $this->memory->write(($base + $r) & 0xFFFF, $this->registers->getV($r));
        }
    }

    /** LD Vx, [I] — Read V0 through Vx from memory starting at address I. */
    private function opFX65(Opcode $op): void
    {
        $base = $this->registers->getI();
        for ($r = 0; $r <= $op->x; $r++) {
            $this->registers->setV($r, $this->memory->read(($base + $r) & 0xFFFF));
        }
    }
}
