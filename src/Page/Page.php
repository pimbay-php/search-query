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

namespace PimBay\SearchQuery\Page;

use PimBay\SearchQuery\Slice\Slice;

/**
 * @template T
 *
 * @extends Slice<T>
 *
 * @implements PageResult<T>
 */
final readonly class Page extends Slice implements PageResult
{
    /**
     * @param iterable<array-key, T> $data
     */
    public function __construct(
        iterable $data,
        int $currentCount,
        int $currentPage,
        int $pageSize,
        bool $hasNextPage,
        bool $hasPreviousPage,
        private int $pageCount,
        private int $totalCount,
    ) {
        parent::__construct($data, $currentCount, $currentPage, $pageSize, $hasNextPage, $hasPreviousPage);
    }

    /**
     * @return self<T>
     */
    public static function empty(): self
    {
        return new self([], 0, 1, 0, false, false, 1, 0);
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }
}
