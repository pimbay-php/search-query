<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Page;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;
use PimBay\SearchQuery\Exception\InvalidPageException;
use PimBay\SearchQuery\Exception\InvalidSizeException;
use PimBay\SearchQuery\Exception\OutOfRangeException;
use PimBay\SearchQuery\Page\Page;
use PimBay\SearchQuery\Page\PageAssembler;

final class PageAssemblerTest extends TestCase
{
    /**
     * @return iterable<string, array{int, array<array-key, string>, int, bool, bool, bool}>
     */
    public static function pageProvider(): iterable
    {
        yield 'first page of three' => [1, ['a', 'b'], 2, true, false, false];
        yield 'last page has a remainder count' => [3, [4 => 'e'], 1, false, true, false];
        yield 'page beyond page count is out of range' => [4, [], 0, false, true, true];
    }

    /**
     * @return iterable<string, array{int, bool}>
     */
    public static function emptyAdapterProvider(): iterable
    {
        yield 'first page' => [1, false];
        yield 'beyond first page' => [2, true];
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
        yield 'page past the end' => [4, OutOfRangeException::class, 'Page 4 is out of range.'];
    }

    /**
     * @param array<array-key, string> $expectedData
     */
    #[Test]
    #[DataProvider('pageProvider')]
    public function paginatesFiveItemsInPagesOfTwo(
        int $page,
        array $expectedData,
        int $expectedCurrentCount,
        bool $expectedHasNext,
        bool $expectedHasPrevious,
        bool $expectedOutOfRange,
    ): void {
        $result = (new PageAssembler())->paginate(
            new InMemoryArrayAdapter(['a', 'b', 'c', 'd', 'e']),
            $page,
            2,
        );

        self::assertSame($expectedData, $result->getData());
        self::assertSame($expectedCurrentCount, $result->getCurrentCount());
        self::assertSame(3, $result->getPageCount());
        self::assertSame(5, $result->getTotalCount());
        self::assertSame($expectedHasNext, $result->hasNextPage());
        self::assertSame($expectedHasPrevious, $result->hasPreviousPage());
        self::assertSame($expectedOutOfRange, $result->isOutOfRange());
    }

    #[Test]
    #[DataProvider('emptyAdapterProvider')]
    public function emptyAdapter(int $page, bool $expectedHasPrevious): void
    {
        $result = (new PageAssembler())->paginate(
            new InMemoryArrayAdapter([]),
            $page,
            2,
        );

        self::assertSame([], $result->getData());
        self::assertSame(0, $result->getCurrentCount());
        self::assertSame(0, $result->getTotalCount());
        self::assertSame(1, $result->getPageCount());
        self::assertFalse($result->hasNextPage());
        self::assertSame($expectedHasPrevious, $result->hasPreviousPage());
    }

    #[Test]
    public function pageCountRoundsUpNotToNearest(): void
    {
        $result = (new PageAssembler())->paginate(
            new InMemoryArrayAdapter(['a', 'b', 'c', 'd']),
            1,
            3,
        );

        self::assertSame(2, $result->getPageCount());
    }

    #[Test]
    #[DataProvider('invalidPageProvider')]
    public function throwsOnInvalidPage(int $page): void
    {
        $this->expectException(InvalidPageException::class);

        (new PageAssembler())->paginate(new InMemoryArrayAdapter(['a']), $page, 2);
    }

    #[Test]
    #[DataProvider('invalidSizeProvider')]
    public function throwsOnInvalidSize(int $size): void
    {
        $this->expectException(InvalidSizeException::class);

        (new PageAssembler())->paginate(new InMemoryArrayAdapter(['a']), 1, $size);
    }

    #[Test]
    public function sizeOfOneIsTheSmallestValidSize(): void
    {
        $result = (new PageAssembler())->paginate(new InMemoryArrayAdapter(['a', 'b']), 1, 1);

        self::assertSame(['a'], $result->getData());
        self::assertSame(1, $result->getPageSize());
    }

    #[Test]
    public function mapsDataAndPreservesPaginationMetadata(): void
    {
        $page = new Page(['a', 'b'], 2, 1, 2, true, false, 3, 5);

        $mapped = (new PageAssembler())->map($page, static fn (string $value): string => strtoupper($value));

        self::assertSame(['A', 'B'], $mapped->getData());
        self::assertSame(2, $mapped->getCurrentCount());
        self::assertSame(1, $mapped->getCurrentPage());
        self::assertSame(2, $mapped->getPageSize());
        self::assertTrue($mapped->hasNextPage());
        self::assertFalse($mapped->hasPreviousPage());
        self::assertSame(3, $mapped->getPageCount());
        self::assertSame(5, $mapped->getTotalCount());
    }

    #[Test]
    public function paginateOrThrowReturnsTheResultWhenInRange(): void
    {
        $result = (new PageAssembler())->paginateOrThrow(
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

        (new PageAssembler())->paginateOrThrow(new InMemoryArrayAdapter(['a', 'b', 'c']), $page, 2);
    }
}
