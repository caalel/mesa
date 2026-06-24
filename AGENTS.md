# AGENTS

## General Principles

- Follow TDD strictly.
- Never write production code without a failing test.
- Keep solutions simple.
- Avoid premature abstractions.
- Follow Laravel conventions.
- Prefer readability over cleverness.

## Architecture

- Controllers must remain thin.
- Business logic belongs to Services.
- Services must use dependency injection.
- Avoid static methods in Services.
- Validation belongs to Requests.
- Models represent domain entities.

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
- On Windows sandboxed shells, avoid parallel initial reads for required context files.
- Prefer simple PowerShell reads with `login: false` for initial project instructions.
