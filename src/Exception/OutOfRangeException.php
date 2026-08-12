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

namespace PimBay\SearchQuery\Exception;

/**
 * Raised by PageAssembler/SliceAssembler::paginateOrThrow(), not by paginate() itself.
 */
final class OutOfRangeException extends SearchQueryException
{
    public function __construct(int $page)
    {
        parent::__construct(\sprintf('Page %d is out of range.', $page));
    }
}
