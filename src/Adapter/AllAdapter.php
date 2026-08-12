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
 * Unbounded read of an entire result set. Deliberately a separate interface
 * from HeadableAdapter — implementing HeadableAdapter must never implicitly
 * grant unbounded reads; a caller/adapter opts into this capability
 * explicitly.
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
