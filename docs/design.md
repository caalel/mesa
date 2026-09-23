# MESA Design

## Purpose and Direction

This document describes the current interface of the MESA MVP: its visual
direction, Home, comparator and meal-calculator flows, interface states, and
interaction behavior.

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
| Error | `#B42318` | Validation and unavailable-nutrient feedback. |

## Global Header

The global header contains the `MESA` brand, navigation for Meals and Comparator,
the `PT`/`EN` language selector, and an authentication action. Guests see a subtle
user-icon sign-in link. Authenticated users see their truncated name and user icon
in a native disclosure control; its menu shows the full name, email, and a red text
sign-out action. There is no account-management link. The header is transparent in
its normal state. On hover or `focus-within`, the navigation gains a rounded white
surface and a subtle shadow.

The current route is indicated with the light-green surface and primary-green
text, and exposes `aria-current="page"`. Below the `lg` breakpoint (1024 px), the
brand, geometrically centered locale selector, and authentication action occupy the
first row; primary navigation occupies a second centered row. From `lg` onward,
navigation remains visually centered while the brand and locale/authentication
controls anchor the sides.

## Authentication

Guests can open dedicated registration and sign-in pages that retain the global
header and use a single responsive form card. Registration has name, email,
password, and password-confirmation fields with field-level validation feedback.
Sign-in has email and password fields and presents a single generic credentials
error, without indicating which credential failed. The pages link to each other;
the current interface intentionally has no Remember me, password recovery, or
account-management controls.

## Home

The Home page is the hub for the two MESA tools. A centered hero introduces the
project, followed by two responsive cards: Nutritional Comparator and Meals. Each
card summarizes its tool and provides its primary navigation action. The cards sit
side by side when space allows and stack on smaller screens.

## Page Structure

The comparator page contains:

1. the global header;
2. a main area with the comparator title and introductory text;
3. a persistent nutrient criterion selector;
4. the Food A card and its summary, when applicable;
5. the Food B card and its summary, when applicable;
6. the result block, after a valid equivalence is derived.

Each food card contains either a search state or a selected-food state. The selected
state displays the food name and an action that returns the card to its search state.

The comparator uses the Blade components `compare-search-result-item` and
`compare-selected-food` to structure search results and selected-food controls.

## Meals

The Meals page has three primary states:

1. **Empty**, which invites the user to create the first meal;
2. **Editor**, for creating or editing a meal; and
3. **List**, which presents saved meals and their nutritional totals.

The editor has a meal name, an item list, a nutritional summary, and cancel and
save actions. At `lg` and above, its main editing area and nutritional summary use
two columns, with the summary on the right and sticky while the page scrolls. Below
`lg`, those regions stack vertically. Foods in the draft appear as individual cards
with spacing between them rather than dividers. A meal requires a name and at least
one Food. It can contain at most 10 distinct Foods. Removing the last item returns
the item area to its empty state; cancelling discards the current editor draft.

Saved meals show their Food count, calories, protein, carbohydrates, and fat. They
can be reopened for editing or deleted immediately from the list.

After a guest saves the first Meal, the list can show an inline authentication
callout. It offers sign in as the primary action, account creation as the secondary
action, and a discreet dismissal control. It is not a modal or toast, does not show
for authenticated users, and does not repeat during the same session after dismissal
or either authentication action.

### Food Details Modal

Foods are added and edited in a modal. It provides localized search, a no-results
state, Food selection, a weight field, and a nutritional preview for that weight.
The preview and actions react to valid input. Validation communicates non-numeric,
non-positive, excessive, and combined-weight-limit values. Adding a Food updates
the draft; editing an item can change its weight or Food. Duplicate Foods merge by
weight rather than producing another distinct item.

## Comparator Flow

1. Calories is selected by default; the user can select calories, protein,
   carbohydrates, or fat at any time.
2. The user searches for and selects Food A, then enters Food A weight.
3. A valid Food A selection and weight display a complete nutritional summary.
4. The user searches for and selects Food B, whose summary initially represents
   100 g.
5. When Food A, a valid Food A weight, Food B, and positive values for the selected
   nutrient are available, the equivalence is derived automatically.
6. The result and Food B summary update automatically whenever either Food, Food A
   weight, or the selected nutrient changes. The page scrolls smoothly to a new or
   recalculated valid result.

