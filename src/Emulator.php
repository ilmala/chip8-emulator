<?php

declare(strict_types=1);

namespace Chip8;

use Chip8\Cpu\Cpu;
use Chip8\Display\Display;
use Chip8\Keyboard\Keyboard;
use Chip8\Memory\Memory;
use Chip8\Register\Registers;
use Chip8\Renderer\TerminalRenderer;
use Chip8\Stack\Stack;
use Chip8\Timer\DelayTimer;
use Chip8\Timer\SoundTimer;

final class Emulator
{
    // CHIP-8 programs typically run at 500–700 Hz; 600 is a safe middle ground.
    private const int CYCLES_PER_SECOND = 600;

    // Timers decrement and the display is refreshed at 60 Hz.
    private const int TIMER_HZ = 60;

    // Number of CPU cycles between each timer tick and display refresh.
    private const int CYCLES_PER_TICK = self::CYCLES_PER_SECOND / self::TIMER_HZ;

    private readonly Memory $memory;

    private readonly Registers $registers;

    private readonly Stack $stack;

    private readonly DelayTimer $delayTimer;

    private readonly SoundTimer $soundTimer;

    private readonly Display $display;

    private readonly Keyboard $keyboard;

    private readonly Cpu $cpu;

    private readonly TerminalRenderer $renderer;

    private bool $stopped = false;

    public function __construct()
    {
        $this->memory = new Memory();
        $this->registers = new Registers();
        $this->stack = new Stack();
        $this->delayTimer = new DelayTimer();
        $this->soundTimer = new SoundTimer();
        $this->display = new Display();
        $this->keyboard = new Keyboard();
        $this->renderer = new TerminalRenderer();

        $this->cpu = new Cpu(
            $this->memory,
            $this->registers,
            $this->stack,
            $this->delayTimer,
            $this->soundTimer,
            $this->display,
            $this->keyboard,
        );
    }

    /** Loads a ROM file into memory starting at 0x200. */
    public function loadRom(string $path): void
    {
        $this->memory->loadRom($path);
    }

    /**
     * Starts the main emulator loop.
     *
     * Runs CPU steps at ~600 Hz. Every CYCLES_PER_TICK steps (~10), both timers
     * are decremented and the display is re-rendered. Call stop() to exit the loop.
     *
     * Rate limiting uses usleep() to target the desired cycle rate without
     * busy-waiting. Precision is sufficient for CHIP-8 (±1 ms drift is acceptable).
     */
    public function run(): void
    {
        $this->renderer->clearScreen();

        $cycleCount = 0;
        $sleepUs = (int) (1_000_000 / self::CYCLES_PER_SECOND);

        while ( ! $this->stopped) {
            $this->cpu->step();
            $cycleCount++;

            if ($cycleCount >= self::CYCLES_PER_TICK) {
                $this->delayTimer->tick();
                $this->soundTimer->tick();
                $this->renderer->render($this->display);
                $cycleCount = 0;
            }

            usleep($sleepUs);
        }
    }

    /** Stops the run() loop at the end of the current cycle. */
    public function stop(): void
    {
        $this->stopped = true;
    }

    public function getDisplay(): Display
    {
        return $this->display;
    }

    public function getKeyboard(): Keyboard
    {
        return $this->keyboard;
    }

    public function getRenderer(): TerminalRenderer
    {
        return $this->renderer;
    }
}
