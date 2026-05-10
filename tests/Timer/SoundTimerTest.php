<?php

declare(strict_types=1);

use Chip8\Timer\SoundTimer;

describe('SoundTimer', function (): void {

    beforeEach(function (): void {
        $this->timer = new SoundTimer();
    });

    it('non è in beep quando il valore è 0', function (): void {
        expect($this->timer->isBeeping())->toBeFalse();
    });

    it('è in beep quando il valore è > 0', function (): void {
        $this->timer->setValue(1);

        expect($this->timer->isBeeping())->toBeTrue();
    });

    it('è in beep con il valore massimo 255', function (): void {
        $this->timer->setValue(255);

        expect($this->timer->isBeeping())->toBeTrue();
    });

    it('smette di fare beep dopo il tick che porta a 0', function (): void {
        $this->timer->setValue(1);
        $this->timer->tick();

        expect($this->timer->isBeeping())->toBeFalse();
    });

    it('continua a fare beep finché il valore è > 0', function (): void {
        $this->timer->setValue(3);

        $this->timer->tick();
        expect($this->timer->isBeeping())->toBeTrue(); // valore = 2

        $this->timer->tick();
        expect($this->timer->isBeeping())->toBeTrue(); // valore = 1

        $this->timer->tick();
        expect($this->timer->isBeeping())->toBeFalse(); // valore = 0
    });

});
