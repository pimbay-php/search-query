# Context

> Working memory, not a historical record.
> Continuously edited, not append-only — unlike DECISIONS.md.
> When something here resolves: delete it if it was only ever local/temporary, or promote it to DECISIONS.md if it turned out to matter beyond this moment.
> Don't let resolved items pile up here.

## Current focus

Core `src/` is complete and internally consistent for the three pagination families (`Page`, `Slice`, `Cursor`) plus capability adapters.

## Open questions

## Known limitations / non-goals (for now)

- No generic filter/sort DSL (`Criterion`/`SortRule`) — deliberate, see `docs/DECISIONS.md`.
- No bidirectional cursor (`previousCursor`) — `Cursor` is forward-only; addable additively later if needed.
- No datasource adapters ship in this package — Doctrine/Eloquent/Elasticsearch/Pimcore implementations are separate, not-yet-built packages that depend on this one.

## Implementation notes

- `Slice::getIterator()` and `Cursor::getIterator()`'s `is_array($this->data)` branch is always true when constructed via their respective `*Assembler` directly (both must materialize results into an array anyway, to compute `currentCount` without exhausting a non-rewindable `Generator`). It stays generic because `Page` extends `Slice` and can legitimately hold a lazy `Traversable` (`PageAssembler` derives `currentCount` arithmetically from the adapter-supplied `totalCount`, so it never needs to consume `$data` to count it). Do not "simplify" this branch away — see each method's own docblock.

## Ideas / future plans

- `search-query-doctrine` (DBAL + ORM `DbalSimpleAdapter`, `Slice`/`Cursor` variants).
- `search-query-pimcore` (terminal implementation over Pimcore `DataObject\Listing`).
- `search-query-elastic` — will likely need its own `CursorAdapter`-only contract given Elasticsearch's `index.max_result_window` limit; no `Page` support past that limit.
- `Cursor\CursorAssembler`'s `?string $cursor`/`nextCursor` currently carries a single opaque string, sufficient for a single-column sort key. If a future adapter needs a composite/multi-column keyset (or richer state than a plain filter position — e.g. a consistency snapshot marker), reconsider whether the cursor needs structured encoding (e.g. a dedicated value object again), informed by Rekapager's `KeysetPageIdentifier` + separate `PageIdentifierEncoderInterface` split (not adopted now — no concrete use case yet, and it would add an abstraction layer this library otherwise avoids).