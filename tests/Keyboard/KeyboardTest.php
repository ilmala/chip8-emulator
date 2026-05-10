<?php

declare(strict_types=1);

use Chip8\Keyboard\Keyboard;

describe('Keyboard', function (): void {

    beforeEach(function (): void {
        $this->keyboard = new Keyboard();
    });

    describe('inizializzazione', function (): void {

        it('tutti i tasti partono rilasciati', function (): void {
            for ($key = 0; $key < 16; $key++) {
                expect($this->keyboard->isPressed($key))->toBeFalse();
            }
        });

        it('getFirstPressedKey restituisce null quando nessun tasto è premuto', function (): void {
            expect($this->keyboard->getFirstPressedKey())->toBeNull();
        });

    });

    describe('press / release', function (): void {

        it('rileva un tasto premuto', function (): void {
            $this->keyboard->press(0xA);

            expect($this->keyboard->isPressed(0xA))->toBeTrue();
        });

        it('rileva un tasto rilasciato dopo press', function (): void {
            $this->keyboard->press(0x5);
            $this->keyboard->release(0x5);

            expect($this->keyboard->isPressed(0x5))->toBeFalse();
        });

        it('più tasti possono essere premuti contemporaneamente', function (): void {
            $this->keyboard->press(0x1);
            $this->keyboard->press(0x2);
            $this->keyboard->press(0xF);

            expect($this->keyboard->isPressed(0x1))->toBeTrue();
            expect($this->keyboard->isPressed(0x2))->toBeTrue();
            expect($this->keyboard->isPressed(0xF))->toBeTrue();
        });

        it('rilasciare un tasto non influenza gli altri', function (): void {
            $this->keyboard->press(0x3);
            $this->keyboard->press(0x4);
            $this->keyboard->release(0x3);

            expect($this->keyboard->isPressed(0x3))->toBeFalse();
            expect($this->keyboard->isPressed(0x4))->toBeTrue();
        });

    });

    describe('getFirstPressedKey', function (): void {

        it('restituisce il tasto premuto quando ce n\'è uno solo', function (): void {
            $this->keyboard->press(0x7);

            expect($this->keyboard->getFirstPressedKey())->toBe(0x7);
        });

        it('restituisce il tasto con indice più basso quando più tasti sono premuti', function (): void {
            $this->keyboard->press(0xC);
            $this->keyboard->press(0x2);
            $this->keyboard->press(0x8);

            expect($this->keyboard->getFirstPressedKey())->toBe(0x2);
        });

        it('torna null dopo che il tasto viene rilasciato', function (): void {
            $this->keyboard->press(0x0);
            $this->keyboard->release(0x0);

            expect($this->keyboard->getFirstPressedKey())->toBeNull();
        });

    });

    describe('releaseAll', function (): void {

        it('rilascia tutti i tasti premuti', function (): void {
            $this->keyboard->press(0x1);
            $this->keyboard->press(0x5);
            $this->keyboard->press(0xE);
            $this->keyboard->releaseAll();

            for ($key = 0; $key < 16; $key++) {
                expect($this->keyboard->isPressed($key))->toBeFalse();
            }
        });

        it('getFirstPressedKey torna null dopo releaseAll', function (): void {
            $this->keyboard->press(0xF);
            $this->keyboard->releaseAll();

            expect($this->keyboard->getFirstPressedKey())->toBeNull();
        });

    });

    describe('validazione', function (): void {

        it('lancia RangeException per tasto > 15', function (): void {
            expect(fn () => $this->keyboard->press(16))->toThrow(RangeException::class);
            expect(fn () => $this->keyboard->release(16))->toThrow(RangeException::class);
            expect(fn () => $this->keyboard->isPressed(16))->toThrow(RangeException::class);
        });

        it('lancia RangeException per tasto negativo', function (): void {
            expect(fn () => $this->keyboard->press(-1))->toThrow(RangeException::class);
        });

    });

});
