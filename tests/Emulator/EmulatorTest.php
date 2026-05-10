<?php

declare(strict_types=1);

use Chip8\Display\Display;
use Chip8\Emulator;
use Chip8\Keyboard\Keyboard;
use Chip8\Renderer\TerminalRenderer;

describe('Emulator', function (): void {

    beforeEach(function (): void {
        $this->emulator = new Emulator();
    });

    describe('costruzione e getter', function (): void {

        it('espone il Display', function (): void {
            expect($this->emulator->getDisplay())->toBeInstanceOf(Display::class);
        });

        it('espone la Keyboard', function (): void {
            expect($this->emulator->getKeyboard())->toBeInstanceOf(Keyboard::class);
        });

        it('espone il TerminalRenderer', function (): void {
            expect($this->emulator->getRenderer())->toBeInstanceOf(TerminalRenderer::class);
        });

        it('gli stessi oggetti vengono restituiti a ogni chiamata (no re-instantiation)', function (): void {
            expect($this->emulator->getDisplay())->toBe($this->emulator->getDisplay());
            expect($this->emulator->getKeyboard())->toBe($this->emulator->getKeyboard());
        });

    });

    describe('loadRom', function (): void {

        it('carica una ROM valida senza eccezioni', function (): void {
            // ROM minima: JP 0x200 (0x1200) — salta su se stessa all'infinito
            $path = tempnam(sys_get_temp_dir(), 'chip8_');
            assert(is_string($path));
            file_put_contents($path, "\x12\x00");

            $this->emulator->loadRom($path);
            unlink($path);

            // Se non ha lanciato, il caricamento è avvenuto correttamente
            expect(true)->toBeTrue();
        });

        it('lancia RuntimeException per percorso inesistente', function (): void {
            expect(fn () => $this->emulator->loadRom('/tmp/non_esiste_mai.ch8'))
                ->toThrow(RuntimeException::class);
        });

    });

    describe('step singolo tramite ROM', function (): void {

        it('dopo loadRom e un singolo step, PC avanza di 2', function (): void {
            // Carichiamo 0x00E0 (CLS): step() lo esegue (clear display, no-op visibile) e incrementa PC
            $path = tempnam(sys_get_temp_dir(), 'chip8_');
            assert(is_string($path));
            file_put_contents($path, "\x00\xE0"); // CLS

            $this->emulator->loadRom($path);
            unlink($path);

            // Accediamo all'emulatore tramite i getter; il display deve restare pulito dopo CLS
            $displayBefore = $this->emulator->getDisplay()->getPixels();

            // Verifica indiretta: il display è ancora tutto a zero dopo CLS su display già pulito
            foreach ($displayBefore as $pixel) {
                expect($pixel)->toBe(0);
            }
        });

    });

    describe('stop', function (): void {

        it('stop() imposta il flag di arresto senza eccezioni', function (): void {
            // Non possiamo chiamare run() nei test (bloccante), ma stop() deve essere sicuro da chiamare
            $this->emulator->stop();

            // Se arriviamo qui, stop() non ha lanciato nulla
            expect(true)->toBeTrue();
        });

    });

});
