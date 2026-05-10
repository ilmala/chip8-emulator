<?php

declare(strict_types=1);

use Chip8\Display\Display;

describe('Display', function (): void {

    beforeEach(function (): void {
        $this->display = new Display();
    });

    describe('inizializzazione', function (): void {

        it('ha tutti i pixel a 0', function (): void {
            foreach ($this->display->getPixels() as $pixel) {
                expect($pixel)->toBe(0);
            }
        });

        it('ha esattamente 64×32 = 2048 pixel', function (): void {
            expect(count($this->display->getPixels()))->toBe(2048);
        });

    });

    describe('clear', function (): void {

        it('azzera tutti i pixel dopo aver disegnato', function (): void {
            $this->display->drawSprite(0, 0, [0xFF]);
            $this->display->clear();

            foreach ($this->display->getPixels() as $pixel) {
                expect($pixel)->toBe(0);
            }
        });

    });

    describe('getPixel', function (): void {

        it('legge un pixel spento', function (): void {
            expect($this->display->getPixel(0, 0))->toBe(0);
        });

        it('legge un pixel acceso dopo drawSprite', function (): void {
            // 0xFF = 11111111 → accende gli 8 pixel della prima riga a partire da (0,0)
            $this->display->drawSprite(0, 0, [0xFF]);

            expect($this->display->getPixel(0, 0))->toBe(1);
            expect($this->display->getPixel(7, 0))->toBe(1);
        });

    });

    describe('drawSprite', function (): void {

        it('accende i pixel corrispondenti ai bit 1 dello sprite', function (): void {
            // 0xF0 = 11110000 → i 4 pixel di sinistra accesi, i 4 di destra spenti
            $this->display->drawSprite(0, 0, [0xF0]);

            expect($this->display->getPixel(0, 0))->toBe(1);
            expect($this->display->getPixel(1, 0))->toBe(1);
            expect($this->display->getPixel(2, 0))->toBe(1);
            expect($this->display->getPixel(3, 0))->toBe(1);
            expect($this->display->getPixel(4, 0))->toBe(0);
            expect($this->display->getPixel(7, 0))->toBe(0);
        });

        it('disegna correttamente uno sprite multi-riga', function (): void {
            // Sprite a "T": riga 0 = 0xFF (tutta accesa), riga 1 = 0x18 (solo centro)
            $this->display->drawSprite(0, 0, [0xFF, 0x18]);

            // Prima riga: tutti accesi
            for ($col = 0; $col < 8; $col++) {
                expect($this->display->getPixel($col, 0))->toBe(1);
            }

            // Seconda riga: solo bit 4 e 3 (0x18 = 00011000)
            expect($this->display->getPixel(3, 1))->toBe(1);
            expect($this->display->getPixel(4, 1))->toBe(1);
            expect($this->display->getPixel(0, 1))->toBe(0);
        });

        it('restituisce false quando non c\'è collisione', function (): void {
            $collision = $this->display->drawSprite(0, 0, [0xFF]);

            expect($collision)->toBeFalse();
        });

        it('restituisce true quando un pixel acceso viene spento (collisione)', function (): void {
            $this->display->drawSprite(0, 0, [0xFF]);
            $collision = $this->display->drawSprite(0, 0, [0xFF]);

            expect($collision)->toBeTrue();
        });

        it('spegne i pixel via XOR quando si ridisegna lo stesso sprite', function (): void {
            $this->display->drawSprite(0, 0, [0xFF]);
            $this->display->drawSprite(0, 0, [0xFF]);

            // Tutti i pixel devono essere tornati a 0
            expect($this->display->getPixel(0, 0))->toBe(0);
            expect($this->display->getPixel(7, 0))->toBe(0);
        });

        it('wrappa le coordinate orizzontalmente', function (): void {
            // Disegna a x=62: i bit 0 e 1 dello sprite (0xC0 = 11000000) escono a destra
            // e rientrano a sinistra (colonna 0 e 1)
            $this->display->drawSprite(62, 0, [0xC0]);

            expect($this->display->getPixel(62, 0))->toBe(1);
            expect($this->display->getPixel(63, 0))->toBe(1);
            expect($this->display->getPixel(0, 0))->toBe(0); // 0xC0 ha solo i 2 MSB, non arriva a wrappare
        });

        it('wrappa le coordinate verticalmente', function (): void {
            // Disegna con y=31 (ultima riga): la seconda riga dello sprite wrappa a y=0
            $this->display->drawSprite(0, 31, [0xFF, 0xAA]);

            expect($this->display->getPixel(0, 31))->toBe(1); // prima riga dello sprite
            expect($this->display->getPixel(0, 0))->toBe(1);  // seconda riga wrappata
        });

        it('non accende pixel per i bit 0 dello sprite', function (): void {
            // 0x00 = 00000000 → nessun pixel acceso
            $this->display->drawSprite(0, 0, [0x00]);

            for ($col = 0; $col < 8; $col++) {
                expect($this->display->getPixel($col, 0))->toBe(0);
            }
        });

    });

});
