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

namespace PimBay\SearchQuery\SearchTerms;

use PimBay\SearchQuery\Exception\InvalidSearchTermsConfigException;

final readonly class SearchTermsConfig
{
    /**
     * @param bool $anywhere pass-through hint for a datasource-specific package's query builder
     * @param int $minLength counts the ignoreChar marker too, e.g. `-ab` needs minLength <= 3
     */
    public function __construct(
        public bool $anywhere = true,
        public int $minLength = 3,
        public string $likeChar = '*',
        public string $ignoreChar = '-',
    ) {
        if ('' === $this->likeChar) {
            throw new InvalidSearchTermsConfigException('likeChar must not be empty');
        }

        if ('' === $this->ignoreChar) {
            throw new InvalidSearchTermsConfigException('ignoreChar must not be empty');
        }

        if ($this->likeChar === $this->ignoreChar) {
            throw new InvalidSearchTermsConfigException('likeChar and ignoreChar must differ');
        }

        if ($this->minLength < 0) {
            throw new InvalidSearchTermsConfigException('minLength must be zero or greater');
        }
    }
}
