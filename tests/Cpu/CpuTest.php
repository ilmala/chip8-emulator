<?php

declare(strict_types=1);

use Chip8\Cpu\Cpu;
use Chip8\Display\Display;
use Chip8\Keyboard\Keyboard;
use Chip8\Memory\Memory;
use Chip8\Register\Registers;
use Chip8\Stack\Stack;
use Chip8\Timer\DelayTimer;
use Chip8\Timer\SoundTimer;

// ── Helper ───────────────────────────────────────────────────────────────────

/**
 * Builds a fully-wired CPU and returns it together with all subsystems so that
 * individual tests can inspect or manipulate state directly.
 *
 * @return array{Cpu, Memory, Registers, Stack, DelayTimer, SoundTimer, Display, Keyboard}
 */
function makeCpu(): array
{
    $memory = new Memory();
    $registers = new Registers();
    $stack = new Stack();
    $delayTimer = new DelayTimer();
    $soundTimer = new SoundTimer();
    $display = new Display();
    $keyboard = new Keyboard();

    $cpu = new Cpu($memory, $registers, $stack, $delayTimer, $soundTimer, $display, $keyboard);

    return [$cpu, $memory, $registers, $stack, $delayTimer, $soundTimer, $display, $keyboard];
}

/** Writes a 2-byte opcode word at address 0x200 (program start). */
function loadOpcode(Memory $memory, int $word): void
{
    $memory->write(0x200, ($word >> 8) & 0xFF);
    $memory->write(0x201, $word & 0xFF);
}

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('Cpu', function (): void {

    describe('step() — meccanismo fetch/decode/execute', function (): void {

        it('avanza PC di 2 dopo ogni step', function (): void {
            [$cpu, $memory, $registers] = makeCpu();
            loadOpcode($memory, 0x00E0); // CLS — stub vuoto, PC deve comunque avanzare

            $cpu->step();

            expect($registers->getPc())->toBe(0x202);
        });

        it('lancia RuntimeException per opcode sconosciuto (tipo 0x0, kk=0x00)', function (): void {
            [$cpu, $memory] = makeCpu();
            loadOpcode($memory, 0x0000); // tipo=0x0, kk=0x00 → non è 0xE0 né 0xEE

            expect(fn () => $cpu->step())->toThrow(RuntimeException::class);
        });

        it('lancia RuntimeException per ALU opcode sconosciuto (tipo 0x8, n=0x9)', function (): void {
            [$cpu, $memory] = makeCpu();
            loadOpcode($memory, 0x8009); // n=0x9 non è un opcode 0x8 valido

            expect(fn () => $cpu->step())->toThrow(RuntimeException::class);
        });

    });

    describe('00E0 — CLS', function (): void {

        it('azzera il display', function (): void {
            [$cpu, $memory, , , , , $display] = makeCpu();
            $display->drawSprite(0, 0, [0xFF, 0xFF]);
            loadOpcode($memory, 0x00E0);

            $cpu->step();

            foreach ($display->getPixels() as $pixel) {
                expect($pixel)->toBe(0);
            }
        });

    });

    describe('00EE — RET', function (): void {

        it('imposta PC all\'indirizzo in cima allo stack', function (): void {
            [$cpu, $memory, $registers, $stack] = makeCpu();
            $stack->push(0x0400);
            loadOpcode($memory, 0x00EE);

            $cpu->step();

            expect($registers->getPc())->toBe(0x0400);
        });

        it('lancia UnderflowException se lo stack è vuoto', function (): void {
            [$cpu, $memory] = makeCpu();
            loadOpcode($memory, 0x00EE);

            expect(fn () => $cpu->step())->toThrow(UnderflowException::class);
        });

    });

    describe('EX9E — SKP Vx', function (): void {

        it('salta la prossima istruzione se il tasto Vx è premuto', function (): void {
            [$cpu, $memory, $registers, , , , , $keyboard] = makeCpu();
            $registers->setV(0x2, 0x5); // tasto 5
            $keyboard->press(0x5);
            loadOpcode($memory, 0xE29E); // SKP V2

            $cpu->step();

            expect($registers->getPc())->toBe(0x204); // +2 di step + 2 di skip
        });

        it('non salta se il tasto Vx non è premuto', function (): void {
            [$cpu, $memory, $registers] = makeCpu();
            $registers->setV(0x2, 0x5);
            loadOpcode($memory, 0xE29E);

            $cpu->step();

            expect($registers->getPc())->toBe(0x202); // solo +2 di step
        });

    });

    describe('EXA1 — SKNP Vx', function (): void {

        it('salta la prossima istruzione se il tasto Vx NON è premuto', function (): void {
            [$cpu, $memory, $registers] = makeCpu();
            $registers->setV(0x3, 0xA);
            loadOpcode($memory, 0xE3A1); // SKNP V3

            $cpu->step();

            expect($registers->getPc())->toBe(0x204);
        });

        it('non salta se il tasto Vx è premuto', function (): void {
            [$cpu, $memory, $registers, , , , , $keyboard] = makeCpu();
            $registers->setV(0x3, 0xA);
            $keyboard->press(0xA);
            loadOpcode($memory, 0xE3A1);

            $cpu->step();

            expect($registers->getPc())->toBe(0x202);
        });

    });

    describe('FX07 — LD Vx, DT', function (): void {

        it('carica il valore del delay timer in Vx', function (): void {
            [$cpu, $memory, $registers, , $delayTimer] = makeCpu();
            $delayTimer->setValue(42);
            loadOpcode($memory, 0xF107); // LD V1, DT

            $cpu->step();

            expect($registers->getV(0x1))->toBe(42);
        });

    });

    describe('FX18 — LD ST, Vx', function (): void {

        it('imposta il sound timer al valore di Vx', function (): void {
            [$cpu, $memory, $registers, , , $soundTimer] = makeCpu();
            $registers->setV(0x4, 10);
            loadOpcode($memory, 0xF418); // LD ST, V4

            $cpu->step();

            expect($soundTimer->getValue())->toBe(10);
            expect($soundTimer->isBeeping())->toBeTrue();
        });

    });

    // ── Opcode stub — da implementare nei task successivi ───────────────────

    todo('1NNN — JP addr: imposta PC a NNN');
    todo('2NNN — CALL addr: push PC e salta a NNN');
    todo('3XKK — SE Vx,byte: skip se Vx == KK');
    todo('4XKK — SNE Vx,byte: skip se Vx != KK');
    todo('5XY0 — SE Vx,Vy: skip se Vx == Vy');
    todo('6XKK — LD Vx,byte: Vx = KK');
    todo('7XKK — ADD Vx,byte: Vx += KK (no carry)');
    todo('8XY0 — LD Vx,Vy');
    todo('8XY1 — OR Vx,Vy');
    todo('8XY2 — AND Vx,Vy');
    todo('8XY3 — XOR Vx,Vy');
    todo('8XY4 — ADD Vx,Vy con carry in VF');
    todo('8XY5 — SUB Vx,Vy con borrow in VF');
    todo('8XY6 — SHR Vx: LSB in VF');
    todo('8XY7 — SUBN Vx,Vy con borrow in VF');
    todo('8XYE — SHL Vx: MSB in VF');
    todo('9XY0 — SNE Vx,Vy: skip se Vx != Vy');
    todo('ANNN — LD I,addr: I = NNN');
    todo('BNNN — JP V0,addr: PC = NNN + V0');
    todo('CXKK — RND Vx,byte: Vx = rand & KK');
    todo('DXYN — DRW Vx,Vy,n: disegna sprite, VF = collisione');
    todo('FX0A — LD Vx,K: attendi keypress');
    todo('FX15 — LD DT,Vx: delay timer = Vx');
    todo('FX1E — ADD I,Vx: I += Vx');
    todo('FX29 — LD F,Vx: I = indirizzo font digit Vx');
    todo('FX33 — LD B,Vx: BCD di Vx in memoria a I,I+1,I+2');
    todo('FX55 — LD [I],Vx: salva V0..Vx in memoria');
    todo('FX65 — LD Vx,[I]: carica V0..Vx dalla memoria');

});
