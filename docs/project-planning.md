# Project Planning

## Domain

Nutrition / Nutritional Calculation

---

## Problem

Facilitate daily nutritional calculations and food substitutions without requiring users to manually search nutritional tables and perform calculations.

The main goal is to quickly answer questions such as:

* "How much pasta can I eat instead of rice?"
* "How many calories and macros does this meal have?"
* "What is the nutritional impact of replacing one food with another?"

---

## MVP Scope

### Included

#### Nutritional Comparator

Allows users to compare two foods based on calories.

The user selects:

* Food A
* Quantity of Food A (grams)
* Food B

The system calculates:

* Equivalent quantity of Food B based on calories.

---

#### Meal Calculator

Allows users to build a meal by adding foods and quantities.

The system calculates:

* Total calories
* Total carbohydrates
* Total proteins
* Total fats

The calculation is updated automatically whenever foods are added, edited or removed.

---

#### Food Search

Used by both features.

Users can search and select foods from the nutritional database.

---

## Out of Scope (Future Versions)

* Authentication
* User accounts
* Custom foods
* Saved meals
* Favorites
* Mobile import
---

## Internationalization

### Internal Language

* Code in English
* Database in English
* Documentation in English
* Commits in English

### User Interface

* Multi-language structure prepared from the beginning
* Portuguese and English supported in the future
* Initial implementation kept simple

---

## Data Source Strategy

Current direction:

### Primary Source

* TACO database imported into local database

### Fallback Source

* USDA API (or equivalent)

### Future Improvement

* Local cache for external data

Goal:

Maintain fast searches while avoiding full dependency on third-party APIs.

---

## Main User Flow - Nutritional Comparator

1. User accesses the home page.
2. User selects "Nutritional Comparator".
3. User searches for Food A.
4. System displays matching foods.
5. User selects Food A.
6. User enters the quantity in grams.
7. User searches for Food B.
8. System displays matching foods.
9. User selects Food B.
10. System calculates the caloric equivalence.
11. System displays the equivalent quantity of Food B.

---

## Main User Flow - Meal Calculator

1. User accesses the home page.
2. User selects "Meal Calculator".
3. User searches for a food.
4. System displays matching foods.
5. User selects a food.
6. User enters the quantity in grams.
7. System adds the food to the meal.
8. System recalculates nutritional totals.
9. User may continue adding foods.
10. User may remove foods.
11. User may edit food quantities.
12. System recalculates automatically after every change.

---

## Current Domain Entities

### Food

Represents a food available in the nutritional database.

Attributes:

* id
* name_pt
* name_en (nullable)
* calories
* protein
* carbs
* fat
* source
* created_at
* updated_at

Notes:

* Nutritional values are stored per 100g.
* Food data may originate from TACO, USDA or future data sources.
* English translations may be added progressively after the initial TACO import.

---

### Meal

Represents a meal being assembled by the user.

Attributes:

* id

Notes:

* A Meal is composed of multiple MealItems.
* Calculated nutritional totals are not stored.
* Meal exists only in the domain during V1 and is not persisted.

---

### MealItem

Represents a food inside a meal.

Attributes:

* id
* meal_id
* food_id
* weight

Notes:

* Weight is always represented in grams.
* Nutritional values are calculated from the associated Food and weight.
* MealItem exists only in the domain during V1 and is not persisted.

---

## Entity Relationships

### Meal → MealItem

Relationship:

One-to-Many

Rules:

* A Meal can contain many MealItems.
* A MealItem belongs to exactly one Meal.

---

### Food → MealItem

Relationship:

One-to-Many

Rules:

* A Food can appear in many MealItems.
* A MealItem references exactly one Food.

Example:

Food (Rice)
├── MealItem
├── MealItem
└── MealItem

The same food may appear in multiple meals.

---

## Modeling Decisions

### Store Source Data Only

The system stores only source nutritional data.

Examples:

* calories
* protein
* carbs
* fat

Stored on Food.

---

### Do Not Store Calculated Values

The following values must be calculated dynamically:

* meal total calories
* meal total protein
* meal total carbs
* meal total fat
* nutritional values of a MealItem

Reason:

Avoid duplicated information and data inconsistency.

---

### Persistence Strategy

For V1, only Food data will be persisted in the database.

Meal and MealItem remain part of the domain model but will not be persisted.

Reason:

* No authentication system exists yet.
* Meals do not need to be recovered later.
* Reduces implementation complexity.
* Keeps the focus on validating the core features.

Future versions may introduce:

* User authentication
* Persisted meals
* Meal history
* Saved meals

---

### Avoid Premature Complexity

The MVP prioritizes simple modeling.

Examples of features intentionally excluded:

* user accounts
* saved meals
* custom foods
* favorites
* diet planning
* nutritional goals

---

## Business Rules

### RN-001

The weight provided by the user must be greater than zero grams.

---

### RN-002

The system must accept only grams (g) as the measurement unit.

---

### RN-003

A meal cannot contain duplicate foods.

---

### RN-004

If the user adds a food that already exists in the meal, the system must add the new weight to the existing MealItem.

Example:

Rice 100g + Rice 50g = Rice 150g

---

### RN-005

The quantity used in food comparison must be greater than zero grams.

---

### RN-006

A food must contain valid nutritional values to participate in calculations.

Examples of invalid values:

* Negative calories
* Negative protein values
* Negative carbohydrate values
* Negative fat values

---

### RN-007

All nutritional calculations must use the nutritional values per 100g as the reference.

---

## Use Cases

### UC-001 - Search Foods

Allows the user to search for foods available in the nutritional database.

---

### UC-002 - Compare Foods

Allows the user to compare two foods based on calories and determine an equivalent quantity.

---

### UC-003 - Add Food To Meal

Allows the user to add a food and its weight to a meal.

---

### UC-004 - Remove Food From Meal

Allows the user to remove a food from a meal.

---

### UC-005 - Update Meal Item Quantity

Allows the user to update the weight of a food already present in a meal.

---

### UC-006 - Calculate Meal Nutrition

Allows the system to calculate total calories, carbohydrates, proteins and fats for a meal.

---

### UC-007 - List Meal Items

Allows the user to view all foods currently added to a meal.

---

## Current Status

Completed:

* Domain definition
* Problem definition
* MVP definition
* Internationalization strategy
* Data source strategy
* Nutritional Comparator flow
* Meal Calculator flow
* Domain entities definition
* Entity relationships definition
* Modeling decisions definition
* Persistence strategy definition
* Business rules definition
* Use cases definition
* Application architecture definition
* Service layer definition
* Controllers responsibilities definition
* Routes definition
* Models responsibilities definition
* Requests definition
* Database modeling
* Foods table definition
* Testing architecture definition
* TDD conventions definition
* Folder structure for tests
* Database testing strategy
* Implementation plan definition

Project planning phase completed.
