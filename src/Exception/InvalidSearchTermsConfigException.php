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

final class InvalidSearchTermsConfigException extends SearchQueryException
{
    public function __construct(string $reason)
    {
        parent::__construct(\sprintf('Invalid SearchTermsConfig: %s.', $reason));
    }
}
