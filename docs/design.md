# Design Direction

## Purpose

This document defines the initial UI/UX and visual identity direction for the application.

The product should feel like a clear, trustworthy nutritional utility. It should not resemble a generic startup template, a wellness app full of motivational language, or an AI-generated interface with decorative excess.

The design direction is:

> Editorial sobriety with technical clarity.

The interface should be:

* Objective and readable.
* Friendly without looking childish.
* Clean without feeling empty.
* Focused on nutritional information hierarchy.
* Built with few elements, where every element has a purpose.

---

## Frontend Stack

The interface will use:

* Livewire for interactive behavior.
* Blade for HTML structure.
* Tailwind CSS for styling.
* CSS variables for visual identity tokens.

No component library should be added initially.

Avoid DaisyUI, Flowbite, prebuilt UI kits, or any library that imposes a generic visual identity. Tailwind should be used in a simple and deliberate way, with reusable visual patterns created only when repetition or complexity justifies them.

---

## Home Page Strategy

Initially, the home page is the nutritional comparator itself.

There should be no separate landing page before the user reaches the tool.

Current route intention:

```text
/ → Nutritional Comparator
```

When the Meal Calculator feature exists, the home page may evolve into a hub where the user chooses between the available tools.

---

## Main User Flow

1. The user opens the home page.
2. The user searches for Food A while typing.
3. The user selects Food A.
4. The user enters the quantity of Food A in grams.
5. The user searches for Food B while typing.
6. The user selects Food B.
7. The user clicks “Compare”.
8. The result appears on the same page, below the form.

The search experience should use Livewire and update while the user types. A debounce should be used to avoid a database query for every keystroke.

The user should never need to understand IDs, nutritional values per 100 g, or internal calculation logic.

---

## Nutritional Comparator Interaction Flow

The home page is the nutritional comparator.

The comparator will be implemented as a class-based, full-page Livewire component.

---

### User Flow

1. The user searches and selects Food A.
2. The search field is replaced by the selected food and an **Alterar** action.
3. The user enters the Food A weight in grams.
4. A summary card for Food A is displayed.
5. The user searches and selects Food B.
6. The search field is replaced by the selected food and an **Alterar** action.
7. The user clicks **Comparar**.
8. The comparison result is displayed below the form.

---

### Food A Summary Card

After Food A is selected and its weight is informed, the interface should display:

* Food name.
* Selected weight in grams.
* Calories for the selected weight.

The calorie calculation must use `NutritionalValuesCalculatorService`.

Example:

```text
Banana
100 g
89 kcal
```

---

### Result Card

The main result should communicate calorie equivalence clearly.

Example:

```text
100 g de Banana ≈ 171 g de Maçã
em calorias
```

Supporting text:

```text
100 g de Banana possuem aproximadamente 89 kcal.

171,15 g de Maçã possuem aproximadamente 89 kcal.
```

The equivalent weight shown in the main result may be rounded for readability, while the supporting text may present the precise value.

---

### Component Responsibilities

The Livewire component is responsible for:

* managing the interface state;
* searching foods using `FoodSearchService`;
* calculating calories using `NutritionalValuesCalculatorService`;
* calculating equivalent weight using `CompareFoodsService`.

The component must not:

* duplicate business rules already implemented in Services;
* call internal HTTP endpoints such as `/foods/search` or `/compare`.

---

## Page Structure

### Desktop

The main form should use two columns:

```text
Food A card                  Food B card
Food selection               Food selection
Food A quantity

              Compare button

              Result card
```

Food A and Food B are placed side by side.

The comparison button is centered below both cards.

The result occupies the full width below the form.

### Mobile

The layout should become a single column:

```text
Food A card
Food A quantity
Food B card
Compare button
Result card
```

The mobile experience must preserve the same order and clarity without hiding essential information.

---

## Initial Interface Copy

### Page title

```text
Compare alimentos
```

### Page subtitle

```text
Descubra quanto de um alimento equivale a outro em calorias.
```

### Food A section

```text
Alimento de referência
```

### Food B section

```text
Alimento para comparar
```

### Quantity label

```text
Quantidade
```

The quantity input should clearly communicate grams:

```text
[ 100 ] g
```

### Main action

```text
Comparar
```

---

## Result Presentation

The result is the main moment of the interface and must have the strongest visual hierarchy on the page.

The primary result should use an approximate equivalence symbol, not an equality symbol.

Example:

```text
100 g de Banana ≈ 171 g de Maçã
em calorias
```

The primary result may round the equivalent amount for visual simplicity.

Supporting text should provide context and may show the more precise value:

```text
100 g de Banana possuem aproximadamente 89 kcal.

Para consumir uma quantidade parecida de calorias,
você precisaria de cerca de 171,15 g de Maçã.
```

The interface must make it clear that this is a calorie equivalence, not complete nutritional equivalence between foods.

---

## Search and Selection States

The search input should provide clear, practical feedback.

Examples:

```text
Digite pelo menos 2 caracteres para buscar um alimento.
```

```text
Nenhum alimento encontrado.
```

