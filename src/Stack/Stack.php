<?php

declare(strict_types=1);

namespace Chip8\Stack;

use OverflowException;
use UnderflowException;

final class Stack
{
    private const int MAX_DEPTH = 16;

    /** @var list<int<0, 65535>> */
    private array $frames = [];

    /** @param Uint16 $address */
    public function push(int $address): void
    {
        if (count($this->frames) >= self::MAX_DEPTH) {
            throw new OverflowException(
                sprintf('Stack overflow: maximum depth of %d reached.', self::MAX_DEPTH),
            );
        }

        $this->frames[] = $address;
    }

    /** @return Uint16 */
    public function pop(): int
    {
        if ($this->frames === []) {
            throw new UnderflowException('Stack underflow: cannot pop from an empty stack.');
        }

        return array_pop($this->frames);
    }

    public function depth(): int
    {
        return count($this->frames);
    }

    public function isEmpty(): bool
    {
        return $this->frames === [];
    }
}
