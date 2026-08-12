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

use PimBay\SearchQuery\Slice\SliceResult;

/**
 * @template-covariant T
 *
 * @extends SliceResult<T>
 */
interface PageResult extends SliceResult
{
    public function getTotalCount(): int;

    public function getPageCount(): int;
}
