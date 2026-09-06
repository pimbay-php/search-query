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

/**
 * Splits raw user-provided search terms into equals/notEquals/likes/notLikes buckets
 * based on a leading negation marker and an embedded wildcard marker.
 * Pure string parsing — no SQL, no column/field mapping, no datasource awareness.
 * Datasource-specific packages turn a ParsedSearchTerms into actual query conditions against a concrete column.
 */
final class SearchTermsParser
{
    public function parseString(string $text, SearchTermsConfig $config): ParsedSearchTerms
    {
        return $this->parse($this->splitAndTrim($text), $config);
    }

    /**
     * @param string[] $terms raw, as typed by a user (e.g. split on whitespace upstream)
     */
    public function parse(array $terms, SearchTermsConfig $config): ParsedSearchTerms
    {
        $equals = [];
        $notEquals = [];
        $likes = [];
        $notLikes = [];

        foreach ($terms as $value) {
            if (\strlen($value) < $config->minLength) {
                continue;
            }

            $negated = str_starts_with($value, $config->ignoreChar);
            $body = $negated ? substr($value, \strlen($config->ignoreChar)) : $value;

            if ('' === $body) {
                continue;
            }

            $isLike = str_contains($body, $config->likeChar);

            if ($negated && $isLike) {
                $notLikes[] = $body;
            } elseif ($negated) {
                $notEquals[] = $body;
            } elseif ($isLike) {
                $likes[] = $body;
            } else {
                $equals[] = $body;
            }
        }

        return new ParsedSearchTerms($equals, $notEquals, $likes, $notLikes);
    }

    /**
     * @return string[]
     */
    private function splitAndTrim(string $text): array
    {
        return preg_split('/\s+/', $text) ?: [];
    }
}
