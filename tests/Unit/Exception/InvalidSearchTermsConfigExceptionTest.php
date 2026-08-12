<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Exception\InvalidSearchTermsConfigException;
use PimBay\SearchQuery\Exception\SearchQueryException;

final class InvalidSearchTermsConfigExceptionTest extends TestCase
{
    #[Test]
    public function isASearchQueryException(): void
    {
        self::assertInstanceOf(SearchQueryException::class, new InvalidSearchTermsConfigException('reason'));
    }

    #[Test]
    public function messageIncludesTheGivenReason(): void
    {
        $exception = new InvalidSearchTermsConfigException('likeChar must not be empty');

        self::assertSame('Invalid SearchTermsConfig: likeChar must not be empty.', $exception->getMessage());
    }
}
