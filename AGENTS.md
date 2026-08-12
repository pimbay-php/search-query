# AGENTS.md — search-query

## Project Overview

`pimbay/search-query` is a framework-agnostic, strict search/pagination contracts library.
Three independent pagination families — `Page` (page-numbered, `totalCount`/`pageCount`), `Slice` (offset, no total count), `Cursor` (forward-only, keyset) — plus orthogonal capability adapters (`CountableAdapter`, `IdentifiableAdapter`, `HeadableAdapter`, `AllAdapter`), independent of all three.
No real datasource code — the shared foundation `pimbay/search-query-doctrine` (and planned `-eloquent`/`-elastic`/`-pimcore`) implement against.
No runtime magic: no duck-typed queries, no string-driven filter DSL, no generic `Criterion`/`SortRule` — filtering/sorting stays in each consumer's own `Query` class.
License: Unlicense. Minimum PHP: 8.3.

## Commands

```bash
composer install
composer php:cs         # php-cs-fixer, --dry-run --diff (check only, never mutates)
composer php:cs:fix     # same, applies the fix
composer php:stan       # phpstan analyse, level: max, bleedingEdge
composer test:83        # docker compose run php83 — phpunit, no coverage
composer test:84        # docker compose run php84 — phpunit, no coverage
composer test:85        # docker compose run php85 — phpunit, no coverage
composer test:all       # test:83 + test:84 + test:85
composer test:coverage  # docker compose run php83 — phpunit --coverage-text
composer test:mutation # infection — mutation testing, --min-msi=100 --min-covered-msi=100
composer docker:build   # docker compose build
composer ci             # php:cs + php:stan + test:all + test:mutation
```

`php:stan` is the authoritative type-safety gate — always run alongside `php:cs`/tests.

## Code Style

- **PHP 8.3+**, `declare(strict_types=1)` everywhere.
- **`@PER-CS2.0` + `@PER-CS2.0:risky` + `@PHP83Migration` + `@Symfony` + `@Symfony:risky`** via php-cs-fixer — run `composer php:cs:fix`, don't hand-format.
- **`final` by default**. `Slice` is non-`final` by design (`Page` extends it — `Page` IS-A `Slice`).
- **`readonly` properties** by default. **Public readonly, no getter** for plain value objects.
- **Named-constructor exceptions** — no inline `new SomeException(...)` beyond the trivial case.
- **PSR-4**, one class per file: `PimBay\SearchQuery\<Family>\...` → `src/<Family>/...`.
- **Comments** only where non-obvious, always English. PHPDoc only for generic/array-shape/template types PHPStan can't infer.
- **Markdown**: semantic linebreaks — break at sentence end, never inside a list item.
- **Docs discipline**: no "Project Layout" in READMEs — the tree speaks for itself.
- **Adapter method naming**: `*Adapter` methods named after query shape (`pageView`, `pageSlice`, `pageAfter`, `head`, `all`, `ids`, `count`) — no `get`-prefix.

## Architecture

```
src/
  Adapter/ — adapter interfaces and inmemory implementation
  Cursor/  — cursor family
  Exception/ — exception classes
  Page/    — page numbered family
  Slice/   — slice numbered family
```

## Public library mode

Every exported-symbol change is a public API decision.

- **Always ask before**: new `composer.json` dep, changing a public signature, new architectural pattern, touching >1 package at once.
- **Never without instruction**: delete a public class/file, rename an exported symbol, break wire compatibility, add a build-affecting dev dependency.
- Two valid approaches → present both, no silent pick.
- Multi-file change → list files, confirm scope, then proceed.

## Testing

- **PHPUnit 11**, `tests/Unit/` only — no real external system to integrate against (`tests/Functional/` would apply in `search-query-doctrine`, not here). Mirrors `src/` 1:1.
- Every collaborator faked, or none exists — `Adapter\InMemoryArrayAdapter` (shipped in `src/`) is the natural adapter for exercising `*Assembler` in unit tests.
- **Coverage: 100%** — hard gate; a dropped-coverage change comes with new tests, not an exclusion.
- **Mutation testing: Infection, min MSI 100%** (`composer test:mutation`) — an escaped mutant needs a stronger assertion, not a suppressed mutator.
- **`#[Test]` attribute**, not `test`-prefix. `#[DataProvider('methodName')]` for parameterized cases.

## Guardrails

- Targeted diffs — don't rewrite a file for a small fix.
- No unrequested docs/test scaffolding.
- Don't introduce a DI container, config loader, or logging framework — flag the need, don't silently add.
- No generic `Criterion`/`SortRule`/filter-DSL — deliberately rejected, filtering/sorting is each consumer's own `Query` class concern (see `docs/DECISIONS.md`).
- No `Query` marker interface — deliberately rejected; `getXAdapter(object $query)` stays untyped at the signature level so consumers never implement a library-owned interface.
- New failure case → check for an existing `Exception\*Exception` class before adding one.