After selection, the selected food should be visible as a normal field state with a clear action to change it.

Avoid oversized pills, decorative tags, or excessive badges.

The comparison button should only be enabled when:

* Food A is selected.
* Food B is selected.
* Food A quantity is greater than zero.

---

## Visual Identity

### Color Tokens

```text
Background:        #F6F5F1
Surface:           #FFFFFF
Primary text:      #1D2620
Secondary text:    #667066
Border:            #DCE1DA
Primary green:     #315E46
Light green:       #EAF1EB
Warm accent:       #C7803D
Error:             #B42318
```

### Color Usage

* Use the warm background as the main page background.
* Use white surfaces for cards and form controls.
* Use the primary green for main actions, active states, and focus states.
* Use light green for the result area and subtle positive emphasis.
* Use the warm accent sparingly for small, intentional details only.
* Use the error color only for validation feedback and destructive states.

Gradients should not be used as a decorative element.

---

## Typography

Primary typeface:

```text
IBM Plex Sans
```

Typography should feel technical, readable, and human without looking excessively futuristic or startup-like.

Initial hierarchy:

```text
Page title:      32–36 px, weight 600
Subtitle:        16–18 px, weight 400
Section title:   16–18 px, weight 600
Labels:          14 px, weight 500
Body text:       15–16 px
Main result:     28–36 px, weight 600
```

Avoid oversized titles that consume most of the viewport.

Numbers in the result should be visually easy to scan.

---

## Cards and Surfaces

Cards should be restrained and functional.

Use:

* White surface.
* 1 px border.
* Border color: `#DCE1DA`.
* Border radius between 12 px and 16 px.
* Consistent internal spacing.
* No heavy shadow.

Avoid:

* Cards inside cards unless there is a clear structural reason.
* Oversized rounded corners.
* Floating effects.
* Excessive visual separation between minor sections.

The result card may use a light green background or border treatment to distinguish it from the form without becoming visually loud.

---

## Design Rules

The interface must avoid “AI slop” patterns.

Do not use:

* Decorative gradients.
* Glassmorphism.
* Excessive rounded cards.
* Heavy shadows.
* Cards inside cards without need.
* Generic dashboard layouts.
* Decorative charts without a real user need.
* Excessive pills, chips, badges, or tags.
* Icons in every label or button.
* Unnecessary animations.
* Emoji in the interface.
* Generic wellness or motivational copy.
* Visual elements that do not improve understanding or action.

Examples of copy to avoid:

```text
Transforme sua jornada alimentar.
```

```text
Encontre sua nutrição ideal.
```

Use practical and direct language instead.

---

## Interaction Principles

The interface should explain what the user can do next.

Errors and empty states must be actionable.

Good example:

```text
Digite pelo menos 2 caracteres para buscar um alimento.
```

Bad example:

```text
Oops! Parece que ainda não encontramos sua nutrição ideal.
```

Animations, when eventually used, must communicate feedback or state changes. They should not exist only to make the interface appear modern.

---

## Component Strategy

The initial comparator should be implemented as one class-based, full-page Livewire component.

Expected initial structure:

```text
app/Livewire/NutritionalComparator.php
resources/views/livewire/nutritional-comparator.blade.php
```

The component should manage interface state and delegate business rules to existing services.

It should use:

```text
FoodSearchService
CompareFoodsService
```

Do not split the initial comparator into multiple child Livewire components prematurely.

Extract child components only when repeated patterns or real complexity justify it.

---

## Localization and Food Naming Strategy

The application should be prepared for future multilingual support from the beginning.

Interface translation and food name translation are separate concerns.

---

### Interface Translation

User-facing interface text must use Laravel localization files with PHP arrays.

Initial structure:

```text
lang/
├── pt_BR/
│   └── ui.php
└── en/
    └── ui.php
```

The initial interface language is `pt_BR`.

The future English interface locale is `en`.

User-facing text must not be hardcoded directly in Blade templates or Livewire components.

Use translation keys grouped by feature or screen:

```php
return [
    'compare' => [
        'title' => 'Compare alimentos',
        'subtitle' => 'Descubra quanto de um alimento equivale a outro em calorias.',
        'food_a_section' => 'Alimento de referência',
        'food_b_section' => 'Alimento para comparar',
        'quantity_label' => 'Quantidade',
        'submit' => 'Comparar',
    ],
];
```

Internal code identifiers, database columns, routes, services, and model names remain in English and must not be translated.

---

### Food Name Translation

Food names are dynamic database data and must not use Laravel interface translation files.

The `foods` table must use explicit columns:

```text
name_pt: required
name_en: nullable
```

The application will not use a JSON translation column or a translatable package at this stage.

Display rule:

```text
Locale pt_BR:
display name_pt

Locale en:
display name_en
fallback to name_pt when name_en is null
```

Current search rule:

```text
Locale pt_BR:
search by name_pt
```

When the English interface is implemented, English search behavior and minimum translation coverage for food names must be defined separately.

A missing English translation must never result in an empty food name. The fallback display value is `name_pt`.
