<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Exception\InvalidPageException;
use PimBay\SearchQuery\Exception\SearchQueryException;

final class InvalidPageExceptionTest extends TestCase
{
    #[Test]
    public function isASearchQueryException(): void
    {
        self::assertInstanceOf(SearchQueryException::class, new InvalidPageException(0));
    }

    #[Test]
    public function messageIncludesTheGivenPage(): void
    {
        self::assertSame('Page must be at least one, -1 given.', (new InvalidPageException(-1))->getMessage());
    }
}
