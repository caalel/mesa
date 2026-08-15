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
- In localization tests, assert literal interface strings for each tested locale.
- In behavioral UI tests, prefer `data-testid`, component state, and the presence
  or absence of structural elements; avoid text assertions when the copy is not
  relevant to the behavior.
- Do not use localization helpers such as `__()` as a substitute for structural
  assertions.
- Keep tests for shared UI elements, such as the global header, in dedicated
  layout test files instead of coupling them to a specific page.
- Tests must explicitly import the classes they use.
- Never add aliases, autoload hacks, or unnecessary infrastructure to satisfy tests.
- Tests may be refactored when their covered behavior remains unchanged.

## UI and Localization

- User-facing interface strings must use Laravel localization keys, not hardcoded text.
- Interface translations and food-name translations are separate concerns.
- Food names use independent explicit database columns: `name_pt` and `name_en` are
  both required.
- Do not introduce a translatable package or JSON translation fields without an
  explicit architectural decision.

## Figma Consultation Workflow

For UI changes based on Figma Make, use manually supplied Make files as the normal
workflow. The user will normally provide the relevant source file (for example,
`App.tsx`) and the main generated CSS file. Analyze them as design references for
layout hierarchy, component composition, spacing, dimensions, typography, colors,
borders, visual states, responsiveness, content, element relationships, and
interactions represented in the code.

The generated React, Vite, Tailwind, and CSS code must be studied to understand the
design, but must not be copied or transplanted as MESA production code. Adapt its
decisions to Laravel, Livewire, Blade, the project's Tailwind and design tokens,
existing components and conventions, localization, and documented MESA architecture
and behavior. When they conflict, MESA decisions take priority.

Do not require the entire Make project upfront. If a supplied file imports, refers
to, or depends on another component, stylesheet, asset, or resource that is needed
to understand the requested layout safely, stop before inferring the missing part
and ask for the specific required files or imports. Do not invent structure, styles,
values, or behavior, and do not substitute a generic approximation. If the supplied
files are sufficient for the relevant layout, proceed without requesting more.

```text
manually supplied Figma Make source + main CSS
        ↓
code and CSS analysis as a design reference
        ↓
adaptation to MESA architecture
```

Use the Figma MCP only when the user explicitly requests it, such as “consult the
Figma through MCP”, “use the Figma MCP”, or “inspect Figma Make through MCP”. Do
not invoke it automatically because a task mentions Figma or depends on a design.
When explicitly requested, use the local Figma MCP integration, obtain context with
`mcp__figma__get_design_context`, follow returned resource links, and read needed
resources with `read_mcp_resource(server: "figma")`. Analyze the returned code as a
design reference and adapt it to MESA; do not copy it directly. If MCP context is
insufficient, stop and report exactly what is missing rather than guessing.

```text
explicit MCP request
        ↓
Figma MCP
        ↓
get_design_context / resource links / read_mcp_resource
        ↓
analysis as a design reference
        ↓
adaptation to MESA architecture
```

Do not use browser or web access as an improvised substitute for either workflow.

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
