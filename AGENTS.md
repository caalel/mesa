# AGENTS

## General Principles

- Follow TDD strictly.
- Never write production code without a failing test.
- Keep solutions simple.
- Avoid premature abstractions.
- Follow Laravel conventions.
- Prefer readability over cleverness.
- Add concise comments or docblocks only for non-obvious domain rules, data transformations,
  external-source quirks, or decision rationale.
- Do not add comments that merely restate the code; prefer clear names and small methods.
- Document why a normalization or workaround changes source data and the original condition it represents.

## Architecture

- Controllers must remain thin.
- Business logic belongs to Services.
- Services must use dependency injection.
- Avoid static methods in Services.
- Validation belongs to Requests.
- Models represent domain entities.

## UI and Localization

- User-facing interface text must use Laravel localization keys instead of hardcoded strings.
- Interface translation and dynamic food name translation are separate concerns.
- Food names use explicit database columns.
- `name_pt` is required.
- `name_en` is nullable.
- Do not introduce a translatable package or JSON translation fields without an explicit architectural decision.

## Tests

- Use Pest.
- Use descriptive test names.
- One behavior per test.
- Unit tests for Services.
- Feature tests for HTTP flows.
- Tests must explicitly import the classes they use.
- Never introduce aliases or autoload hacks to satisfy tests.
- Prefer refactoring test imports instead of adding infrastructure complexity.
- Tests may be refactored as long as the tested behavior remains the same.

## Refactoring

- Preserve behavior.
- Remove duplication.
- Improve readability.
- Avoid unnecessary patterns.

## Communication

- Explain important decisions.
- Prefer small incremental changes.
- Never implement unrelated features.

## Agent Workflow

- At the start of a new work session, read `AGENTS.md` alone before any other file.
- For UI tasks, read `docs/design.md` after `docs/architecture.md`.
- On Windows sandboxed shells, avoid parallel initial reads for required context files.
- Prefer simple PowerShell reads with `login: false` for initial project instructions.
- Permission warnings for `vendor/pestphp/pest/.temp/test-results` may occur in the Codex sandbox.
- Ignore those warnings if the tests execute normally.
- Do not change project permissions to fix Codex sandbox warnings.

## Encoding and file editing

- Treat project files as UTF-8 without BOM.
- Do not rewrite an entire file just because a patch failed due to accented characters or symbols.
- Before replacing text with special characters, confirm the real file contents with an appropriate UTF-8 read.
- Prefer small edits by line or by blocks delimited with stable ASCII context.
- Only rewrite an entire file when there is a real technical reason and after preserving its existing contents exactly.
- After any edit caused by an encoding issue, review the diff to ensure there were no accidental changes outside the requested section.
