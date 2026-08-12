<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Exception\OutOfRangeException;
use PimBay\SearchQuery\Exception\SearchQueryException;

final class OutOfRangeExceptionTest extends TestCase
{
    #[Test]
    public function isASearchQueryException(): void
    {
        self::assertInstanceOf(SearchQueryException::class, new OutOfRangeException(1));
    }

    #[Test]
    public function messageIncludesTheGivenPage(): void
    {
        self::assertSame('Page 4 is out of range.', (new OutOfRangeException(4))->getMessage());
    }
}