The same food can be selected on both sides. In that case, its equivalent weight is
mathematically the same as the entered Food A weight.

## Food Search and Selection

Search starts from the first non-whitespace character. It searches only the food-name
column for the active locale (`name_pt` for `pt_BR`,
`name_en` for `en`) and returns at most eight results. There is intentionally no
fallback between the two columns.

Every typed term is required. Terms may be separated by other words or punctuation
in the food name. More direct matches are shown before other compatible results,
with alphabetical order as the tiebreaker.

Before the minimum length, the interface provides guidance about the search
requirement. When no match is found, it provides friendly empty-state feedback. A
selected food replaces its search field and can be changed to reopen that search
state.

### Reactive Fields and Debounce

Use a 300 ms Livewire debounce for fields that must react while the user types,
such as food search and weight changes. Without this interval, rapid typing can
send multiple overlapping Livewire updates; in this environment, that caused
intermittent internal 404 responses even though the application and its normal
routes were available. Debouncing waits briefly after the last keystroke, reducing
those requests while keeping the interface responsive.

Do not apply it indiscriminately: use it when each change triggers reactive
processing or an update during typing, not for inputs that do not need it.

## Food A Weight and Validation

Food A weight accepts either a point or a comma as the decimal separator. Both forms
are interpreted as the same numeric value.

The value must be numeric, greater than zero, and no more than 10,000 g. The input
placeholder communicates that the field expects a weight in grams.

The interface provides friendly feedback for:

* a non-numeric value;
* zero or a negative value;
* a weight above the maximum.

Changing Food A weight updates the derived result when the remaining inputs permit
an equivalence; otherwise no result is shown.

## Comparison Availability and Unavailable Data

The result is available only when Food A and Food B are selected, Food A weight is
valid and within the maximum, and both Foods have a positive value for the selected
nutrient. The result is derived rather than stored as mutable component state.

A selected Food with a zero or negative value for the active criterion presents a
local unavailable-nutrient message in that Food's card, even before the other inputs
are complete. Food A's weight input is disabled in that state. Changing to a
criterion with available values removes the message and can produce the result
immediately.

## Summary and Result

Both Food summaries show calories, protein, carbohydrates, and fat using locale-aware
number formatting. Food A's summary represents the entered weight. Food B's summary
represents 100 g before a valid equivalence and the calculated equivalent weight
after one. Search results, selected foods, summaries, and comparison results all use
`localized_name`.

The result presents the selected criterion, approximate equivalent amounts, and the
matched nutritional value in each portion. It uses a warm-accent left border to
distinguish it from the form. Positive values below `0,01` are displayed as
`< 0,01` instead of zero, including an equivalent Food B weight below that minimum.

The page scrolls smoothly to the result only after a valid equivalence is produced or
recalculated.

## Responsive Layout

On smaller screens, the nutrient selector uses two columns and the Food A and Food B
cards stack in a single column. At larger layout widths, the selector is horizontal
and the Food cards are displayed in two columns, with the result below them.

Each nutritional summary uses a compact responsive two-by-two grid for calories,
protein, carbohydrates, and fat. Its weight indicator uses the warm accent.

Cards and controls use constrained widths, `min-w-0`, and responsive spacing to
avoid overflow. Food names, search results, and result text can break across lines;
controls remain usable at mobile widths.

## Language and Interface Copy

The interface supports `pt_BR` and `en`. User-facing copy is rendered through
localization keys, and the header selector lets the user change the active language.
Locale resolution, session storage, and localized Food-search behavior are
documented in [`docs/architecture.md`](architecture.md). The editorial policy for
Food names belongs in [`docs/data-sources.md`](data-sources.md).

## Accessibility and Interaction

The interface uses native buttons for actions and links for navigation. Form inputs
have associated labels, and validation or empty states provide textual feedback.

Interactive controls expose visible focus styles. Native controls support keyboard
interaction, while the selected nutrient, disabled Food A weight input, and local
unavailable-nutrient states communicate their status visually and through text. This
document does not claim a complete accessibility or WCAG audit.

## Documentation Boundaries

Current architecture, services, persistence, integration, and technical operations
are documented in [`docs/architecture.md`](architecture.md). Dataset provenance and
scientific decisions are documented in [`docs/data-sources.md`](data-sources.md).
Development conventions are defined in [`AGENTS.md`](../AGENTS.md).
