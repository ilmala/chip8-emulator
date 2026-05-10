<?php

declare(strict_types=1);

use Chip8\Display\Display;
use Chip8\Renderer\TerminalRenderer;

describe('TerminalRenderer', function (): void {

    beforeEach(function (): void {
        $this->display = new Display();
        $this->renderer = new TerminalRenderer();
    });

    describe('renderToString', function (): void {

        it('produce una stringa non vuota', function (): void {
            $output = $this->renderer->renderToString($this->display);

            expect($output)->not->toBeEmpty();
        });

        it('contiene esattamente 32 righe di pixel più il cursore ANSI', function (): void {
            $output = $this->renderer->renderToString($this->display);

            // Una riga per ogni row del display + il codice ANSI iniziale
            $lines = explode("\n", $output);
            // 32 righe di pixel + eventuale stringa vuota finale dopo l'ultimo \n
            expect(count(array_filter($lines, fn ($l) => $l !== '')))->toBe(Display::HEIGHT);
        });

        it('usa ░░ per i pixel spenti', function (): void {
            $output = $this->renderer->renderToString($this->display);

            // Tutti i pixel sono spenti: nessun ██
            expect($output)->not->toContain('██');
            expect($output)->toContain('░░');
        });

        it('usa ██ per i pixel accesi', function (): void {
            // Accende l'intera prima riga
            $this->display->drawSprite(0, 0, [0xFF]);

            $output = $this->renderer->renderToString($this->display);

            expect($output)->toContain('██');
        });

        it('produce frame identici per display identici', function (): void {
            $this->display->drawSprite(4, 4, [0xAA, 0x55]);

            $first = $this->renderer->renderToString($this->display);
            $second = $this->renderer->renderToString($this->display);

            expect($first)->toBe($second);
        });

        it('cambia output dopo clear del display', function (): void {
            $this->display->drawSprite(0, 0, [0xFF]);
            $before = $this->renderer->renderToString($this->display);

            $this->display->clear();
            $after = $this->renderer->renderToString($this->display);

            expect($before)->not->toBe($after);
            expect($after)->not->toContain('██');
        });

        it('ogni riga ha esattamente 64 pixel (128 caratteri per i blocchi)', function (): void {
            $output = $this->renderer->renderToString($this->display);
            $lines = array_values(array_filter(explode("\n", $output), fn ($l) => $l !== ''));

            // Salta la prima riga che contiene il codice ANSI
            $pixelLines = array_filter($lines, fn ($l) => ! str_starts_with($l, "\033"));

            foreach ($pixelLines as $line) {
                // Ogni pixel è 2 caratteri UTF-8 (░░ o ██), 64 pixel = 128 caratteri visivi
                // Ma in byte UTF-8 ogni carattere blocco è 3 byte, quindi 64*2*3 = 384 byte
                expect(mb_strlen($line))->toBe(Display::WIDTH * 2);
            }
        });

    });

    describe('render', function (): void {

        it('scrive su stdout senza eccezioni', function (): void {
            ob_start();
            $this->renderer->render($this->display);
            $output = ob_get_clean();

            expect($output)->not->toBeEmpty();
        });

    });

});
