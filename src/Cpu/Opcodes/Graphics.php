<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait Graphics
{
    /** CLS — Clear the display. */
    private function op00E0(): void
    {
        $this->display->clear();
    }

    /**
     * DRW Vx, Vy, nibble — Draw N-byte sprite at (Vx, Vy); VF = collision.
     * Reads N bytes from memory starting at address I.
     */
    private function opDXYN(Opcode $op): void
    {
        $x = $this->registers->getV($op->x);
        $y = $this->registers->getV($op->y);
        $base = $this->registers->getI();
        $spriteBytes = [];

        for ($row = 0; $row < $op->n; $row++) {
            $spriteBytes[] = $this->memory->read(($base + $row) & 0xFFFF);
        }

        $collision = $this->display->drawSprite($x, $y, $spriteBytes);
        $this->registers->setV(0xF, $collision ? 1 : 0);
    }
}
