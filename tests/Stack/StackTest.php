<?php

declare(strict_types=1);

use Chip8\Stack\Stack;

describe('Stack', function (): void {

    beforeEach(function (): void {
        $this->stack = new Stack();
    });

    describe('inizializzazione', function (): void {

        it('parte vuoto', function (): void {
            expect($this->stack->isEmpty())->toBeTrue();
            expect($this->stack->depth())->toBe(0);
        });

    });

    describe('push / pop', function (): void {

        it('esegue push e poi pop dello stesso indirizzo', function (): void {
            $this->stack->push(0x0300);

            expect($this->stack->pop())->toBe(0x0300);
        });

        it('rispetta l\'ordine LIFO con più indirizzi', function (): void {
            $this->stack->push(0x0200);
            $this->stack->push(0x0300);
            $this->stack->push(0x0400);

            expect($this->stack->pop())->toBe(0x0400);
            expect($this->stack->pop())->toBe(0x0300);
            expect($this->stack->pop())->toBe(0x0200);
        });

        it('aggiorna depth correttamente dopo push e pop', function (): void {
            expect($this->stack->depth())->toBe(0);

            $this->stack->push(0x0200);
            expect($this->stack->depth())->toBe(1);

            $this->stack->push(0x0300);
            expect($this->stack->depth())->toBe(2);

            $this->stack->pop();
            expect($this->stack->depth())->toBe(1);

            $this->stack->pop();
            expect($this->stack->depth())->toBe(0);
        });

        it('isEmpty torna false dopo un push', function (): void {
            $this->stack->push(0x0200);

            expect($this->stack->isEmpty())->toBeFalse();
        });

        it('isEmpty torna true dopo aver svuotato lo stack', function (): void {
            $this->stack->push(0x0200);
            $this->stack->pop();

            expect($this->stack->isEmpty())->toBeTrue();
        });

    });

    describe('overflow / underflow', function (): void {

        it('lancia OverflowException al 17° push', function (): void {
            for ($i = 0; $i < 16; $i++) {
                $this->stack->push(0x0200 + $i * 2);
            }

            expect(fn () => $this->stack->push(0x0300))->toThrow(OverflowException::class);
        });

        it('accetta esattamente 16 livelli senza eccezioni', function (): void {
            for ($i = 0; $i < 16; $i++) {
                $this->stack->push(0x0200 + $i * 2);
            }

            expect($this->stack->depth())->toBe(16);
        });

        it('lancia UnderflowException facendo pop su stack vuoto', function (): void {
            expect(fn () => $this->stack->pop())->toThrow(UnderflowException::class);
        });

        it('lancia UnderflowException dopo aver svuotato lo stack', function (): void {
            $this->stack->push(0x0200);
            $this->stack->pop();

            expect(fn () => $this->stack->pop())->toThrow(UnderflowException::class);
        });

    });

});
