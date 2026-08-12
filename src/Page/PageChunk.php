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

/**
 * An internal computation intermediate consumed by PageAssembler, not the public result type.
 *
 * @template-covariant T
 */
final readonly class PageChunk
{
    /**
     * @param iterable<array-key, T> $results
     */
    public function __construct(
        public iterable $results,
        public int $totalCount,
    ) {
    }
}
