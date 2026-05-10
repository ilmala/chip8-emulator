<?php

declare(strict_types=1);

use Chip8\Opcode\Opcode;

describe('Opcode', function (): void {

    // 0x1A2B usato come riferimento fisso per tutti i test di decodifica:
    //   type = 0x1, x = 0xA, y = 0x2, n = 0xB, kk = 0x2B, nnn = 0xA2B
    beforeEach(function (): void {
        $this->opcode = new Opcode(0x1A2B);
    });

    describe('decodifica campi', function (): void {

        it('preserva il word originale', function (): void {
            expect($this->opcode->word)->toBe(0x1A2B);
        });

        it('decodifica type (nibble più significativo)', function (): void {
            expect($this->opcode->type)->toBe(0x1);
        });

        it('decodifica x (secondo nibble)', function (): void {
            expect($this->opcode->x)->toBe(0xA);
        });

        it('decodifica y (terzo nibble)', function (): void {
            expect($this->opcode->y)->toBe(0x2);
        });

        it('decodifica n (nibble meno significativo)', function (): void {
            expect($this->opcode->n)->toBe(0xB);
        });

        it('decodifica kk (byte basso)', function (): void {
            expect($this->opcode->kk)->toBe(0x2B);
        });

        it('decodifica nnn (12 bit bassi)', function (): void {
            expect($this->opcode->nnn)->toBe(0xA2B);
        });

    });

    describe('casi limite', function (): void {

        it('decodifica 0x0000 correttamente (tutto zero)', function (): void {
            $op = new Opcode(0x0000);

            expect($op->word)->toBe(0x0000);
            expect($op->type)->toBe(0x0);
            expect($op->x)->toBe(0x0);
            expect($op->y)->toBe(0x0);
            expect($op->n)->toBe(0x0);
            expect($op->kk)->toBe(0x00);
            expect($op->nnn)->toBe(0x000);
        });

        it('decodifica 0xFFFF correttamente (tutto uno)', function (): void {
            $op = new Opcode(0xFFFF);

            expect($op->word)->toBe(0xFFFF);
            expect($op->type)->toBe(0xF);
            expect($op->x)->toBe(0xF);
            expect($op->y)->toBe(0xF);
            expect($op->n)->toBe(0xF);
            expect($op->kk)->toBe(0xFF);
            expect($op->nnn)->toBe(0xFFF);
        });

        it('decodifica un opcode reale: CALL 0x200 (0x2200)', function (): void {
            $op = new Opcode(0x2200);

            expect($op->type)->toBe(0x2);
            expect($op->nnn)->toBe(0x200);
        });

        it('decodifica un opcode reale: LD V3, 0xAB (0x63AB)', function (): void {
            $op = new Opcode(0x63AB);

            expect($op->type)->toBe(0x6);
            expect($op->x)->toBe(0x3);
            expect($op->kk)->toBe(0xAB);
        });

        it('decodifica un opcode reale: DRW V1, V2, 5 (0xD125)', function (): void {
            $op = new Opcode(0xD125);

            expect($op->type)->toBe(0xD);
            expect($op->x)->toBe(0x1);
            expect($op->y)->toBe(0x2);
            expect($op->n)->toBe(0x5);
        });

    });

    describe('immutabilità', function (): void {

        it('le proprietà non sono modificabili dopo la costruzione', function (): void {
            expect(fn () => $this->opcode->word = 0x0000)->toThrow(Error::class);
        });

    });

    describe('validazione', function (): void {

        it('lancia RangeException per word > 0xFFFF', function (): void {
            expect(fn () => new Opcode(0x10000))->toThrow(RangeException::class);
        });

        it('lancia RangeException per word negativo', function (): void {
            expect(fn () => new Opcode(-1))->toThrow(RangeException::class);
        });

    });

    describe('__toString', function (): void {

        it('formatta il word come stringa esadecimale uppercase', function (): void {
            expect((string) $this->opcode)->toBe('0x1A2B');
        });

        it('aggiunge zero padding a 4 cifre per word piccoli', function (): void {
            expect((string) new Opcode(0x00E0))->toBe('0x00E0');
        });

    });

});
