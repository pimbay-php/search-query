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
 * @template-covariant T
 */
interface SliceAdapter
{
    /**
     * Implementations are expected to determine `hasMore` cheaply (e.g. by
     * fetching `size + 1` rows and dropping the extra one), not via a separate
     * count query — that's the entire point of the Slice family over Page.
     *
     * @return SliceChunk<T>
     */
    public function pageSlice(int $offset, int $size): SliceChunk;
}
