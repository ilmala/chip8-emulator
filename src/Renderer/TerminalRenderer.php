<?php

declare(strict_types=1);

namespace Chip8\Renderer;

use Chip8\Display\Display;

final class TerminalRenderer
{
    private const string ON = '██';

    private const string OFF = '░░';

    // ANSI: move cursor to top-left without clearing (avoids flicker vs \033[2J\033[H)
    private const string HOME = "\033[H";

    /**
     * Renders the display framebuffer to stdout.
     * Call this at 60 Hz from the emulator loop.
     */
    public function render(Display $display): void
    {
        echo $this->renderToString($display);
    }

    /**
     * Returns the full frame as a string (used by render() and tests).
     */
    public function renderToString(Display $display): string
    {
        $output = self::HOME;

        for ($y = 0; $y < Display::HEIGHT; $y++) {
            for ($x = 0; $x < Display::WIDTH; $x++) {
                $output .= $display->getPixel($x, $y) === 1 ? self::ON : self::OFF;
            }

            $output .= "\n";
        }

        return $output;
    }

    /** Clears the terminal screen (call once before the first frame). */
    public function clearScreen(): void
    {
        echo "\033[2J\033[H";
    }
}
