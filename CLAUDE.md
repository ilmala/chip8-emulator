# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A technically accurate CHIP-8 emulator written in PHP 8.5 (CLI mode). The system loads a ROM, interprets opcodes, and manages internal state (CPU, Memory, Registers, Stack).

## Commands

**Install dependencies:**
```bash
composer install
```

**Run tests (Pest PHP — add to require-dev when setting up):**
```bash
./vendor/bin/pest
./vendor/bin/pest --filter=SomeName   # single test
```

**Static analysis (PHPStan Level 9 — add to require-dev when setting up):**
```bash
./vendor/bin/phpstan analyse
```

## Architecture

Domain-Driven Design. The `Chip8\` namespace maps to `src/` via PSR-4.

Planned domain modules:
- **CPU** — opcode fetch/decode/execute loop, 60Hz tick
- **Memory** — 4KB RAM, ROM loading
- **Registers** — 16 general-purpose registers V0–VF, plus I, PC, delay/sound timers
- **Stack** — 16-level call stack for subroutine support
- **I/O** — 64×32 display, 16-key hexadecimal keypad

## Code style

- PHP 8.5 with `declare(strict_types=1)` everywhere
- Readonly properties and Typed Enums preferred
- PHPStan at Level 9 / Bleeding Edge — all code must pass at this strictness
