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
 * No totalCount/pageCount — unknown without a separate count query; that's the distinction from Page.
 *
 * @template-covariant T
 *
 * @extends \IteratorAggregate<array-key, T>
 */
interface SliceResult extends \IteratorAggregate
{
    /**
     * @return iterable<array-key, T>
     */
    public function getData(): iterable;

    public function getCurrentCount(): int;

    public function getCurrentPage(): int;

    public function getPageSize(): int;

    public function hasNextPage(): bool;

    public function hasPreviousPage(): bool;

    public function getCurrentOffset(): int;

    public function isOutOfRange(): bool;
}
