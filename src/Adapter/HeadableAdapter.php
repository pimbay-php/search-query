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

namespace PimBay\SearchQuery\Adapter;

/**
 * Bounded read from the start of a result set — unrelated to page navigation, for known-small
 * result sets (e.g. a filtered listing forwarded to a view helper).
 *
 * @template-covariant T
 */
interface HeadableAdapter
{
    /**
     * @return iterable<array-key, T>
     */
    public function head(int $size): iterable;
}
