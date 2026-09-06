# Decisions

> Append-only log of decisions specific to _this_ project.
> Never edit or delete a past entry — if a decision changes, add a new entry that supersedes it and says so.
>
> **What belongs here** (test): would changing this silently break correctness, compatibility, or behavior if someone didn't know why it was done this way?
> If yes → here.
> If it's a cheap/local implementation detail → docs/context.md instead.
> If it's a pattern repeated across multiple repos → AGENTS.md instead, not here.

## Three independent pagination families instead of one adapter interface

**Date:** 2026-08-09

**Decision:** `Page`, `Slice`, and `Cursor` are three separate, independent adapter/result families.
There is no shared "does everything" adapter interface.
A consuming repository picks exactly the family it needs per method and the type system enforces it.

**Why:** A single fat interface forces every adapter implementation to either support a capability it can't do correctly (e.g. `totalCount` on an Elasticsearch `search_after` query past `index.max_result_window`), or throw at runtime. Each family exists because it has genuinely distinct capabilities: `Page` knows `totalCount`/`pageCount` (can jump to an arbitrary page); `Slice` only knows `hasMore` (cheap, no count query); `Cursor` is forward-only with an opaque token (works for keyset pagination and datasources without offset support).

**Alternatives considered:** One `AdapterInterface` with all methods (rejected — forces unsupported capabilities on every implementer). `OffsetPagination` as a name for the `Page`/`Slice` pair (rejected — `Slice` is also offset-addressed, so the name doesn't disambiguate).

## No generic `Criterion`/`SortRule` filter DSL

**Date:** 2026-08-09

**Decision:** This library has no generic, string-keyed filter/sort mechanism. Filtering and sorting are entirely each consumer's own `Query` class's concern.

**Why:** A generic `Criterion(field: string, operator, value)` mechanism invites string-matching/reflection to map a "field" onto a column, association, or computed expression — exactly the kind of runtime magic this library exists to avoid. A consumer's `Query` class (e.g. `RequestsQuery` with typed `?array $siteZones`, `array $sort`) is already fully typed and needs no library-owned abstraction on top.

**Alternatives considered:** A typed `Criterion`/`SortRule` pair with a library-agnostic key (considered and rejected before implementation).

## No `Query` marker interface

**Date:** 2026-08-09

**Decision:** `*Adapter` factory methods on a repository (`getPageAdapter(object $query): PageAdapter`, etc.) take a native `object $query`, narrowed only via `@param ConcreteQueryClass $query` PHPDoc for static analysis — never a library-owned `Query` interface.

**Why:** A marker interface would force every CQRS Query class across every consuming project to implement a library-specific contract for zero runtime benefit (the interface would be empty). PHP's contravariant-parameter rule for abstract-method overrides also means the native signature can't be narrowed per-repository anyway; PHPStan-level narrowing via PHPDoc achieves the same static safety without the coupling.

## `Page` extends `Slice`; `Slice` is not `final`

**Date:** 2026-08-09

**Decision:** `Page\Page extends Slice\Slice` and `Page\PageResult extends Slice\SliceResult`. `Slice` is the one non-`final` concrete class in this codebase.

**Why:** `Page` IS-A `Slice` — everything knowable from a `Slice` result (`currentCount`, `currentPage`, `pageSize`, `hasNextPage`, `hasPreviousPage`, `currentOffset`) is also true of a `Page` result, which adds `totalCount`/`pageCount` on top. Sharing the contract via inheritance instead of duplicating it keeps the two families from silently drifting apart. This mirrors Spring Data's own `Page extends Slice` relationship, which the two-family/three-family split in this library already resembles independently.

## `PageAssembler` never clamps the requested page number

**Date:** 2026-08-09

**Decision:** `PageAssembler::paginate()` reports `getCurrentPage()` as the literally requested page number, even when it exceeds `getPageCount()`. It does not silently clamp to the last valid page. `SliceResult::isOutOfRange(): bool` lets a caller detect this explicitly.

**Why:** The library previously clamped (`min($page, $pagesCount)`), which meant a request for an out-of-range page silently returned the same content as the last valid page. For an HTML listing crawled by search engines, this produces duplicate-content pages at every URL beyond the real range — exactly the kind of issue tools like Google Search Console flag. The library itself does not throw or decide an HTTP status for this; that is the consuming controller's policy decision (404 for an SEO-sensitive HTML listing, a plain empty `200` for a JSON API that doesn't care).

**Alternatives considered:** Throwing an exception directly from `PageAssembler` for an out-of-range page (rejected — would force every consumer, including ones that are fine with an empty result, into a `try`/`catch`).

## Negated search terms combine with AND (exclude on any match), not OR

**Date:** 2026-09-06

**Decision:** A datasource-specific package must combine negated terms (`ParsedSearchTerms::notEquals`/`notLikes`) with `AND` — a record is excluded if it matches *any one* of them, not only if it matches *all*. `-dog -cat` excludes anything mentioning either. `SearchTermsParser` only buckets terms; it never builds the query, so this is guidance for consumer packages, not enforced here.

**Why:** Matches how a search box reads in natural language — several exclusions mean "hide all of these", not "only hide the overlap". Requiring every negated term to co-occur before excluding a record would almost never fire in free-text search.

**Alternatives considered:** Combining negated terms with `OR` inside the query (rejected — counter-intuitive, rarely useful).
