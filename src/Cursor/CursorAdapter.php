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
 * @template-covariant T
 */
interface CursorAdapter
{
    /**
     * @return CursorChunk<T>
     */
    public function pageAfter(?string $cursor, int $size): CursorChunk;
}
