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

final readonly class ParsedSearchTerms
{
    /**
     * @param string[] $equals
     * @param string[] $notEquals
     * @param string[] $likes
     * @param string[] $notLikes negation marker stripped, like marker kept
     */
    public function __construct(
        public array $equals,
        public array $notEquals,
        public array $likes,
        public array $notLikes,
    ) {
    }

    public function isEmpty(): bool
    {
        return [] === $this->equals && [] === $this->notEquals && [] === $this->likes && [] === $this->notLikes;
    }
}
