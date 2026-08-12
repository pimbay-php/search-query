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
 * An internal computation intermediate consumed by CursorAssembler, not the public result type.
 *
 * @template-covariant T
 */
final readonly class CursorChunk
{
    /**
     * @param iterable<array-key, T> $results
     */
    public function __construct(
        public iterable $results,
        public ?string $nextCursor,
        public bool $hasMore,
    ) {
    }
}
