<?php

declare(strict_types=1);

namespace PimBay\SearchQuery\Tests\Unit\SearchTerms;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PimBay\SearchQuery\Exception\InvalidSearchTermsConfigException;
use PimBay\SearchQuery\SearchTerms\SearchTermsConfig;
use PimBay\SearchQuery\SearchTerms\SearchTermsParser;

final class SearchTermsParserTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string[], string[], string[], string[]}>
     */
    public static function singleTermBucketProvider(): iterable
    {
        yield 'equals term' => ['pes', ['pes'], [], [], []];
        yield 'like term' => ['pes*', [], [], ['pes*'], []];
        yield 'negated equals term' => ['-pes', [], ['pes'], [], []];
        yield 'negated like term' => ['-pes*', [], [], [], ['pes*']];
    }

    /**
     * @return iterable<string, array{string[], int, string[], string[]}>
     */
    public static function minLengthProvider(): iterable
    {
        yield 'terms shorter than minLength are skipped' => [['ab', 'abc'], 3, ['abc'], []];
        yield 'negation marker counts towards minLength' => [['-ab'], 3, [], ['ab']];
    }

    /**
     * @return iterable<string, array{string[]}>
     */
    public static function degenerateTermProvider(): iterable
    {
        yield 'bare ignoreChar' => [['-']];
        yield 'empty string' => [['']];
    }

    /**
     * @return iterable<string, array{string[], string[]}>
     */
    public static function degenerateTermFollowedByValidTermProvider(): iterable
    {
        yield 'bare ignoreChar then a valid term' => [['-', 'dog'], ['dog']];
        yield 'empty string then a valid term' => [['', 'cat'], ['cat']];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function singleTermProvider(): iterable
    {
        yield 'equals' => ['pes'];
        yield 'notEquals' => ['-pes'];
        yield 'likes' => ['pes*'];
        yield 'notLikes' => ['-pes*'];
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function invalidConfigProvider(): iterable
    {
        yield 'empty likeChar' => ['', '-', 3];
        yield 'empty ignoreChar' => ['*', '', 3];
        yield 'same likeChar and ignoreChar' => ['*', '*', 3];
        yield 'negative minLength' => ['*', '-', -1];
    }

    /**
     * @return iterable<string, array{string, string[], string[], string[]}>
     */
    public static function parseStringProvider(): iterable
    {
        yield 'simple whitespace' => ['dog hors* -cow', ['dog'], ['cow'], ['hors*']];
        yield 'repeated and mixed whitespace' => [" dog \t  hors* \n -cow ", ['dog'], ['cow'], ['hors*']];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyParseStringProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only whitespace' => ["   \t\n  "];
    }

    #[Test]
    public function defaults(): void
    {
        $config = new SearchTermsConfig();

        self::assertTrue($config->anywhere);
        self::assertSame(3, $config->minLength);
        self::assertSame('*', $config->likeChar);
        self::assertSame('-', $config->ignoreChar);
    }

    #[Test]
    public function zeroIsTheSmallestValidMinLength(): void
    {
        self::assertSame(0, (new SearchTermsConfig(minLength: 0))->minLength);
    }

    /**
     * @param string[] $expectedEquals
     * @param string[] $expectedNotEquals
     * @param string[] $expectedLikes
     * @param string[] $expectedNotLikes
     */
    #[Test]
    #[DataProvider('singleTermBucketProvider')]
    public function singleTermIsBucketedCorrectly(
        string $term,
        array $expectedEquals,
        array $expectedNotEquals,
        array $expectedLikes,
        array $expectedNotLikes,
    ): void {
        $result = (new SearchTermsParser())->parse([$term], new SearchTermsConfig());

        self::assertSame($expectedEquals, $result->equals);
        self::assertSame($expectedNotEquals, $result->notEquals);
        self::assertSame($expectedLikes, $result->likes);
        self::assertSame($expectedNotLikes, $result->notLikes);
    }

    /**
     * @param string[] $terms
     * @param string[] $expectedEquals
     * @param string[] $expectedNotEquals
     */
    #[Test]
    #[DataProvider('minLengthProvider')]
    public function minLengthFiltering(array $terms, int $minLength, array $expectedEquals, array $expectedNotEquals): void
    {
        $result = (new SearchTermsParser())->parse($terms, new SearchTermsConfig(minLength: $minLength));

        self::assertSame($expectedEquals, $result->equals);
        self::assertSame($expectedNotEquals, $result->notEquals);
    }

    /**
     * @param string[] $terms
     */
    #[Test]
    #[DataProvider('degenerateTermProvider')]
    public function termsThatAreEmptyAfterMarkerStrippingAreIgnored(array $terms): void
    {
        $result = (new SearchTermsParser())->parse($terms, new SearchTermsConfig(minLength: 0));

        self::assertTrue($result->isEmpty());
    }

    /**
     * @param string[] $terms
     * @param string[] $expectedEquals
     */
    #[Test]
    #[DataProvider('degenerateTermFollowedByValidTermProvider')]
    public function degenerateTermDoesNotStopProcessingOfLaterTerms(array $terms, array $expectedEquals): void
    {
        $result = (new SearchTermsParser())->parse($terms, new SearchTermsConfig(minLength: 0));

        self::assertSame($expectedEquals, $result->equals);
    }

    #[Test]
    public function multiCharacterMarkersAreSupported(): void
    {
        $config = new SearchTermsConfig(likeChar: '%%', ignoreChar: '!!');

        $result = (new SearchTermsParser())->parse(['!!pes%%'], $config);

        self::assertSame(['pes%%'], $result->notLikes);
    }

    #[Test]
    public function emptyInputProducesEmptyResult(): void
    {
        $result = (new SearchTermsParser())->parse([], new SearchTermsConfig());

        self::assertTrue($result->isEmpty());
    }

    #[Test]
    #[DataProvider('singleTermProvider')]
    public function isEmptyIsFalseWhenExactlyOneBucketIsPopulated(string $term): void
    {
        $result = (new SearchTermsParser())->parse([$term], new SearchTermsConfig());

        self::assertFalse($result->isEmpty());
    }

    #[Test]
    public function mixedTermsAreBucketedIndependently(): void
    {
        $result = (new SearchTermsParser())->parse(['dog', 'hors*', '-cow', '-shee*'], new SearchTermsConfig());

        self::assertSame(['dog'], $result->equals);
        self::assertSame(['hors*'], $result->likes);
        self::assertSame(['cow'], $result->notEquals);
        self::assertSame(['shee*'], $result->notLikes);
        self::assertFalse($result->isEmpty());
    }

    #[Test]
    #[DataProvider('invalidConfigProvider')]
    public function configRejectsInvalidValues(string $likeChar, string $ignoreChar, int $minLength): void
    {
        $this->expectException(InvalidSearchTermsConfigException::class);

        new SearchTermsConfig(minLength: $minLength, likeChar: $likeChar, ignoreChar: $ignoreChar);
    }

    /**
     * @param string[] $expectedEquals
     * @param string[] $expectedNotEquals
     * @param string[] $expectedLikes
     */
    #[Test]
    #[DataProvider('parseStringProvider')]
    public function parseStringSplitsOnWhitespace(
        string $text,
        array $expectedEquals,
        array $expectedNotEquals,
        array $expectedLikes,
    ): void {
        $result = (new SearchTermsParser())->parseString($text, new SearchTermsConfig());

        self::assertSame($expectedEquals, $result->equals);
        self::assertSame($expectedNotEquals, $result->notEquals);
        self::assertSame($expectedLikes, $result->likes);
    }

    #[Test]
    #[DataProvider('emptyParseStringProvider')]
    public function parseStringWithBlankInputProducesEmptyResult(string $text): void
    {
        $result = (new SearchTermsParser())->parseString($text, new SearchTermsConfig());

        self::assertTrue($result->isEmpty());
    }

    #[Test]
    #[DataProvider('emptyParseStringProvider')]
    public function parseStringWithBlankInputAndZeroMinLengthProducesEmptyResult(string $text): void
    {
        $result = (new SearchTermsParser())->parseString($text, new SearchTermsConfig(minLength: 0));

        self::assertTrue($result->isEmpty());
    }

    #[Test]
    public function parseStringKeepsZeroAsAValidTerm(): void
    {
        $result = (new SearchTermsParser())->parseString('0 dog', new SearchTermsConfig(minLength: 1));

        self::assertSame(['0', 'dog'], $result->equals);
    }

    #[Test]
    public function parseStringBehavesLikeParseOnPreSplitTerms(): void
    {
        $config = new SearchTermsConfig();

        $viaString = (new SearchTermsParser())->parseString('dog hors* -cow -shee*', $config);
        $viaArray = (new SearchTermsParser())->parse(['dog', 'hors*', '-cow', '-shee*'], $config);

        self::assertEquals($viaArray, $viaString);
    }
}
