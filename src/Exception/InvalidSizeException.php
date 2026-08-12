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

final class InvalidSizeException extends SearchQueryException
{
    public function __construct(int $size)
    {
        parent::__construct(\sprintf('Size must be a positive integer, %d given.', $size));
    }
}
