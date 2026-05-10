<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$emulator = new Chip8\Emulator();
$emulator->loadRom(__DIR__ . '/../roms/5-quirks.ch8');
$emulator->run(); // loop bloccante — Ctrl+C per uscire
