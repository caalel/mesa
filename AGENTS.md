# AGENTS

## Scope and Context

- Read only the files needed for the requested change.
- Do not expand the scope or implement unrelated work.
- Use `docs/architecture.md` for current technical architecture and operations,
  `docs/design.md` for interface behavior and visual direction, and
  `docs/data-sources.md` for dataset provenance and preparation.

## TDD Workflow

- Follow TDD strictly for behavior changes.
- Write or update a failing test before production code. Explain why it is RED.
- Keep RED and GREEN as separate steps; do not add production behavior during RED.
- Implement the smallest change that makes the relevant test GREEN.
- Refactor only after GREEN. Visual-only refactors that do not change behavior may
  use a GREEN-GREEN cycle.
- Preserve existing behavior unless the request explicitly changes it.

## Code and Responsibilities

- Keep solutions simple and readable; avoid premature abstractions.
- Follow Laravel conventions.
- Keep controllers thin.
- Put domain and business logic in Services, using dependency injection.
- Keep validation in Requests when handling HTTP input.
- Models represent domain entities.
- Avoid static methods in Services.
- Add concise comments or docblocks only for non-obvious domain rules, data
  transformations, external-source quirks, or decision rationale.
- Do not add comments that merely restate code; prefer clear names and small methods.

## Tests

- Use Pest/PHPUnit.
- Write small, descriptive tests with one behavior per test.
- Use unit tests for focused service and pipeline behavior; use feature tests for
  HTTP, Livewire, commands, seeders, and integration flows.
- Prefer explicit expectations over indirect assertions.
- Avoid long chains of `and()`.
- Use localization keys or translated strings in UI assertions instead of duplicating
  interface copy.
- Tests must explicitly import the classes they use.
- Never add aliases, autoload hacks, or unnecessary infrastructure to satisfy tests.
- Tests may be refactored when their covered behavior remains unchanged.

## UI and Localization

- User-facing interface strings must use Laravel localization keys, not hardcoded text.
- Interface translations and food-name translations are separate concerns.
- Food names use explicit database columns: `name_pt` is required and `name_en` is
  nullable.
- Do not introduce a translatable package or JSON translation fields without an
  explicit architectural decision.

## Refactoring and File Editing

- Preserve behavior, remove duplication, and avoid unnecessary patterns.
- Treat project files as UTF-8 without BOM.
- Do not rewrite a whole file merely because a patch failed around accented characters
  or symbols.
- Before replacing special characters, confirm the file with a UTF-8 read.
- Prefer small edits using stable context. Rewrite a full file only when there is a
  technical reason and its existing content has been preserved exactly.
- Review the diff after an encoding-related edit to detect unintended changes.

## Codex Workflow and Communication

- At the start of a new work session, read `AGENTS.md` alone before other files.
- For UI tasks, read `docs/architecture.md` and then `docs/design.md` when needed.
- Use simple PowerShell reads with `login: false` for initial project instructions.
- Review diffs before handoff.
- State important decisions, the reason for RED, tests executed, files changed, and
  validation results.
- Do not hide failures, blocked validation, limitations, or existing unrelated changes.
- Permission warnings for `vendor/pestphp/pest/.temp/test-results` may occur in the
  Codex sandbox. Ignore them when tests otherwise run normally; do not change project
  permissions to address them.

## Standard Validation

```bash
php artisan test
npm.cmd run build
git diff --check
```

- Run `php artisan test` for behavior or code changes, unless the request explicitly
  limits validation or prohibits test execution.
- Run `npm.cmd run build` for frontend asset or styling changes, unless the request
  explicitly prohibits it.
- Run `git diff --check` before the final handoff.
