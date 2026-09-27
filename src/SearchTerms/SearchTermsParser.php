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
 * Pure string parsing — no SQL, no column/field mapping, no datasource awareness. Datasource-specific
 * packages turn a ParsedSearchTerms into query conditions against a concrete column.
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

            $body = $value;
            $negated = false;

            foreach ($config->ignoreMarkers as $marker) {
                if (str_starts_with($value, $marker)) {
                    $body = substr($value, \strlen($marker));
                    $negated = true;
                    break;
                }
            }

            if ('' === $body) {
                continue;
            }

            $isLike = $this->containsAnyMarker($body, $config->likeMarkers);

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
     * @param list<string> $markers
     */
    private function containsAnyMarker(string $body, array $markers): bool
    {
        foreach ($markers as $marker) {
            if (str_contains($body, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function splitAndTrim(string $text): array
    {
        return preg_split('/\s+/', $text) ?: [];
    }
}
