# pimbay/search-query

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pimbay/search-query?style=flat-square&color=blue)](https://packagist.org/packages/pimbay/search-query)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.3-8892bf?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/packagist/l/pimbay/search-query?style=flat-square&color=green)](LICENSE)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen?style=flat-square)](https://codeberg.org/pimbay-php/search-query)
[![Mutation Score](https://img.shields.io/badge/MSI-100%25-brightgreen?style=flat-square)](https://codeberg.org/pimbay-php/search-query)

Framework-agnostic, strict search/pagination contracts.
No runtime magic — every contract is an explicit type, no duck-typed queries, no string-driven filter configuration.
The library defines three independent pagination families — `Page`, `Slice`, `Cursor` — plus orthogonal capability adapters (`count`, `ids`, `head`, `all`), and nothing else: no query builder, no filter/sort DSL, no datasource code.

## Why three pagination families, not one adapter interface

A single "does everything" adapter interface forces every implementation to either support a capability it genuinely can't do correctly, or throw at runtime.
Each family here exists because it has a distinct, real capability profile:

| Family | Adapter | Result | Use it when |
|---|---|---|---|
| `Page` | `PageAdapter` | `PageResult` | admin-style listing with page numbers — you need `totalCount`/`pageCount` |
| `Slice` | `SliceAdapter` | `SliceResult` | infinite scroll — `hasNextPage` without a separate, expensive count query |
| `Cursor` | `CursorAdapter` | `CursorResult` | very large/unbounded datasets, Elasticsearch `search_after`, keyset pagination |

None of the three requires the other two.
A concrete adapter implements exactly the combination a given datasource can do cheaply and correctly — nothing is forced on it.
`Page` additionally extends `Slice`'s contract (`PageResult extends SliceResult`), since everything knowable from a `Slice` result is also true of a `Page` result.

Separately, `Adapter\CountableAdapter`, `Adapter\IdentifiableAdapter`, `Adapter\HeadableAdapter`, and `Adapter\AllAdapter` are orthogonal capability adapters, independent of all three families and of each other.
A repository calls them directly, not through a pagination orchestrator.

## Installation

```bash
composer require pimbay/search-query
```

## Usage

### `Page\PageAssembler`

Page-numbered pagination with a known total count.
Requires a `PageAdapter` — this example uses the bundled `Adapter\InMemoryArrayAdapter`, so it runs with no external datasource.

```php
<?php

declare(strict_types=1);

use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;
use PimBay\SearchQuery\Page\PageAssembler;

$adapter = new InMemoryArrayAdapter(['apple', 'banana', 'cherry', 'date', 'elderberry']);

$result = (new PageAssembler())->paginate($adapter, 2, 2);

$result->getData();          // ['cherry', 'date']
$result->getTotalCount();    // 5
$result->getPageCount();     // 3
$result->hasNextPage();      // true
$result->isOutOfRange();     // false — request page 10 instead to see this flip to true
```

`PageAssembler` never silently clamps to the last valid page — the caller decides what to do with an out-of-range request (e.g. a `404` for an SEO-sensitive HTML listing, or a plain empty result for a JSON API).
If a hard failure is exactly what you want, `paginateOrThrow()` does the `isOutOfRange()` check for you and raises `Exception\OutOfRangeException` instead:

```php
(new PageAssembler())->paginateOrThrow($adapter, 10, 2); // throws OutOfRangeException
```

### `Slice\SliceAssembler`

Offset-addressed, but without a total count — cheaper than `Page` when you only need "is there more?", not "how many pages total?".

```php
<?php

declare(strict_types=1);

use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;
use PimBay\SearchQuery\Slice\SliceAssembler;

$adapter = new InMemoryArrayAdapter(['apple', 'banana', 'cherry', 'date', 'elderberry']);

$result = (new SliceAssembler())->paginate($adapter, 1, 2);

$result->getData();       // ['apple', 'banana']
$result->hasNextPage();   // true
$result->isOutOfRange();  // false — request page 10 instead to see this flip to true
```

`isOutOfRange()` is `true` when `getCurrentPage() > 1` and the adapter returned no results — with no total count available, this is the only signal `Slice` has for "past the end", but for an offset-addressed source it's unambiguous.
`SliceAssembler` has the same `paginateOrThrow()` as `PageAssembler`, raising `Exception\OutOfRangeException` instead of returning a result with `isOutOfRange(): true`.

### `Cursor\CursorAssembler`

Forward-only, keyset-style pagination.
No page numbers — only an opaque cursor `string` and a `nextCursor` to continue from.

```php
<?php

declare(strict_types=1);

use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;
use PimBay\SearchQuery\Cursor\CursorAssembler;

$adapter = new InMemoryArrayAdapter(['apple', 'banana', 'cherry', 'date', 'elderberry']);

$firstPage = (new CursorAssembler())->paginate($adapter, cursor: null, size: 2);

$firstPage->getData();       // ['apple', 'banana']
$firstPage->hasNextPage();   // true

$secondPage = (new CursorAssembler())->paginate($adapter, $firstPage->getNextCursor(), 2);

$secondPage->getData();      // ['cherry', 'date']
```

### `SearchTerms\SearchTermsParser`

Splits raw search-term strings into `equals`/`notEquals`/`likes`/`notLikes` buckets, based on a leading negation marker and an embedded wildcard marker.
Pure string parsing — no SQL, no column mapping; a datasource-specific package (e.g. `search-query-doctrine`) turns the result into actual query conditions against a named column.

```php
<?php

declare(strict_types=1);

use PimBay\SearchQuery\SearchTerms\SearchTermsConfig;
use PimBay\SearchQuery\SearchTerms\SearchTermsParser;

$parsed = (new SearchTermsParser())->parseString('dog hors* -cow', new SearchTermsConfig());

$parsed->equals;    // ['dog']
$parsed->likes;     // ['hors*']
$parsed->notEquals; // ['cow']
```

`SearchTermsConfig` is tunable (`anywhere`, `minLength`, `likeChar`, `ignoreChar`) and validates its own markers (non-empty, mutually distinct) via `SearchQueryException`.
Negated terms are grouped as AND — `-dog -cat` excludes any record mentioning either, not only records mentioning both (see `docs/DECISIONS.md` for the reasoning).

### `Adapter\InMemoryArrayAdapter`

A reference `PageAdapter`/`SliceAdapter`/`CursorAdapter` implementation over a plain PHP array — used in the examples above, and useful in your own unit tests for exercising `PageAssembler`/`SliceAssembler`/`CursorAssembler` without a real datasource.
Not a template for production datasource adapters; those belong in a `search-query-<datasource>` package.

```php
<?php

declare(strict_types=1);

use PimBay\SearchQuery\Adapter\InMemoryArrayAdapter;

final class Person
{
    public function __construct(public readonly int $id, public readonly string $name) {}
}

$people = [new Person(1, 'Alice'), new Person(2, 'Bob')];

$adapter = new InMemoryArrayAdapter($people, extractId: fn (Person $p) => $p->id);

$adapter->count();  // 2
$adapter->ids();    // [1, 2]
```

Full usage reference (a repository shaped for a real datasource): **[docs/usage.md](docs/usage.md)**.

## Exceptions

Every failure mode this library can raise is a `final` subclass of the abstract `Exception\SearchQueryException`.
Catch the base type for coarse handling, or a specific subclass to distinguish why a call failed:

```php
use PimBay\SearchQuery\Exception\InvalidPageException;
use PimBay\SearchQuery\Exception\SearchQueryException;

try {
    (new PageAssembler())->paginate($adapter, 0, 10);
} catch (InvalidPageException $e) {
    // $e->getMessage() === 'Page must be at least one, 0 given.'
} catch (SearchQueryException $e) {
    // any other failure mode below
}
```

| Exception | Raised by |
|---|---|
| `SearchQueryException` | `abstract` and never thrown directly |
| `InvalidPageException` | `PageAssembler`/`SliceAssembler::paginate()` — `$page` less than 1 |
| `InvalidSizeException` | `PageAssembler`/`SliceAssembler`/`CursorAssembler::paginate()` — `$size` less than 1 |
| `EmptyCursorException` | `CursorAssembler::paginate()` — `$cursor` (or an adapter's returned `nextCursor`) is an empty string (use `null`, not `''`, for "no cursor") |
| `InvalidSearchTermsConfigException` | `SearchTermsConfig` constructor — an invalid combination of `likeChar`/`ignoreChar`/`minLength` |
| `OutOfRangeException` | `PageAssembler`/`SliceAssembler::paginateOrThrow()` — the requested page is past the last valid one |

## Testing

```bash
composer test:83        # docker compose run php83 — phpunit, no coverage
composer test:84        # docker compose run php84 — phpunit, no coverage
composer test:85        # docker compose run php85 — phpunit, no coverage
composer test:all       # test:83 + test:84 + test:85
composer test:coverage  # docker compose run php83 — phpunit --coverage-text
composer test:mutation # infection — mutation testing, --min-msi=100 --min-covered-msi=100
```

| PHP | Status |
|:----|:----|
| **8.3** | ✅ |
| **8.4** | ✅ |
| **8.5** | ✅ |

## Development Helpers

```bash
composer php:cs        # php-cs-fixer, --dry-run --diff (check only)
composer php:cs:fix    # same, applies the fix
composer php:stan      # phpstan analyse, level: max
```

## Packages in the stack

| Package | Description |
|---|---|
| `pimbay/search-query` | This package — framework-agnostic contracts, no datasource code. |
| `pimbay/search-query-doctrine` | *(planned)* `PageAdapter`/`SliceAdapter`/`CountableAdapter`/`IdentifiableAdapter`/`HeadableAdapter`/`AllAdapter` over Doctrine DBAL/ORM. |
| `pimbay/search-query-pimcore` | *(planned)* Terminal implementation over Pimcore `DataObject\Listing`. |

## Architecture & Decisions

- **[docs/context.md](docs/context.md)** — current working state: what's in progress, what's next.
- **[docs/DECISIONS.md](docs/DECISIONS.md)** — why things are built the way they are, in the order the decisions were made.
- **[docs/CHANGELOG.md](docs/CHANGELOG.md)** — version history.

## License

Public domain — [Unlicense](LICENSE)

Created by [Jan Sarmir](https://pimbay.dev) · No conditions · No copyright

Bundled third-party dependencies and their licenses: **[docs/THIRD-PARTY-NOTICES.md](docs/THIRD-PARTY-NOTICES.md)**.