# Security Policy

## Reporting a vulnerability

Report suspected security vulnerabilities privately — do not open a public issue for them.

Use [GitHub Security Advisories](https://github.com/pimbay-php/search-query/security/advisories/new) for this repository, or email **security@pimbay.dev**.

Include what you'd include in any bug report: affected version/commit, reproduction steps, and impact as you understand it.
A proof-of-concept is helpful but not required to file a report.

## What to expect

- Acknowledgement within 5 business days.
- An initial assessment (confirmed / not applicable / needs more information) within 10 business days of acknowledgement.
- Credit in the fix's changelog entry, unless you ask to stay anonymous.

There is no bug bounty program.

## Scope

In scope: this repository's own code in `src/` and its Actions workflows.

Out of scope: vulnerabilities in third-party dependencies with no search-query-specific exploitation path — report those upstream instead (see `docs/THIRD-PARTY-NOTICES.md` for what's bundled).
This library has no runtime dependencies, no network I/O, no filesystem access, and no code execution of user-supplied strings — its attack surface is limited to how a consuming application wires its own datasource adapters, which is that application's responsibility, not this library's.
If you're unsure whether something is in scope, report it anyway and let us triage it.

## Supported versions

Only the latest published version receives security fixes.
This project does not currently maintain long-term-support branches.
