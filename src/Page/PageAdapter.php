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
 * Strictly for bounded, page-numbered navigation. Not meant to also serve "give me everything" reads.
 *
 * @template-covariant T
 */
interface PageAdapter
{
    /**
     * @return PageChunk<T>
     */
    public function pageView(int $offset, int $size): PageChunk;
}
