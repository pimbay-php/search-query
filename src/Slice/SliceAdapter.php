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
     * `hasMore` must be determined cheaply (fetch `size + 1` rows, drop the extra one), never via a
     * separate count query — that is the entire point of the Slice family over Page.
     *
     * @return SliceChunk<T>
     */
    public function pageSlice(int $offset, int $size): SliceChunk;
}
