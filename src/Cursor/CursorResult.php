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
 * Forward-only (no previousCursor) — add additively if bidirectional cursors
 * are needed later. No currentPage/pageCount/totalCount/currentOffset; the
 * cursor model has none of these concepts.
 *
 * @template-covariant T
 *
 * @extends \IteratorAggregate<array-key, T>
 */
interface CursorResult extends \IteratorAggregate
{
    /**
     * @return iterable<array-key, T>
     */
    public function getData(): iterable;

    public function getCurrentCount(): int;

    public function getPageSize(): int;

    public function getNextCursor(): ?string;

    public function hasNextPage(): bool;
}
