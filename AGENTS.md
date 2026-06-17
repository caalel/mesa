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
- Validation belongs to Requests.
- Models represent domain entities.

## Tests

- Use Pest.
- Use descriptive test names.
- One behavior per test.
- Unit tests for Services.
- Feature tests for HTTP flows.

## Refactoring

- Preserve behavior.
- Remove duplication.
- Improve readability.
- Avoid unnecessary patterns.

## Communication

- Explain important decisions.
- Prefer small incremental changes.
- Never implement unrelated features.