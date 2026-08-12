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

namespace PimBay\SearchQuery\Cursor;

use PimBay\SearchQuery\Exception\EmptyCursorException;
use PimBay\SearchQuery\Exception\InvalidSizeException;

final class CursorAssembler
{
    /**
     * @template T
     * @template R
     *
     * @param CursorResult<T> $cursorResult
     * @param callable(T): R $mapping
     *
     * @return CursorResult<R>
     */
    public function map(CursorResult $cursorResult, callable $mapping): CursorResult
    {
        $data = [];

        foreach ($cursorResult->getData() as $value) {
            $data[] = $mapping($value);
        }

        return new Cursor(
            $data,
            $cursorResult->getCurrentCount(),
            $cursorResult->getPageSize(),
            $cursorResult->getNextCursor(),
            $cursorResult->hasNextPage(),
        );
    }

    /**
     * @template T
     *
     * @param CursorAdapter<T> $adapter
     *
     * @return CursorResult<T>
     */
    public function paginate(CursorAdapter $adapter, ?string $cursor, int $size): CursorResult
    {
        if ('' === $cursor) {
            throw new EmptyCursorException();
        }

        if ($size < 1) {
            throw new InvalidSizeException($size);
        }

        $chunk = $adapter->pageAfter($cursor, $size);

        if ('' === $chunk->nextCursor) {
            throw new EmptyCursorException();
        }

        $results = \is_array($chunk->results) ? $chunk->results : iterator_to_array($chunk->results);

        if ([] === $results) {
            /** @var CursorResult<T> $empty */
            $empty = Cursor::empty($size);

            return $empty;
        }

        return new Cursor(
            $results,
            \count($results),
            $size,
            $chunk->nextCursor,
            $chunk->hasMore,
        );
    }
}
