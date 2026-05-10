<?php

declare(strict_types=1);

namespace Chip8\Cpu\Opcodes;

use Chip8\Opcode\Opcode;

trait Alu
{
    /** LD Vx, byte — Set Vx = KK. */
    private function op6XKK(Opcode $op): void
    {
        $this->registers->setV($op->x, $op->kk);
    }

    /** ADD Vx, byte — Set Vx = Vx + KK (no carry flag). */
    private function op7XKK(Opcode $op): void
    {
        $this->registers->setV($op->x, ($this->registers->getV($op->x) + $op->kk) & 0xFF);
    }

    /** LD Vx, Vy — Set Vx = Vy. */
    private function op8XY0(Opcode $op): void
    {
        $this->registers->setV($op->x, $this->registers->getV($op->y));
    }

    /** OR Vx, Vy — Set Vx = Vx OR Vy; VF reset (original CHIP-8 quirk). */
    private function op8XY1(Opcode $op): void
    {
        $this->registers->setV($op->x, ($this->registers->getV($op->x) | $this->registers->getV($op->y)) & 0xFF);
        $this->registers->setV(0xF, 0);
    }

    /** AND Vx, Vy — Set Vx = Vx AND Vy; VF reset (original CHIP-8 quirk). */
    private function op8XY2(Opcode $op): void
    {
        $this->registers->setV($op->x, ($this->registers->getV($op->x) & $this->registers->getV($op->y)) & 0xFF);
        $this->registers->setV(0xF, 0);
    }

    /** XOR Vx, Vy — Set Vx = Vx XOR Vy; VF reset (original CHIP-8 quirk). */
    private function op8XY3(Opcode $op): void
    {
        $this->registers->setV($op->x, ($this->registers->getV($op->x) ^ $this->registers->getV($op->y)) & 0xFF);
        $this->registers->setV(0xF, 0);
    }

    /** ADD Vx, Vy — Set Vx = Vx + Vy; VF = carry. */
    private function op8XY4(Opcode $op): void
    {
        $sum = $this->registers->getV($op->x) + $this->registers->getV($op->y);
        $this->registers->setV($op->x, $sum & 0xFF);
        $this->registers->setV(0xF, $sum > 0xFF ? 1 : 0);
    }

    /** SUB Vx, Vy — Set Vx = Vx - Vy; VF = NOT borrow (1 if Vx >= Vy). */
    private function op8XY5(Opcode $op): void
    {
        $vx = $this->registers->getV($op->x);
        $vy = $this->registers->getV($op->y);
        $this->registers->setV($op->x, ($vx - $vy) & 0xFF);
        $this->registers->setV(0xF, $vx >= $vy ? 1 : 0);
    }

    /** SHR Vx, Vy — Set Vx = Vy >> 1; VF = shifted-out LSB (original CHIP-8: source is Vy). */
    private function op8XY6(Opcode $op): void
    {
        $vy = $this->registers->getV($op->y);
        $this->registers->setV($op->x, $vy >> 1);
        $this->registers->setV(0xF, $vy & 0x1);
    }

    /** SUBN Vx, Vy — Set Vx = Vy - Vx; VF = NOT borrow (1 if Vy >= Vx). */
    private function op8XY7(Opcode $op): void
    {
        $vx = $this->registers->getV($op->x);
        $vy = $this->registers->getV($op->y);
        $this->registers->setV($op->x, ($vy - $vx) & 0xFF);
        $this->registers->setV(0xF, $vy >= $vx ? 1 : 0);
    }

    /** SHL Vx, Vy — Set Vx = Vy << 1; VF = shifted-out MSB (original CHIP-8: source is Vy). */
    private function op8XYE(Opcode $op): void
    {
        $vy = $this->registers->getV($op->y);
        $this->registers->setV($op->x, ($vy << 1) & 0xFF);
        $this->registers->setV(0xF, ($vy >> 7) & 0x1);
    }

    /** RND Vx, byte — Set Vx = random byte AND KK. */
    private function opCXKK(Opcode $op): void
    {
        $this->registers->setV($op->x, (random_int(0, 255) & $op->kk) & 0xFF);
    }
}
