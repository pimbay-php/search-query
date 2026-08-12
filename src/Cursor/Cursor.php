<?php

declare(strict_types=1);

/**
 * This file is part of the PimBay Search Query library.
 *
 * @author Jan Sarmir <sarmir@pimbay.dev>
 * @link   https://pimbay.dev
 *
 * For the full license information, see the LICENSE file.
 */

namespace PimBay\SearchQuery\Cursor;

/**
 * @template T
 *
 * @implements CursorResult<T>
 */
final class Cursor implements CursorResult
{
    /**
     * @param iterable<array-key, T> $data
     */
    public function __construct(
        private readonly iterable $data,
        private readonly int $currentCount,
        private readonly int $pageSize,
        private readonly ?string $nextCursor,
        private readonly bool $hasNextPage,
    ) {
    }

    /**
     * @return self<T>
     */
    public static function empty(int $pageSize): self
    {
        return new self([], 0, $pageSize, null, false);
    }

    public function getData(): iterable
    {
        return $this->data;
    }

    public function getIterator(): \Traversable
    {
        return \is_array($this->data) ? new \ArrayIterator($this->data) : $this->data;
    }

    public function getCurrentCount(): int
    {
        return $this->currentCount;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }
}
