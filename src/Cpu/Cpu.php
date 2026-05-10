<?php

declare(strict_types=1);

namespace Chip8\Cpu;

use Chip8\Display\Display;
use Chip8\Keyboard\Keyboard;
use Chip8\Memory\Memory;
use Chip8\Opcode\Opcode;
use Chip8\Register\Registers;
use Chip8\Stack\Stack;
use Chip8\Timer\DelayTimer;
use Chip8\Timer\SoundTimer;
use RuntimeException;

final class Cpu
{
    public function __construct(
        private readonly Memory $memory,
        private readonly Registers $registers,
        private readonly Stack $stack,
        private readonly DelayTimer $delayTimer,
        private readonly SoundTimer $soundTimer,
        private readonly Display $display,
        private readonly Keyboard $keyboard,
    ) {}

    /**
     * Executes one fetch-decode-execute cycle.
     *
     * Fetches the 2-byte opcode at the current PC, advances PC, then dispatches
     * to the appropriate handler. Timer ticking (60 Hz) is the Emulator's responsibility.
     */
    public function step(): void
    {
        $word = $this->memory->readWord($this->registers->getPc());
        $opcode = new Opcode($word);
        $this->registers->incrementPc();
        $this->dispatch($opcode);
    }

    // ── Dispatch ─────────────────────────────────────────────────────────────

    private function dispatch(Opcode $op): void
    {
        // Outer match covers all 16 possible type nibbles (int<0,15> = 0x0–0xF).
        // No default needed — all cases are exhaustively handled.
        match ($op->type) {
            0x0 => match ($op->kk) {
                0xE0 => $this->op00E0(),
                0xEE => $this->op00EE(),
                default => throw new RuntimeException(
                    sprintf('Unknown opcode: %s', $op),
                ),
            },
            0x1 => $this->op1NNN($op),
            0x2 => $this->op2NNN($op),
            0x3 => $this->op3XKK($op),
            0x4 => $this->op4XKK($op),
            0x5 => $this->op5XY0($op),
            0x6 => $this->op6XKK($op),
            0x7 => $this->op7XKK($op),
            0x8 => match ($op->n) {
                0x0 => $this->op8XY0($op),
                0x1 => $this->op8XY1($op),
                0x2 => $this->op8XY2($op),
                0x3 => $this->op8XY3($op),
                0x4 => $this->op8XY4($op),
                0x5 => $this->op8XY5($op),
                0x6 => $this->op8XY6($op),
                0x7 => $this->op8XY7($op),
                0xE => $this->op8XYE($op),
                default => throw new RuntimeException(
                    sprintf('Unknown opcode: %s', $op),
                ),
            },
            0x9 => $this->op9XY0($op),
            0xA => $this->opANNN($op),
            0xB => $this->opBNNN($op),
            0xC => $this->opCXKK($op),
            0xD => $this->opDXYN($op),
            0xE => match ($op->kk) {
                0x9E => $this->opEX9E($op),
                0xA1 => $this->opEXA1($op),
                default => throw new RuntimeException(
                    sprintf('Unknown opcode: %s', $op),
                ),
            },
            0xF => match ($op->kk) {
                0x07 => $this->opFX07($op),
                0x0A => $this->opFX0A($op),
                0x15 => $this->opFX15($op),
                0x18 => $this->opFX18($op),
                0x1E => $this->opFX1E($op),
                0x29 => $this->opFX29($op),
                0x33 => $this->opFX33($op),
                0x55 => $this->opFX55($op),
                0x65 => $this->opFX65($op),
                default => throw new RuntimeException(
                    sprintf('Unknown opcode: %s', $op),
                ),
            },
        };
    }

    // ── 0x0 ──────────────────────────────────────────────────────────────────

    /** CLS — Clear the display. */
    private function op00E0(): void
    {
        $this->display->clear();
    }

    /** RET — Return from subroutine: pop PC from stack. */
    private function op00EE(): void
    {
        $this->registers->setPc($this->stack->pop());
    }

    // ── 0x1 ──────────────────────────────────────────────────────────────────

    /** JP addr — Jump to address NNN. */
    private function op1NNN(Opcode $op): void
    {
        $this->registers->setPc($op->nnn);
    }

    // ── 0x2 ──────────────────────────────────────────────────────────────────

    /** CALL addr — Push current PC onto stack, then jump to NNN. */
    private function op2NNN(Opcode $op): void {}

    // ── 0x3 ──────────────────────────────────────────────────────────────────

    /** SE Vx, byte — Skip next instruction if Vx == KK. */
    private function op3XKK(Opcode $op): void {}

