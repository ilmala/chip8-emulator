<?php

declare(strict_types=1);

namespace Chip8\Keyboard;

use RangeException;

final class Keyboard
{
    public const int KEY_COUNT = 16;

    /** @var array<int<0, 15>, bool> */
    private array $keys;

    public function __construct()
    {
        $this->keys = array_fill(0, self::KEY_COUNT, false);
    }

    public function press(int $key): void
    {
        $this->assertKey($key);

        $this->keys[$key] = true;
    }

    public function release(int $key): void
    {
        $this->assertKey($key);

        $this->keys[$key] = false;
    }

    public function isPressed(int $key): bool
    {
        $this->assertKey($key);

        return $this->keys[$key];
    }

    /**
     * Returns the first currently-pressed key, or null if none are pressed.
     * Used by opcode FX0A ("wait for key press").
     *
     * @return int<0, 15>|null
     */
    public function getFirstPressedKey(): ?int
    {
        foreach ($this->keys as $key => $pressed) {
            if ($pressed) {
                return $key;
            }
        }

        return null;
    }

    public function releaseAll(): void
    {
        $this->keys = array_fill(0, self::KEY_COUNT, false);
    }

    /** @phpstan-assert int<0, 15> $key */
    private function assertKey(int $key): void
    {
        if ($key < 0 || $key >= self::KEY_COUNT) {
            throw new RangeException(
                sprintf('Key must be 0–15, got %d.', $key),
            );
        }
    }
}
