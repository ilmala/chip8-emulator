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
    // CHIP-8 programs typically run at 500–1000 Hz; 900 is a good balance.
    private const int CYCLES_PER_SECOND = 900;

    // Timers decrement and the display is refreshed at 60 Hz.
    private const int TIMER_HZ = 60;

    // Number of CPU cycles between each timer tick and display refresh.
    private const int CYCLES_PER_TICK = self::CYCLES_PER_SECOND / self::TIMER_HZ;

    // How many ticks a key stays "pressed" after the last character read from stdin.
    // At 60 Hz, 6 ticks ≈ 100 ms — enough for slow game loops to poll it.
    private const int KEY_HOLD_TICKS = 6;


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

    // Ticks remaining before auto-releasing the current key (0 = no key held).
    private int $keyHoldTicks = 0;

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
     * Each iteration runs CYCLES_PER_TICK CPU steps back-to-back (no sleep between
     * them), then ticks the timers, reads input, and renders the display. The loop
     * sleeps for whatever time remains in the 1/TIMER_HZ window, keeping the tick
     * rate close to 60 Hz regardless of render overhead.
     */
    public function run(): void
    {
        $this->setupTerminal();
        $this->renderer->clearScreen();

        $tickNs = (int) (1_000_000_000 / self::TIMER_HZ);

        while ( ! $this->stopped) {
            $tickStart = hrtime(true);

            for ($i = 0; $i < self::CYCLES_PER_TICK; $i++) {
                $this->cpu->step();
            }

            $this->readInput();
            $this->delayTimer->tick();
            $this->soundTimer->tick();
            $this->renderer->render($this->display);

            $remainingNs = $tickNs - (hrtime(true) - $tickStart);

            if ($remainingNs > 0) {
                usleep((int) ($remainingNs / 1_000));
            }
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

    /**
     * Maps a physical key character to a CHIP-8 key index (0–15).
     * Layout:  1 2 3 4  →  1 2 3 C
     *          q w e r  →  4 5 6 D
     *          a s d f  →  7 8 9 E
     *          z x c v  →  A 0 B F
     *
     * @return int<0, 15>|null
     */
    private static function charToChip8Key(string $char): ?int
    {
        return match ($char) {
            '1' => 0x1, '2' => 0x2, '3' => 0x3, '4' => 0xC,
            'q' => 0x4, 'w' => 0x5, 'e' => 0x6, 'r' => 0xD,
            'a' => 0x7, 's' => 0x8, 'd' => 0x9, 'f' => 0xE,
            'z' => 0xA, 'x' => 0x0, 'c' => 0xB, 'v' => 0xF,
            default => null,
        };
    }

    private function setupTerminal(): void
    {
        system('stty -icanon -echo min 0 time 0');
        stream_set_blocking(STDIN, false);
        register_shutdown_function(static function (): void {
            system('stty sane');
        });
    }

    private function readInput(): void
    {
        // Drain the entire stdin buffer; keep the last mapped CHIP-8 key found.
        $newKey = null;

        while (true) {
            $char = fread(STDIN, 1);

            if ($char === false || $char === '') {
                break;
            }

            if ($char === "\033") {
                $this->stop();

                return;
            }

            $key = self::charToChip8Key($char);

            if ($key !== null) {
                $newKey = $key;
            }
        }

        if ($newKey !== null) {
            // New input: press the key and reset the hold window.
            $this->keyboard->releaseAll();
            $this->keyboard->press($newKey);
            $this->keyHoldTicks = self::KEY_HOLD_TICKS;
        } elseif ($this->keyHoldTicks > 0) {
            // No new input: count down and auto-release when the window expires.
            $this->keyHoldTicks--;

            if ($this->keyHoldTicks === 0) {
                $this->keyboard->releaseAll();
            }
        }
    }
}
