# Contributing

Contributions are welcome — new datasource adapters, capability adapters, bug reports, documentation.

## Public Domain Dedication

By submitting a pull request, you dedicate your contribution to the public domain under the same [Unlicense](LICENSE) terms as this project.
You assert that you have the right to make this dedication.

## Guidelines

- PHP 8.3+, `declare(strict_types=1)` on every file
- PHPStan level max, no errors, no baseline ignores
- 100% code coverage required
- 100% mutation score required (`composer test:mutation`, Infection — min MSI 100%, min covered MSI 100%); an escaped mutant means the test needs a stronger assertion, not a suppressed mutator
- Adding a new pagination family requires the full `<Family>Chunk`/`<Family>Adapter`/`<Family>Result`/`<Family>`/`<Family>Assembler` set, mirroring `Page`/`Slice`/`Cursor` — not a partial shape
- Adding a new capability adapter (alongside `CountableAdapter`/`IdentifiableAdapter`/`HeadableAdapter`/`AllAdapter`) requires it to stay orthogonal: it must not require or imply any of the existing capabilities
