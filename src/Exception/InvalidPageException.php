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

final class InvalidPageException extends SearchQueryException
{
    public function __construct(int $page)
    {
        parent::__construct(\sprintf('Page must be at least one, %d given.', $page));
    }
}
