# MESA Design

## Purpose and Direction

This document describes the current interface of the MESA MVP: its visual
direction, comparator flow, interface states, and interaction behavior.

The design is sober, editorial, technical, and clear. It prioritizes contrast,
readability, and information hierarchy. Surfaces are simple and functional, with
no decorative gradients, glassmorphism, or heavy shadows.

## Typography

IBM Plex Sans is the primary typeface. It is loaded globally from Bunny Fonts with
weights 400, 500, and 600. The font stack retains `sans-serif` as a fallback.

The interface uses regular text for content, medium weight for labels and secondary
actions, and semibold weight for headings, selected food names, primary actions,
and results.

## Visual Tokens

The current CSS tokens are:

| Token | Value | Use |
| --- | --- | --- |
| Background | `#F6F5F1` | Page background and subtle inactive surfaces. |
| Surface | `#FFFFFF` | Cards, controls, and header hover surface. |
| Primary text | `#1D2620` | Headings and primary content. |
| Secondary text | `#667066` | Supporting text and labels. |
| Border | `#DCE1DA` | Cards, controls, and content separators. |
| Primary green | `#315E46` | Main action, focus states, and emphasis. |
| Light green | `#EAF1EB` | Selected-food state and interactive highlights. |
| Warm accent | `#C7803D` | Result-card left border. |
| Error | `#B42318` | Validation and unavailable-calorie feedback. |

## Global Header

The global header contains the `MESA` brand on the left, a link to the comparator,
and a `PT`/`EN` language selector on the right. It is transparent in its normal
state. On hover or `focus-within`, the navigation gains a rounded white surface and
a subtle shadow.

The header has no active-route indicator or additional tools beyond the comparator
link and language selector.

## Page Structure

The comparator page contains:

1. the global header;
2. a main area with the comparator title and introductory text;
3. the Food A card;
4. the Food B card;
5. the Food A summary, when applicable;
6. the comparison button;
7. the result block, after a successful comparison.

Each food card contains either a search state or a selected-food state. The selected
state displays the food name and an action that returns the card to its search state.

The comparator uses the Blade components `compare-search-result-item` and
`compare-selected-food` to structure search results and selected-food controls.

## Comparator Flow

1. The user searches for and selects Food A.
2. The user enters Food A weight.
3. A valid Food A selection and weight display a calorie summary.
4. The user searches for and selects Food B.
5. When all required inputs are valid, the user triggers the comparison.
6. The result appears below the form and the page scrolls smoothly to it.

The same food can be selected on both sides. In that case, its equivalent weight is
mathematically the same as the entered Food A weight.

## Food Search and Selection

Search starts after at least two characters and uses a 300 ms Livewire debounce.
It searches only the food-name column for the active locale (`name_pt` for `pt_BR`,
`name_en` for `en`) and returns at most eight results. There is intentionally no
fallback between the two columns.

Every typed term is required. Terms may be separated by other words or punctuation
in the food name. More direct matches are shown before other compatible results,
with alphabetical order as the tiebreaker.

Before the minimum length, the interface provides guidance about the search
requirement. When no match is found, it provides friendly empty-state feedback. A
selected food replaces its search field and can be changed to reopen that search
state.

## Food A Weight and Validation

Food A weight accepts either a point or a comma as the decimal separator. Both forms
are interpreted as the same numeric value.

The value must be numeric, greater than zero, and no more than 10,000 g. The input
placeholder communicates that the field expects a weight in grams.

The interface provides friendly feedback for:

* a non-numeric value;
* zero or a negative value;
* a weight above the maximum.

Changing Food A weight clears any previous result.

## Comparison Availability and Unavailable Data

The comparison action is available only when Food A and Food B are selected, both
have positive calorie values available, and Food A weight is valid and within the
maximum.

Foods with zero or negative calories present clear textual feedback about unavailable
calorie data. They cannot participate in a caloric equivalence. When Food A has
unavailable calorie data, its weight input is disabled; unavailable data on either
side prevents comparison.

Changing either selected food also clears a previous result.

## Summary and Result

The Food A summary shows its localized name, formatted weight, and calories
calculated from the entered weight. Values use number formatting for the active
locale. Search results, selected foods, summaries, and comparison results all use
`localized_name`.

The result presents an approximate caloric equivalence and uses a warm-accent left
border to distinguish it from the form. Positive values below `0,01` are displayed
as `< 0,01` instead of zero.

After a successful comparison, the page scrolls smoothly to bring the result into view.

## Responsive Layout

On smaller screens, the Food A and Food B cards stack in a single column. At larger
layout widths, they are displayed in two columns, with the comparison control and
result below them.

Cards and controls use constrained widths, `min-w-0`, and responsive spacing to
avoid overflow. Food names, search results, and result text can break across lines;
controls remain usable at mobile widths.

## Language and Interface Copy

The interface supports `pt_BR` and `en`. User-facing text is centralized in
`lang/pt_BR/ui.php` and `lang/en/ui.php` and rendered through localization keys.
A valid locale stored in the session takes precedence; otherwise, the initial
request uses `Accept-Language`, mapping Portuguese variants to `pt_BR`, English to
`en`, and unsupported languages to `pt_BR`. The header selector lets the user
change this choice manually.

Food-name localization is separate from interface-copy localization. English food
names are editorial translations of the Brazilian catalog, and may deliberately
retain Brazilian Portuguese terms where a literal English equivalent would be less
clear or would distort the food's culinary identity. The active locale determines
which stored name is searched and displayed; it does not imply an international
food-database equivalence.

## Accessibility and Interaction

The interface uses native buttons for actions and links for navigation. Form inputs
have associated labels, and validation or empty states provide textual feedback.

Interactive controls expose visible focus styles. Native controls support keyboard
interaction, while disabled comparison and unavailable-calorie states communicate
their status visually and through text. This document does not claim a complete
accessibility or WCAG audit.

## Documentation Boundaries

Current architecture, services, persistence, integration, and technical operations
are documented in [`docs/architecture.md`](architecture.md). Dataset provenance and
scientific decisions are documented in [`docs/data-sources.md`](data-sources.md).
Development conventions are defined in [`AGENTS.md`](../AGENTS.md).
