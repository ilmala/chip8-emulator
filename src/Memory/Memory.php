<?php

declare(strict_types=1);

namespace Chip8\Memory;

use RangeException;
use RuntimeException;

final class Memory
{
    private const int SIZE = 0x1000; // 4096 bytes

    private const int FONT_START = 0x050;

    private const int PROG_START = 0x200;

    // 16 sprites (0x0–0xF), 5 bytes each = 80 bytes
    // Each byte represents one row of 8 pixels; only the top 4 bits are used per sprite
    private const array FONT_SET = [
        0xF0, 0x90, 0x90, 0x90, 0xF0, // 0
        0x20, 0x60, 0x20, 0x20, 0x70, // 1
        0xF0, 0x10, 0xF0, 0x80, 0xF0, // 2
        0xF0, 0x10, 0xF0, 0x10, 0xF0, // 3
        0x90, 0x90, 0xF0, 0x10, 0x10, // 4
        0xF0, 0x80, 0xF0, 0x10, 0xF0, // 5
        0xF0, 0x80, 0xF0, 0x90, 0xF0, // 6
        0xF0, 0x10, 0x20, 0x40, 0x40, // 7
        0xF0, 0x90, 0xF0, 0x90, 0xF0, // 8
        0xF0, 0x90, 0xF0, 0x10, 0xF0, // 9
        0xF0, 0x90, 0xF0, 0x90, 0x90, // A
        0xE0, 0x90, 0xE0, 0x90, 0xE0, // B
        0xF0, 0x80, 0x80, 0x80, 0xF0, // C
        0xE0, 0x90, 0x90, 0x90, 0xE0, // D
        0xF0, 0x80, 0xF0, 0x80, 0xF0, // E
        0xF0, 0x80, 0xF0, 0x80, 0x80, // F
    ];

    /** @var array<int, int<0, 255>> */
    private array $ram;

    public function __construct()
    {
        $this->ram = array_fill(0, self::SIZE, 0);
        $this->loadFont();
    }

    /**
     * Reads one byte from the given address.
     *
     * @param Uint16 $address
     * @return int<0, 255>
     */
    public function read(int $address): int
    {
        $this->assertAddress($address);

        return $this->ram[$address];
    }

    /**
     * Writes one byte to the given address.
     *
     * @param Uint16 $address
     * @param Uint8 $value
     */
    public function write(int $address, int $value): void
    {
        $this->assertAddress($address);

        $this->ram[$address] = $value;
    }

    /**
     * Reads two consecutive bytes and returns a big-endian 16-bit word.
     * Used by the CPU to fetch one opcode (opcodes are always 2 bytes).
     *
     * @param Uint16 $address
     * @return Uint16
     */
    public function readWord(int $address): int
    {
        $this->assertAddress($address);

        // Validate the second byte's address separately to avoid int<1, 65536> range issues
        if ($address + 1 >= self::SIZE) {
            throw new RangeException(
                sprintf('readWord address 0x%04X would read past end of memory.', $address),
            );
        }

        return (($this->ram[$address] << 8) | $this->ram[$address + 1]) & 0xFFFF;
    }

    /**
     * Loads a ROM file into memory starting at 0x200.
     *
     * @throws RuntimeException if the file does not exist or is too large
     */
    public function loadRom(string $path): void
    {
        if ( ! file_exists($path)) {
            throw new RuntimeException(sprintf('ROM file not found: %s', $path));
        }

        $rom = file_get_contents($path);

        if (false === $rom) {
            throw new RuntimeException(sprintf('Could not read ROM file: %s', $path));
        }

        $maxSize = self::SIZE - self::PROG_START;

        if (mb_strlen($rom) > $maxSize) {
            throw new RuntimeException(
                sprintf('ROM is too large: %d bytes (max %d).', mb_strlen($rom), $maxSize),
            );
        }

        $bytes = array_values(unpack('C*', $rom) ?: []);

        foreach ($bytes as $offset => $byte) {
            $this->ram[self::PROG_START + $offset] = $byte;
        }
    }

    /**
     * Returns the memory address of the built-in font sprite for a given hex digit.
     *
     * @param int $digit hex digit 0–15
     * @return Uint16
     */
    public function fontAddress(int $digit): int
    {
        if ($digit < 0 || $digit > 0xF) {
            throw new RangeException(
                sprintf('Font digit must be 0–15, got %d.', $digit),
            );
        }

        return self::FONT_START + ($digit * 5);
    }

    /**
     * Validates that an address falls within the 4KB RAM space.
     * Accepts a raw int so it can serve as a boundary check before type narrowing.
     *
     * @phpstan-assert int<0, 4095> $address
     */
    private function assertAddress(int $address): void
    {
        if ($address < 0 || $address >= self::SIZE) {
            throw new RangeException(
                sprintf('Memory address out of range: 0x%04X (valid: 0x0000–0x0FFF).', $address),
            );
        }
    }

    private function loadFont(): void
    {
        foreach (self::FONT_SET as $offset => $byte) {
            $this->ram[self::FONT_START + $offset] = $byte;
        }
    }
}
