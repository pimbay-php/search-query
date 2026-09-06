# Changelog

All notable changes to this project are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versioning follows [SemVer](https://semver.org/).

## [Unreleased]

## [1.0.1] - 2026-09-06

### Removed
- The `composer docker:build` script.

### Fixed
- `SearchTermsParser` no longer produces an empty-string term when a search term becomes empty after stripping the negation marker (e.g. a term consisting solely of the marker) with `minLength: 0`.

### Changed
- `Cursor`, `Page`, and `Slice` are now declared as `readonly` classes rather than only having `readonly` properties.

## [1.0.0] - 2026-08-15

### Added
- `Page`, `Slice`, and `Cursor` pagination families, each with its own `*Adapter`/`*Chunk`/`*Result`/`*Assembler` set.
- `Adapter\CountableAdapter`, `Adapter\IdentifiableAdapter`, `Adapter\HeadableAdapter`, `Adapter\AllAdapter` — capability adapters, independent of all three pagination families.
