<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Exception\EmptyCursorException;
use PimBay\SearchQuery\Exception\SearchQueryException;

final class EmptyCursorExceptionTest extends TestCase
{
    #[Test]
    public function isASearchQueryException(): void
    {
        self::assertInstanceOf(SearchQueryException::class, new EmptyCursorException());
    }

    #[Test]
    public function messageIsFixed(): void
    {
        self::assertSame('Cursor must not be empty.', (new EmptyCursorException())->getMessage());
    }
}
