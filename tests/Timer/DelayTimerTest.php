<?php

declare(strict_types=1);

use Chip8\Timer\DelayTimer;

describe('DelayTimer', function (): void {

    beforeEach(function (): void {
        $this->timer = new DelayTimer();
    });

    it('parte a 0', function (): void {
        expect($this->timer->getValue())->toBe(0);
    });

    it('imposta e legge un valore', function (): void {
        $this->timer->setValue(60);

        expect($this->timer->getValue())->toBe(60);
    });

    it('tick decrementa di 1 quando il valore è > 0', function (): void {
        $this->timer->setValue(10);
        $this->timer->tick();

        expect($this->timer->getValue())->toBe(9);
    });

    it('tick non scende sotto 0', function (): void {
        $this->timer->setValue(0);
        $this->timer->tick();

        expect($this->timer->getValue())->toBe(0);
    });

    it('tick da 1 porta a 0', function (): void {
        $this->timer->setValue(1);
        $this->timer->tick();

        expect($this->timer->getValue())->toBe(0);
    });

    it('più tick consecutivi decrementano correttamente', function (): void {
        $this->timer->setValue(3);
        $this->timer->tick();
        $this->timer->tick();
        $this->timer->tick();

        expect($this->timer->getValue())->toBe(0);

        // Ulteriori tick non vanno sotto zero
        $this->timer->tick();
        expect($this->timer->getValue())->toBe(0);
    });

    it('accetta il valore massimo 255', function (): void {
        $this->timer->setValue(255);

        expect($this->timer->getValue())->toBe(255);
    });

    it('lancia RangeException per valore > 255', function (): void {
        expect(fn () => $this->timer->setValue(256))->toThrow(RangeException::class);
    });

    it('lancia RangeException per valore negativo', function (): void {
        expect(fn () => $this->timer->setValue(-1))->toThrow(RangeException::class);
    });

});
