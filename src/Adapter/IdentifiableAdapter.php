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
 * @template-covariant Id of int|string
 */
interface IdentifiableAdapter
{
    /**
     * @return array<Id>
     */
    public function ids(): array;
}
