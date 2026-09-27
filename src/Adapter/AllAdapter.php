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
 * Separate from HeadableAdapter on purpose — implementing a bounded read must never implicitly
 * grant unbounded ones, so this capability is opted into explicitly.
 *
 * @template-covariant T
 */
interface AllAdapter
{
    /**
     * @return iterable<array-key, T>
     */
    public function all(): iterable;
}
