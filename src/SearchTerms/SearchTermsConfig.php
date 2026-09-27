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
    /** @var list<string> */
    public array $likeMarkers;

    /** @var list<string> */
    public array $ignoreMarkers;

    /**
     * @param string[] $likeMarkers
     * @param string[] $ignoreMarkers
     */
    public function __construct(
        public bool $anywhere = true,
        public int $minLength = 3,
        array $likeMarkers = ['*'],
        array $ignoreMarkers = ['-', '!'],
    ) {
        $this->likeMarkers = self::normalize($likeMarkers, 'likeMarkers');
        $this->ignoreMarkers = self::normalize($ignoreMarkers, 'ignoreMarkers');

        if ([] !== array_intersect($this->likeMarkers, $this->ignoreMarkers)) {
            throw new InvalidSearchTermsConfigException('likeMarkers and ignoreMarkers must not overlap');
        }

        if ($this->minLength < 0) {
            throw new InvalidSearchTermsConfigException('minLength must be zero or greater');
        }
    }

    /**
     * Longest first, so that a marker which is a prefix of another one (`-` next to `--`) never
     * shadows it — matching would otherwise depend on the order the caller happened to pass.
     *
     * @param string[] $markers
     *
     * @return list<string>
     */
    private static function normalize(array $markers, string $name): array
    {
        foreach ($markers as $marker) {
            if ('' === $marker) {
                throw new InvalidSearchTermsConfigException(\sprintf('%s must not contain an empty marker', $name));
            }
        }

        if (\count(array_unique($markers)) !== \count($markers)) {
            throw new InvalidSearchTermsConfigException(\sprintf('%s must not contain duplicates', $name));
        }

        // usort reindexes, so the returned array is a list regardless of the keys passed in.
        usort($markers, static fn (string $a, string $b): int => \strlen($b) <=> \strlen($a));

        return $markers;
    }
}
