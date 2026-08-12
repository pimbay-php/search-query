<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Page;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Page\Page;

final class PageTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function currentOffsetProvider(): iterable
    {
        yield 'first page' => [1, 2, 0];
        yield 'third page of size ten' => [3, 10, 20];
    }

    #[Test]
    public function gettersReflectConstructorArguments(): void
    {
        $page = new Page(['a', 'b'], 2, 1, 2, true, false, 3, 5);

        self::assertSame(['a', 'b'], $page->getData());
        self::assertSame(2, $page->getCurrentCount());
        self::assertSame(1, $page->getCurrentPage());
        self::assertSame(2, $page->getPageSize());
        self::assertTrue($page->hasNextPage());
        self::assertFalse($page->hasPreviousPage());
        self::assertSame(3, $page->getPageCount());
        self::assertSame(5, $page->getTotalCount());
    }

    #[Test]
    #[DataProvider('currentOffsetProvider')]
    public function currentOffset(int $currentPage, int $pageSize, int $expectedOffset): void
    {
        $page = new Page([], 0, $currentPage, $pageSize, false, false, 1, 0);

        self::assertSame($expectedOffset, $page->getCurrentOffset());
    }

    #[Test]
    public function isOutOfRangeIsInheritedFromSlice(): void
    {
        $withinRange = new Page(['a', 'b'], 2, 2, 2, false, true, 2, 3);
        $beyondPageCount = new Page([], 0, 4, 2, false, true, 2, 3);

        self::assertFalse($withinRange->isOutOfRange());
        self::assertTrue($beyondPageCount->isOutOfRange());
    }

    #[Test]
    public function emptyResult(): void
    {
        $page = Page::empty();

        self::assertSame([], $page->getData());
        self::assertSame(0, $page->getCurrentCount());
        self::assertSame(1, $page->getCurrentPage());
        self::assertSame(0, $page->getPageSize());
        self::assertFalse($page->hasNextPage());
        self::assertFalse($page->hasPreviousPage());
        self::assertSame(1, $page->getPageCount());
        self::assertSame(0, $page->getTotalCount());
        self::assertFalse($page->isOutOfRange());
    }
}
