<?php

declare(strict_types=1);

use Chip8\Memory\Memory;

describe('Memory', function (): void {

    beforeEach(function (): void {
        $this->memory = new Memory();
    });

    describe('initializzazione', function (): void {

        it('ha tutti i byte a zero nelle aree non riservate', function (): void {
            // L'area 0x200–0xFFF deve essere tutta a zero prima di caricare una ROM
            for ($addr = 0x200; $addr < 0x1000; $addr++) {
                expect($this->memory->read($addr))->toBe(0);
            }
        });

        it('carica il font set a partire da 0x050', function (): void {
            // Primo sprite: "0" → 0xF0, 0x90, 0x90, 0x90, 0xF0
            expect($this->memory->read(0x050))->toBe(0xF0);
            expect($this->memory->read(0x051))->toBe(0x90);
            expect($this->memory->read(0x054))->toBe(0xF0);
        });

        it('carica tutti e 16 gli sprite del font (80 byte totali)', function (): void {
            // Ultimo sprite: "F" → 0xF0, 0x80, 0xF0, 0x80, 0x80
            $fStart = 0x050 + (0xF * 5); // 0x09B
            expect($this->memory->read($fStart))->toBe(0xF0);
            expect($this->memory->read($fStart + 1))->toBe(0x80);
            expect($this->memory->read($fStart + 4))->toBe(0x80);
        });

    });

    describe('read / write', function (): void {

        it('scrive e rilegge un byte correttamente', function (): void {
            $this->memory->write(0x300, 0xAB);

            expect($this->memory->read(0x300))->toBe(0xAB);
        });

        it('sovrascrive un valore precedente', function (): void {
            $this->memory->write(0x400, 0x11);
            $this->memory->write(0x400, 0xFF);

            expect($this->memory->read(0x400))->toBe(0xFF);
        });

        it('lancia RangeException per indirizzo fuori range in lettura', function (): void {
            expect(fn () => $this->memory->read(0x1000))->toThrow(RangeException::class);
            expect(fn () => $this->memory->read(-1))->toThrow(RangeException::class);
        });

        it('lancia RangeException per indirizzo fuori range in scrittura', function (): void {
            expect(fn () => $this->memory->write(0x1000, 0x00))->toThrow(RangeException::class);
        });

    });
    // Nota: write() non valida il range del valore a runtime perché @param Uint8 $value
    // garantisce staticamente tramite PHPStan Level 9 che il caller passi sempre int<0,255>.

    describe('readWord', function (): void {

        it('legge una parola big-endian da due byte consecutivi', function (): void {
            $this->memory->write(0x300, 0x1A);
            $this->memory->write(0x301, 0x2B);

            expect($this->memory->readWord(0x300))->toBe(0x1A2B);
        });

        it('combina correttamente byte alto e basso', function (): void {
            $this->memory->write(0x500, 0xFF);
            $this->memory->write(0x501, 0x00);

            expect($this->memory->readWord(0x500))->toBe(0xFF00);
        });

        it('lancia RangeException se il secondo byte è fuori range', function (): void {
            // 0xFFF è l'ultimo indirizzo valido: readWord richiederebbe 0xFFF e 0x1000
            expect(fn () => $this->memory->readWord(0xFFF))->toThrow(RangeException::class);
        });

    });

    describe('loadRom', function (): void {

        it('carica i byte della ROM a partire da 0x200', function (): void {
            $path = tempnam(sys_get_temp_dir(), 'chip8_');
            assert(is_string($path));
            file_put_contents($path, "\x12\x00\xAB\xCD");

            $this->memory->loadRom($path);
            unlink($path);

            expect($this->memory->read(0x200))->toBe(0x12);
            expect($this->memory->read(0x201))->toBe(0x00);
            expect($this->memory->read(0x202))->toBe(0xAB);
            expect($this->memory->read(0x203))->toBe(0xCD);
        });

        it('lancia RuntimeException se il file non esiste', function (): void {
            expect(fn () => $this->memory->loadRom('/tmp/non_esiste.ch8'))
                ->toThrow(RuntimeException::class);
        });

        it('lancia RuntimeException se la ROM supera 3584 byte', function (): void {
            $path = tempnam(sys_get_temp_dir(), 'chip8_');
            assert(is_string($path));
            file_put_contents($path, str_repeat("\x00", 3585));

            $result = fn () => $this->memory->loadRom($path);
            unlink($path);

            expect($result)->toThrow(RuntimeException::class);
        });

    });

    describe('fontAddress', function (): void {

        it('restituisce 0x050 per il digit 0', function (): void {
            expect($this->memory->fontAddress(0))->toBe(0x050);
        });

        it('restituisce l\'indirizzo corretto per ogni digit (passo di 5 byte)', function (): void {
            expect($this->memory->fontAddress(1))->toBe(0x055);
            expect($this->memory->fontAddress(0xF))->toBe(0x050 + 0xF * 5);
        });

        it('lancia RangeException per digit > 15', function (): void {
            expect(fn () => $this->memory->fontAddress(16))->toThrow(RangeException::class);
        });

    });

});
