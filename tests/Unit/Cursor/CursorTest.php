<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Cursor;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Cursor\Cursor;

final class CursorTest extends TestCase
{
    #[Test]
    public function gettersReflectConstructorArguments(): void
    {
        $cursor = new Cursor(['a', 'b'], 2, 2, 'abc', true);

        self::assertSame(['a', 'b'], $cursor->getData());
        self::assertSame(2, $cursor->getCurrentCount());
        self::assertSame(2, $cursor->getPageSize());
        self::assertSame('abc', $cursor->getNextCursor());
        self::assertTrue($cursor->hasNextPage());
    }

    #[Test]
    public function emptyResult(): void
    {
        $cursor = Cursor::empty(10);

        self::assertSame([], $cursor->getData());
        self::assertSame(0, $cursor->getCurrentCount());
        self::assertSame(10, $cursor->getPageSize());
        self::assertNull($cursor->getNextCursor());
        self::assertFalse($cursor->hasNextPage());
    }

    #[Test]
    public function arrayDataIsWrappedInArrayIterator(): void
    {
        $cursor = new Cursor(['a', 'b'], 2, 2, null, false);

        self::assertInstanceOf(\ArrayIterator::class, $cursor->getIterator());
        self::assertSame(['a', 'b'], iterator_to_array($cursor->getIterator()));
    }

    #[Test]
    public function traversableDataIsReturnedAsIs(): void
    {
        $generator = (static function (): \Generator {
            yield 'a';
            yield 'b';
        })();

        $cursor = new Cursor($generator, 2, 2, null, false);

        self::assertSame($generator, $cursor->getIterator());
    }
}
