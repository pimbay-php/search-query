<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Slice;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Slice\Slice;

final class SliceTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, bool}>
     */
    public static function isOutOfRangeProvider(): iterable
    {
        yield 'first page, no results' => [1, 0, false];
        yield 'first page, has results' => [1, 2, false];
        yield 'later page, has results' => [2, 2, false];
        yield 'later page, no results' => [2, 0, true];
    }

    #[Test]
    public function gettersReflectConstructorArguments(): void
    {
        $slice = new Slice(['a', 'b'], 2, 2, 2, true, true);

        self::assertSame(['a', 'b'], $slice->getData());
        self::assertSame(2, $slice->getCurrentCount());
        self::assertSame(2, $slice->getCurrentPage());
        self::assertSame(2, $slice->getPageSize());
        self::assertTrue($slice->hasNextPage());
        self::assertTrue($slice->hasPreviousPage());
        self::assertSame(2, $slice->getCurrentOffset());
    }

    #[Test]
    public function emptyResult(): void
    {
        $slice = Slice::empty();

        self::assertSame([], $slice->getData());
        self::assertSame(0, $slice->getCurrentCount());
        self::assertSame(1, $slice->getCurrentPage());
        self::assertSame(0, $slice->getPageSize());
        self::assertFalse($slice->hasNextPage());
        self::assertFalse($slice->hasPreviousPage());
        self::assertFalse($slice->isOutOfRange());
    }

    #[Test]
    #[DataProvider('isOutOfRangeProvider')]
    public function isOutOfRange(int $currentPage, int $currentCount, bool $expected): void
    {
        $slice = new Slice([], $currentCount, $currentPage, 2, false, $currentPage > 1);

        self::assertSame($expected, $slice->isOutOfRange());
    }

    #[Test]
    public function arrayDataIsWrappedInArrayIterator(): void
    {
        $slice = new Slice(['a', 'b'], 2, 1, 2, false, false);

        self::assertInstanceOf(\ArrayIterator::class, $slice->getIterator());
        self::assertSame(['a', 'b'], iterator_to_array($slice->getIterator()));
    }

    #[Test]
    public function traversableDataIsReturnedAsIs(): void
    {
        $generator = (static function (): \Generator {
            yield 'a';
            yield 'b';
        })();

        $slice = new Slice($generator, 2, 1, 2, false, false);

        self::assertSame($generator, $slice->getIterator());
    }
}