    // ── 0x4 ──────────────────────────────────────────────────────────────────

    /** SNE Vx, byte — Skip next instruction if Vx != KK. */
    private function op4XKK(Opcode $op): void {}

    // ── 0x5 ──────────────────────────────────────────────────────────────────

    /** SE Vx, Vy — Skip next instruction if Vx == Vy. */
    private function op5XY0(Opcode $op): void {}

    // ── 0x6 ──────────────────────────────────────────────────────────────────

    /** LD Vx, byte — Set Vx = KK. */
    private function op6XKK(Opcode $op): void
    {
        $this->registers->setV($op->x, $op->kk);
    }

    // ── 0x7 ──────────────────────────────────────────────────────────────────

    /** ADD Vx, byte — Set Vx = Vx + KK (no carry flag). */
    private function op7XKK(Opcode $op): void {}

    // ── 0x8 (ALU) ────────────────────────────────────────────────────────────

    /** LD Vx, Vy — Set Vx = Vy. */
    private function op8XY0(Opcode $op): void {}

    /** OR Vx, Vy — Set Vx = Vx OR Vy. */
    private function op8XY1(Opcode $op): void {}

    /** AND Vx, Vy — Set Vx = Vx AND Vy. */
    private function op8XY2(Opcode $op): void {}

    /** XOR Vx, Vy — Set Vx = Vx XOR Vy. */
    private function op8XY3(Opcode $op): void {}

    /** ADD Vx, Vy — Set Vx = Vx + Vy; VF = carry. */
    private function op8XY4(Opcode $op): void {}

    /** SUB Vx, Vy — Set Vx = Vx - Vy; VF = NOT borrow. */
    private function op8XY5(Opcode $op): void {}

    /** SHR Vx — Set Vx = Vx >> 1; VF = shifted-out LSB. */
    private function op8XY6(Opcode $op): void {}

    /** SUBN Vx, Vy — Set Vx = Vy - Vx; VF = NOT borrow. */
    private function op8XY7(Opcode $op): void {}

    /** SHL Vx — Set Vx = Vx << 1; VF = shifted-out MSB. */
    private function op8XYE(Opcode $op): void {}

    // ── 0x9 ──────────────────────────────────────────────────────────────────

    /** SNE Vx, Vy — Skip next instruction if Vx != Vy. */
    private function op9XY0(Opcode $op): void {}

    // ── 0xA ──────────────────────────────────────────────────────────────────

    /** LD I, addr — Set I = NNN. */
    private function opANNN(Opcode $op): void
    {
        $this->registers->setI($op->nnn);
    }

    // ── 0xB ──────────────────────────────────────────────────────────────────

    /** JP V0, addr — Jump to address NNN + V0. */
    private function opBNNN(Opcode $op): void {}

    // ── 0xC ──────────────────────────────────────────────────────────────────

    /** RND Vx, byte — Set Vx = random byte AND KK. */
    private function opCXKK(Opcode $op): void {}

    // ── 0xD ──────────────────────────────────────────────────────────────────

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

    // ── 0xE ──────────────────────────────────────────────────────────────────

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

    // ── 0xF ──────────────────────────────────────────────────────────────────

    /** LD Vx, DT — Set Vx = delay timer value. */
    private function opFX07(Opcode $op): void
    {
        $this->registers->setV($op->x, $this->delayTimer->getValue());
    }

    /**
     * LD Vx, K — Wait for key press, store key value in Vx.
     * Execution is halted until a key is pressed (re-executes this opcode by decrementing PC).
     */
    private function opFX0A(Opcode $op): void {}

    /** LD DT, Vx — Set delay timer = Vx. */
    private function opFX15(Opcode $op): void {}

    /** LD ST, Vx — Set sound timer = Vx. */
    private function opFX18(Opcode $op): void
    {
        $this->soundTimer->setValue($this->registers->getV($op->x));
    }

    /** ADD I, Vx — Set I = I + Vx. */
    private function opFX1E(Opcode $op): void {}

    /** LD F, Vx — Set I = address of built-in font sprite for digit Vx. */
    private function opFX29(Opcode $op): void {}

    /** LD B, Vx — Store BCD of Vx in memory at I, I+1, I+2. */
    private function opFX33(Opcode $op): void {}

    /** LD [I], Vx — Store V0 through Vx in memory starting at address I. */
    private function opFX55(Opcode $op): void {}

    /** LD Vx, [I] — Read V0 through Vx from memory starting at address I. */
    private function opFX65(Opcode $op): void {}
}
