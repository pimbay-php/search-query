<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Cursor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;
use PimBay\SearchQuery\Cursor\Cursor;
use PimBay\SearchQuery\Cursor\CursorAdapter;
use PimBay\SearchQuery\Cursor\CursorAssembler;
use PimBay\SearchQuery\Cursor\CursorChunk;
use PimBay\SearchQuery\Exception\EmptyCursorException;
use PimBay\SearchQuery\Exception\InvalidSizeException;

final class CursorAssemblerTest extends TestCase
{
    /**
     * @return iterable<string, array{array<int, string>, array<array-key, string>, int, bool, bool}>
     */
    public static function pageProvider(): iterable
    {
        yield 'first page without a cursor' => [['a', 'b', 'c'], ['a', 'b'], 2, true, true];
        yield 'empty adapter returns empty result' => [[], [], 0, false, false];
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidSizeProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
    }

    /**
     * @param array<int, string> $items
     * @param array<array-key, string> $expectedData
     */
    #[Test]
    #[DataProvider('pageProvider')]
    public function pagination(
        array $items,
        array $expectedData,
        int $expectedCurrentCount,
        bool $expectedHasNext,
        bool $expectedHasCursor,
    ): void {
        $result = (new CursorAssembler())->paginate(new InMemoryArrayAdapter($items), null, 2);

        self::assertSame($expectedData, $result->getData());
        self::assertSame($expectedCurrentCount, $result->getCurrentCount());
        self::assertSame($expectedHasNext, $result->hasNextPage());

        if ($expectedHasCursor) {
            self::assertIsString($result->getNextCursor());
        } else {
            self::assertNull($result->getNextCursor());
        }
    }

    #[Test]
    public function throwsOnEmptyCursor(): void
    {
        $this->expectException(EmptyCursorException::class);
        $this->expectExceptionMessage('Cursor must not be empty.');

        (new CursorAssembler())->paginate(new InMemoryArrayAdapter(['a', 'b']), '', 2);
    }

    #[Test]
    public function throwsWhenAdapterReturnsAnEmptyNextCursor(): void
    {
        $adapter = new class implements CursorAdapter {
            public function pageAfter(?string $cursor, int $size): CursorChunk
            {
                return new CursorChunk(['a'], '', true);
            }
        };

        $this->expectException(EmptyCursorException::class);
        $this->expectExceptionMessage('Cursor must not be empty.');

        (new CursorAssembler())->paginate($adapter, null, 2);
    }

    #[Test]
    #[DataProvider('invalidSizeProvider')]
    public function throwsOnInvalidSize(int $size): void
    {
        $this->expectException(InvalidSizeException::class);

        (new CursorAssembler())->paginate(new InMemoryArrayAdapter(['a']), null, $size);
    }

    #[Test]
    public function sizeOfOneIsTheSmallestValidSize(): void
    {
        $result = (new CursorAssembler())->paginate(new InMemoryArrayAdapter(['a', 'b']), null, 1);

        self::assertSame(['a'], $result->getData());
        self::assertSame(1, $result->getPageSize());
    }

    #[Test]
    public function emptyResultsAreNormalizedRegardlessOfWhatTheChunkClaims(): void
    {
        $adapter = new class implements CursorAdapter {
            public function pageAfter(?string $cursor, int $size): CursorChunk
            {
                return new CursorChunk([], 'stale', true);
            }
        };

        $result = (new CursorAssembler())->paginate($adapter, null, 2);

        self::assertSame([], $result->getData());
        self::assertFalse($result->hasNextPage());
        self::assertNull($result->getNextCursor());
    }

    #[Test]
    public function traversableAdapterResultsAreMaterialized(): void
    {
        $adapter = new class implements CursorAdapter {
            public function pageAfter(?string $cursor, int $size): CursorChunk
            {
                $generator = (static function (): \Generator {
                    yield 'a';
                    yield 'b';
                })();

                return new CursorChunk($generator, null, false);
            }
        };

        $result = (new CursorAssembler())->paginate($adapter, null, 2);

        self::assertSame(['a', 'b'], $result->getData());
        self::assertSame(2, $result->getCurrentCount());
    }

    #[Test]
    public function mapsDataAndPreservesPaginationMetadata(): void
    {
        $cursor = new Cursor(['a', 'b'], 2, 2, 'abc', true);

        $mapped = (new CursorAssembler())->map($cursor, static fn (string $value): string => strtoupper($value));

        self::assertSame(['A', 'B'], $mapped->getData());
        self::assertSame(2, $mapped->getCurrentCount());
        self::assertSame(2, $mapped->getPageSize());
        self::assertSame('abc', $mapped->getNextCursor());
        self::assertTrue($mapped->hasNextPage());
    }
}
