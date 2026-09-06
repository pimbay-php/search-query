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

namespace PimBay\SearchQuery\Slice;

/**
 * @template T
 *
 * @implements SliceResult<T>
 */
readonly class Slice implements SliceResult
{
    /**
     * @param iterable<array-key, T> $data
     */
    public function __construct(
        private iterable $data,
        private int $currentCount,
        private int $currentPage,
        private int $pageSize,
        private bool $hasNextPage,
        private bool $hasPreviousPage,
    ) {
    }

    /**
     * @return self<T>
     */
    public static function empty(): self
    {
        return new self([], 0, 1, 0, false, false);
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

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function hasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }

    public function getCurrentOffset(): int
    {
        return $this->pageSize * ($this->currentPage - 1);
    }

    public function isOutOfRange(): bool
    {
        return $this->currentPage > 1 && 0 === $this->currentCount;
    }
}
