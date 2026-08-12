<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Slice;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;
use PimBay\SearchQuery\Exception\InvalidPageException;
use PimBay\SearchQuery\Exception\InvalidSizeException;
use PimBay\SearchQuery\Exception\OutOfRangeException;
use PimBay\SearchQuery\Slice\Slice;
use PimBay\SearchQuery\Slice\SliceAdapter;
use PimBay\SearchQuery\Slice\SliceAssembler;
use PimBay\SearchQuery\Slice\SliceChunk;

final class SliceAssemblerTest extends TestCase
{
    /**
     * @return iterable<string, array{int, array<array-key, string>, bool, bool}>
     */
    public static function pageProvider(): iterable
    {
        yield 'first page reports has more' => [1, ['a', 'b'], true, false];
        yield 'last page reports no more' => [2, [2 => 'c'], false, true];
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidPageProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
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
     * @return iterable<string, array{int, class-string<\Throwable>, string}>
     */
    public static function paginateOrThrowThrowsProvider(): iterable
    {
        yield 'invalid page' => [0, InvalidPageException::class, 'Page must be at least one, 0 given.'];
        yield 'page past the end' => [3, OutOfRangeException::class, 'Page 3 is out of range.'];
    }

    /**
     * @param array<array-key, string> $expectedData
     */
    #[Test]
    #[DataProvider('pageProvider')]
    public function paginatesThreeItemsInPagesOfTwo(
        int $page,
        array $expectedData,
        bool $expectedHasNext,
        bool $expectedHasPrevious,
    ): void {
        $result = (new SliceAssembler())->paginate(
            new InMemoryArrayAdapter(['a', 'b', 'c']),
            $page,
            2,
        );

        self::assertSame($expectedData, $result->getData());
        self::assertSame($expectedHasNext, $result->hasNextPage());
        self::assertSame($expectedHasPrevious, $result->hasPreviousPage());
    }

    #[Test]
    #[DataProvider('invalidPageProvider')]
    public function throwsOnInvalidPage(int $page): void
    {
        $this->expectException(InvalidPageException::class);

        (new SliceAssembler())->paginate(new InMemoryArrayAdapter(['a']), $page, 2);
    }

    #[Test]
    #[DataProvider('invalidSizeProvider')]
    public function throwsOnInvalidSize(int $size): void
    {
        $this->expectException(InvalidSizeException::class);

        (new SliceAssembler())->paginate(new InMemoryArrayAdapter(['a']), 1, $size);
    }

    #[Test]
    public function sizeOfOneIsTheSmallestValidSize(): void
    {
        $result = (new SliceAssembler())->paginate(new InMemoryArrayAdapter(['a', 'b']), 1, 1);

        self::assertSame(['a'], $result->getData());
        self::assertSame(1, $result->getPageSize());
    }

    #[Test]
    public function traversableAdapterResultsAreMaterialized(): void
    {
        $adapter = new class implements SliceAdapter {
            public function pageSlice(int $offset, int $size): SliceChunk
            {
                $generator = (static function (): \Generator {
                    yield 'a';
                    yield 'b';
                })();

                return new SliceChunk($generator, false);
            }
        };

        $result = (new SliceAssembler())->paginate($adapter, 1, 2);

        self::assertSame(['a', 'b'], $result->getData());
        self::assertSame(2, $result->getCurrentCount());
    }

    #[Test]
    public function mapsDataAndPreservesPaginationMetadata(): void
    {
        $slice = new Slice(['a', 'b'], 2, 1, 2, true, false);

        $mapped = (new SliceAssembler())->map($slice, static fn (string $value): string => strtoupper($value));

        self::assertSame(['A', 'B'], $mapped->getData());
        self::assertSame(2, $mapped->getCurrentCount());
        self::assertSame(1, $mapped->getCurrentPage());
        self::assertSame(2, $mapped->getPageSize());
        self::assertTrue($mapped->hasNextPage());
        self::assertFalse($mapped->hasPreviousPage());
    }

    #[Test]
    public function paginateOrThrowReturnsTheResultWhenInRange(): void
    {
        $result = (new SliceAssembler())->paginateOrThrow(
            new InMemoryArrayAdapter(['a', 'b', 'c']),
            1,
            2,
        );

        self::assertSame(['a', 'b'], $result->getData());
    }

    /**
     * @param class-string<\Throwable> $expectedException
     */
    #[Test]
    #[DataProvider('paginateOrThrowThrowsProvider')]
    public function paginateOrThrowThrows(int $page, string $expectedException, string $expectedMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        (new SliceAssembler())->paginateOrThrow(new InMemoryArrayAdapter(['a', 'b', 'c']), $page, 2);
    }
}
