<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Exception\InvalidSizeException;
use PimBay\SearchQuery\Exception\SearchQueryException;

final class InvalidSizeExceptionTest extends TestCase
{
    #[Test]
    public function isASearchQueryException(): void
    {
        self::assertInstanceOf(SearchQueryException::class, new InvalidSizeException(0));
    }

    #[Test]
    public function messageIncludesTheGivenSize(): void
    {
        self::assertSame('Size must be a positive integer, -1 given.', (new InvalidSizeException(-1))->getMessage());
    }
}
