<?php

declare(strict_types=1);

use Chip8\Register\Registers;

describe('Registers', function (): void {

    beforeEach(function (): void {
        $this->registers = new Registers();
    });

    describe('inizializzazione', function (): void {

        it('inizializza PC a 0x200', function (): void {
            expect($this->registers->getPc())->toBe(0x200);
        });

        it('inizializza tutti i registri V a 0', function (): void {
            for ($i = 0; $i < 16; $i++) {
                expect($this->registers->getV($i))->toBe(0);
            }
        });

        it('inizializza I a 0', function (): void {
            expect($this->registers->getI())->toBe(0);
        });

    });

    describe('registro V', function (): void {

        it('legge e scrive un registro V per indice', function (): void {
            $this->registers->setV(3, 0xAB);

            expect($this->registers->getV(3))->toBe(0xAB);
        });

        it('gestisce tutti e 16 i registri indipendentemente', function (): void {
            for ($i = 0; $i < 16; $i++) {
                $this->registers->setV($i, $i * 10);
            }

            for ($i = 0; $i < 16; $i++) {
                expect($this->registers->getV($i))->toBe($i * 10);
            }
        });

        it('lancia RangeException per indice V fuori da 0–15 in lettura', function (): void {
            expect(fn () => $this->registers->getV(16))->toThrow(RangeException::class);
            expect(fn () => $this->registers->getV(-1))->toThrow(RangeException::class);
        });

        it('lancia RangeException per indice V fuori da 0–15 in scrittura', function (): void {
            expect(fn () => $this->registers->setV(16, 0x00))->toThrow(RangeException::class);
        });

        it('VF (indice 15) è accessibile come gli altri registri', function (): void {
            $this->registers->setV(0xF, 0x01);

            expect($this->registers->getV(0xF))->toBe(0x01);
        });

    });

    describe('registro I', function (): void {

        it('legge e scrive il registro I', function (): void {
            $this->registers->setI(0x0ABC);

            expect($this->registers->getI())->toBe(0x0ABC);
        });

        it('accetta il valore massimo 0xFFFF', function (): void {
            $this->registers->setI(0xFFFF);

            expect($this->registers->getI())->toBe(0xFFFF);
        });

    });

    describe('program counter', function (): void {

        it('legge e scrive PC', function (): void {
            $this->registers->setPc(0x300);

            expect($this->registers->getPc())->toBe(0x300);
        });

        it('incrementPc avanza di 2', function (): void {
            $this->registers->setPc(0x200);
            $this->registers->incrementPc();

            expect($this->registers->getPc())->toBe(0x202);
        });

        it('incrementPc wrappa a 0 se supera 0xFFFF', function (): void {
            $this->registers->setPc(0xFFFE);
            $this->registers->incrementPc();

            expect($this->registers->getPc())->toBe(0x0000);
        });

    });

});
