<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;

final class InMemoryArrayAdapterTest extends TestCase
{
    /**
     * @return iterable<string, array{array<array-key, string>, \Closure|null, array<array-key, string|int>}>
     */
    public static function idsProvider(): iterable
    {
        yield 'default to array keys' => [[10 => 'a', 20 => 'b'], null, [10, 20]];
        yield 'use extractId when given' => [
            ['a', 'b'],
            static fn (string $value): string => strtoupper($value),
            ['A', 'B'],
        ];
    }

    /**
     * @return iterable<string, array{array<array-key, string>, int, int, array<array-key, string>, bool}>
     */
    public static function pageSliceProvider(): iterable
    {
        yield 'reports has more when an extra row exists' => [['a', 'b', 'c'], 0, 2, ['a', 'b'], true];
        yield 'only reads one row ahead of size' => [['a', 'b', 'c', 'd'], 0, 2, ['a', 'b'], true];
        yield 'on an exactly full last page has no more' => [['a', 'b'], 0, 2, ['a', 'b'], false];
        yield 'reports no more on the last page' => [['a', 'b', 'c'], 2, 2, [2 => 'c'], false];
    }

    /**
     * @return iterable<string, array{array<array-key, string>, string|null, int, array<array-key, string>, bool, string|null}>
     */
    public static function pageAfterProvider(): iterable
    {
        yield 'without cursor starts from the beginning' => [['a', 'b', 'c'], null, 2, ['a', 'b'], true, '1'];
        yield 'only reads one row ahead of size' => [['a', 'b', 'c', 'd'], null, 2, ['a', 'b'], true, '1'];
        yield 'on an exactly full last page has no more' => [['a', 'b'], null, 2, ['a', 'b'], false, null];
        yield 'continues from a given cursor' => [['a', 'b', 'c'], '1', 2, [2 => 'c'], false, null];
        yield 'with an unknown cursor restarts from the beginning' => [
            ['a', 'b', 'c'],
            'does-not-exist',
            2,
            ['a', 'b'],
            true,
            '1',
        ];
    }

    #[Test]
    public function countValue(): void
    {
        $adapter = new InMemoryArrayAdapter(['a', 'b', 'c']);

        self::assertSame(3, $adapter->count());
    }

    /**
     * @param array<array-key, string> $items
     * @param array<array-key, string|int> $expectedIds
     */
    #[Test]
    #[DataProvider('idsProvider')]
    public function ids(array $items, ?\Closure $extractId, array $expectedIds): void
    {
        self::assertSame($expectedIds, (new InMemoryArrayAdapter($items, $extractId))->ids());
    }

    #[Test]
    public function head(): void
    {
        $adapter = new InMemoryArrayAdapter(['a', 'b', 'c', 'd']);

        self::assertSame(['a', 'b'], $adapter->head(2));
    }

    #[Test]
    public function all(): void
    {
        $adapter = new InMemoryArrayAdapter(['a', 'b']);

        self::assertSame(['a', 'b'], $adapter->all());
    }

    #[Test]
    public function pageViewPreservesEntityIdKeys(): void
    {
        $adapter = new InMemoryArrayAdapter([10 => 'a', 20 => 'b', 30 => 'c']);

        $chunk = $adapter->pageView(1, 2);

        self::assertSame([20 => 'b', 30 => 'c'], $chunk->results);
    }

    #[Test]
    public function pageViewReturnsTotalCount(): void
    {
        $adapter = new InMemoryArrayAdapter(['a', 'b', 'c', 'd', 'e']);

        $chunk = $adapter->pageView(2, 2);

        self::assertSame([2 => 'c', 3 => 'd'], $chunk->results);
        self::assertSame(5, $chunk->totalCount);
    }

    /**
     * @param array<array-key, string> $items
     * @param array<array-key, string> $expectedResults
     */
    #[Test]
    #[DataProvider('pageSliceProvider')]
    public function pageSlice(array $items, int $offset, int $size, array $expectedResults, bool $expectedHasMore): void
    {
        $chunk = (new InMemoryArrayAdapter($items))->pageSlice($offset, $size);

        self::assertSame($expectedResults, $chunk->results);
        self::assertSame($expectedHasMore, $chunk->hasMore);
    }

    /**
     * @param array<array-key, string> $items
     * @param array<array-key, string> $expectedResults
     */
    #[Test]
    #[DataProvider('pageAfterProvider')]
    public function pageAfter(
        array $items,
        ?string $cursorValue,
        int $size,
        array $expectedResults,
        bool $expectedHasMore,
        ?string $expectedNextCursorValue,
    ): void {
        $chunk = (new InMemoryArrayAdapter($items))->pageAfter($cursorValue, $size);

        self::assertSame($expectedResults, $chunk->results);
        self::assertSame($expectedHasMore, $chunk->hasMore);
        self::assertSame($expectedNextCursorValue, $chunk->nextCursor);
    }
}
