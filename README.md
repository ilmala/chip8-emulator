# CHIP-8 Emulator

Emulatore CHIP-8 scritto in PHP 8.5, tecnicamente accurato e sviluppato come progetto didattico.
Carica una ROM, interpreta gli opcode e gestisce lo stato interno — CPU, Memoria, Registri, Stack — con rendering diretto nel terminale via ANSI.

## Requisiti

- PHP >= 8.4
- Composer

## Installazione

```bash
composer install
```

## Utilizzo

```php
<?php

require 'vendor/autoload.php';

$emulator = new Chip8\Emulator();
$emulator->loadRom('path/to/rom.ch8');
$emulator->run(); // loop bloccante — Ctrl+C per uscire
```

Il display viene renderizzato nel terminale a 60 Hz:

```
██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░
░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██░░██
...
```

## Architettura

Il progetto segue un'architettura Domain-Driven con namespace `Chip8\` mappato su `src/`.

| Modulo | Responsabilità |
|---|---|
| `Memory` | 4 KB RAM, caricamento ROM, font set built-in (0x050–0x09F) |
| `Registers` | 16 registri V0–VF, registro I, program counter PC |
| `Stack` | Stack LIFO a 16 livelli per indirizzi di ritorno subroutine |
| `Timer` | Delay timer e Sound timer — decremento a 60 Hz |
| `Display` | Framebuffer 64×32, XOR-draw sprite, rilevamento collisione |
| `Keyboard` | 16 tasti esadecimali (0x0–0xF), attesa keypress (FX0A) |
| `Opcode` | Value Object immutabile — decodifica word 16-bit in nibble fields |
| `Cpu` | Ciclo fetch-decode-execute, dispatch dei 35 opcode |
| `Emulator` | Composition root — wiring di tutti i sottosistemi e loop principale |
| `TerminalRenderer` | Rendering ANSI del framebuffer nel terminale |

## Specifiche CHIP-8

| Componente | Dettaglio |
|---|---|
| RAM | 4 KB (0x000–0xFFF) |
| ROM | Caricata a partire da 0x200 (max 3584 byte) |
| Registri | 16 × 8-bit (V0–VF) + I (16-bit) + PC (16-bit) |
| Stack | 16 livelli |
| Display | 64 × 32 pixel monocromatici |
| Tastiera | 16 tasti esadecimali |
| Velocità CPU | ~600 Hz |
| Timer | 60 Hz |
| Opcode | 35 istruzioni, 2 byte ciascuna (big-endian) |

## Sviluppo

Il workflow obbligatorio per ogni modifica al codice sorgente:

```bash
./vendor/bin/pint src/       # 1. formatta il codice
./vendor/bin/phpstan analyse  # 2. analisi statica (Level 9 / bleedingEdge)
./vendor/bin/pest             # 3. esegui i test
```

Oppure tramite gli script Composer:

```bash
composer test      # esegue Pest
composer analyse   # esegue PHPStan
```

Per eseguire un singolo file di test:

```bash
./vendor/bin/pest tests/Memory/MemoryTest.php
```

## Stato attuale

La struttura base è completa e tutti i moduli passano PHPStan Level 9 e la suite di test.

Gli opcode implementati nella CPU sono attualmente:

| Opcode | Istruzione | Stato |
|---|---|---|
| `00E0` | CLS — clear display | ✅ |
| `00EE` | RET — return from subroutine | ✅ |
| `EX9E` | SKP Vx — skip if key pressed | ✅ |
| `EXA1` | SKNP Vx — skip if key not pressed | ✅ |
| `FX07` | LD Vx, DT — load delay timer | ✅ |
| `FX18` | LD ST, Vx — set sound timer | ✅ |
| tutti gli altri | — | 🔲 da implementare |

## Tech Stack

- **Runtime:** PHP 8.5 (CLI)
- **Dipendenze:** Composer
- **Test:** [Pest PHP](https://pestphp.com) v4
- **Analisi statica:** [PHPStan](https://phpstan.org) v2, Level 9 / Bleeding Edge
- **Formattazione:** [Laravel Pint](https://laravel.com/docs/pint) (preset `per`)
