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

namespace PimBay\SearchQuery\Page;

use PimBay\SearchQuery\Exception\InvalidPageException;
use PimBay\SearchQuery\Exception\InvalidSizeException;
use PimBay\SearchQuery\Exception\OutOfRangeException;

final class PageAssembler
{
    /**
     * @template T
     * @template R
     *
     * @param PageResult<T> $pageResult
     * @param callable(T): R $mapping
     *
     * @return PageResult<R>
     */
    public function map(PageResult $pageResult, callable $mapping): PageResult
    {
        $data = [];

        foreach ($pageResult->getData() as $value) {
            $data[] = $mapping($value);
        }

        return new Page(
            $data,
            $pageResult->getCurrentCount(),
            $pageResult->getCurrentPage(),
            $pageResult->getPageSize(),
            $pageResult->hasNextPage(),
            $pageResult->hasPreviousPage(),
            $pageResult->getPageCount(),
            $pageResult->getTotalCount(),
        );
    }

    /**
     * @template T
     *
     * @param PageAdapter<T> $adapter
     *
     * @return PageResult<T>
     */
    public function paginate(PageAdapter $adapter, int $page, int $size): PageResult
    {
        if ($page < 1) {
            throw new InvalidPageException($page);
        }

        if ($size < 1) {
            throw new InvalidSizeException($size);
        }

        $offset = ($page - 1) * $size;
        $chunk = $adapter->pageView($offset, $size);

        if (0 === $chunk->totalCount) {
            return new Page([], 0, $page, $size, false, $page > 1, 1, 0);
        }

        $pagesCount = (int) ceil($chunk->totalCount / $size);
        $currentCount = match (true) {
            $page > $pagesCount => 0,
            $page < $pagesCount => $size,
            default => $chunk->totalCount - ($pagesCount - 1) * $size,
        };

        return new Page(
            $chunk->results,
            $currentCount,
            $page,
            $size,
            $page < $pagesCount,
            $page > 1,
            $pagesCount,
            $chunk->totalCount,
        );
    }

    /**
     * @template T
     *
     * @param PageAdapter<T> $adapter
     *
     * @return PageResult<T>
     */
    public function paginateOrThrow(PageAdapter $adapter, int $page, int $size): PageResult
    {
        $result = $this->paginate($adapter, $page, $size);

        if ($result->isOutOfRange()) {
            throw new OutOfRangeException($page);
        }

        return $result;
    }
}
