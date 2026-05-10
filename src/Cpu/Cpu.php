<?php

declare(strict_types=1);

namespace Chip8\Cpu;

use Chip8\Cpu\Opcodes\Alu;
use Chip8\Cpu\Opcodes\ControlFlow;
use Chip8\Cpu\Opcodes\Graphics;
use Chip8\Cpu\Opcodes\Input;
use Chip8\Cpu\Opcodes\MemoryOps;
use Chip8\Cpu\Opcodes\Skip;
use Chip8\Cpu\Opcodes\Timers;
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
    use Alu;
    use ControlFlow;
    use Graphics;
    use Input;
    use MemoryOps;
    use Skip;
    use Timers;

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

    private function dispatch(Opcode $op): void
    {
        match ($op->type) {
            0x0 => match ($op->kk) {
                0xE0 => $this->op00E0(),
                0xEE => $this->op00EE(),
                // SUPER-CHIP extensions: hires mode, scroll, exit — treat as no-ops
                0xFB, 0xFC, 0xFD, 0xFE, 0xFF => null,
                default => ($op->kk & 0xF0) === 0xC0
                    ? null  // 0x00Cn scroll down (no-op)
                    : throw new RuntimeException(sprintf('Unknown opcode: %s', $op)),
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
                default => throw new RuntimeException(sprintf('Unknown opcode: %s', $op)),
            },
            0x9 => $this->op9XY0($op),
            0xA => $this->opANNN($op),
            0xB => $this->opBNNN($op),
            0xC => $this->opCXKK($op),
            0xD => $this->opDXYN($op),
            0xE => match ($op->kk) {
                0x9E => $this->opEX9E($op),
                0xA1 => $this->opEXA1($op),
                default => throw new RuntimeException(sprintf('Unknown opcode: %s', $op)),
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
                default => throw new RuntimeException(sprintf('Unknown opcode: %s', $op)),
            },
        };
    }
}
