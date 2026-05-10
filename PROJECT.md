# Realizzare un emulatore CHIP-8


Considerando che siamo nel 2026, useremo le proprietà readonly, i Typed Enums, e le performance migliorate di PHP 8.5 per gestire i 60Hz del sistema senza sudare.

L'obiettivo è creare un emulatore CHIP-8 tecnicamente accurato, scritto in PHP 8.5 con tipizzazione rigorosa. Il sistema deve caricare una ROM, interpretare gli opcode e gestire lo stato interno (CPU, Memoria, Registri, Stack).

## Tech Stack

- Runtime: PHP 8.5 (CLI mode)
- Package Manager: Composer 
- Testing: Pest PHP
- Static Analysis: PHPStan (Level 9 / Bleeding Edge)
- Architecture: Domain-Driven (CPU, Memory, I/O)