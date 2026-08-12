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

namespace PimBay\SearchQuery\Adapter;

use PimBay\SearchQuery\Cursor\CursorAdapter;
use PimBay\SearchQuery\Cursor\CursorChunk;
use PimBay\SearchQuery\Page\PageAdapter;
use PimBay\SearchQuery\Page\PageChunk;
use PimBay\SearchQuery\Slice\SliceAdapter;
use PimBay\SearchQuery\Slice\SliceChunk;

/**
 * Reference implementation over a plain PHP array — for tests and small, already-loaded collections.
 *
 * @template T
 * @template Id of int|string
 *
 * @implements PageAdapter<T>
 * @implements SliceAdapter<T>
 * @implements CursorAdapter<T>
 * @implements HeadableAdapter<T>
 * @implements AllAdapter<T>
 * @implements IdentifiableAdapter<Id>
 */
final readonly class InMemoryArrayAdapter implements PageAdapter, SliceAdapter, CursorAdapter, CountableAdapter, IdentifiableAdapter, HeadableAdapter, AllAdapter
{
    /**
     * @param array<array-key, T> $items keys are preserved through pageView()/pageSlice()/head() — an int-keyed
     *                                   array (e.g. keyed by entity ID) keeps those keys on every returned page
     * @param (\Closure(T): Id)|null $extractId defaults to the array's own keys when omitted
     */
    public function __construct(
        private array $items,
        private ?\Closure $extractId = null,
    ) {
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function ids(): array
    {
        if (null === $this->extractId) {
            /** @var array<Id> $ids */
            $ids = array_keys($this->items);

            return $ids;
        }

        /** @var array<Id> $ids */
        $ids = array_map($this->extractId, $this->items);

        return $ids;
    }

    public function head(int $size): iterable
    {
        return \array_slice($this->items, 0, $size, preserve_keys: true);
    }

    public function all(): iterable
    {
        return $this->items;
    }

    public function pageView(int $offset, int $size): PageChunk
    {
        return new PageChunk(\array_slice($this->items, $offset, $size, preserve_keys: true), \count($this->items));
    }

    public function pageSlice(int $offset, int $size): SliceChunk
    {
        $slice = \array_slice($this->items, $offset, $size + 1, preserve_keys: true);
        $hasMore = \count($slice) > $size;

        if ($hasMore) {
            array_pop($slice);
        }

        return new SliceChunk($slice, $hasMore);
    }

    public function pageAfter(?string $cursor, int $size): CursorChunk
    {
        $startIndex = 0;

        if (null !== $cursor) {
            $keys = array_map(strval(...), array_keys($this->items));
            $position = array_search($cursor, $keys, true);
            // Stale/unknown cursor (can't happen within one instance's lifetime,
            // since $items never changes) — restart from the beginning rather than throw.
            $startIndex = false === $position ? 0 : $position + 1;
        }

        $slice = \array_slice($this->items, $startIndex, $size + 1, preserve_keys: true);
        $hasMore = \count($slice) > $size;

        if ($hasMore) {
            array_pop($slice);
        }

        $lastKey = array_key_last($slice);
        $nextCursor = $hasMore && null !== $lastKey ? (string) $lastKey : null;

        return new CursorChunk($slice, $nextCursor, $hasMore);
    }
}
