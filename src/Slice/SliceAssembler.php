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

namespace PimBay\SearchQuery\Slice;

use PimBay\SearchQuery\Exception\InvalidPageException;
use PimBay\SearchQuery\Exception\InvalidSizeException;
use PimBay\SearchQuery\Exception\OutOfRangeException;

final class SliceAssembler
{
    /**
     * @template T
     * @template R
     *
     * @param SliceResult<T> $sliceResult
     * @param callable(T): R $mapping
     *
     * @return SliceResult<R>
     */
    public function map(SliceResult $sliceResult, callable $mapping): SliceResult
    {
        $data = [];

        foreach ($sliceResult->getData() as $value) {
            $data[] = $mapping($value);
        }

        return new Slice(
            $data,
            $sliceResult->getCurrentCount(),
            $sliceResult->getCurrentPage(),
            $sliceResult->getPageSize(),
            $sliceResult->hasNextPage(),
            $sliceResult->hasPreviousPage(),
        );
    }

    /**
     * @template T
     *
     * @param SliceAdapter<T> $adapter
     *
     * @return SliceResult<T>
     */
    public function paginate(SliceAdapter $adapter, int $page, int $size): SliceResult
    {
        if ($page < 1) {
            throw new InvalidPageException($page);
        }

        if ($size < 1) {
            throw new InvalidSizeException($size);
        }

        $offset = ($page - 1) * $size;
        $chunk = $adapter->pageSlice($offset, $size);

        $results = \is_array($chunk->results) ? $chunk->results : iterator_to_array($chunk->results);

        return new Slice(
            $results,
            \count($results),
            $page,
            $size,
            $chunk->hasMore,
            $page > 1,
        );
    }

    /**
     * @template T
     *
     * @param SliceAdapter<T> $adapter
     *
     * @return SliceResult<T>
     */
    public function paginateOrThrow(SliceAdapter $adapter, int $page, int $size): SliceResult
    {
        $result = $this->paginate($adapter, $page, $size);

        if ($result->isOutOfRange()) {
            throw new OutOfRangeException($page);
        }

        return $result;
    }
}
