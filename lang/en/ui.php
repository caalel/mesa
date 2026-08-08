<?php

return [
    'navigation' => [
        'comparator' => 'Comparator',
    ],
    'homepage' => [
        'name' => 'MESA',
        'full_name' => 'Medidor de Equivalência e Síntese Alimentar',
        'introduction' => 'Compare foods, build meals, and better understand the relationships between calories and macronutrients.',
        'tools_title' => 'Tools',
        'comparator_title' => 'Nutritional comparator',
        'comparator_description' => 'Find the equivalent amount between two foods based on calories.',
        'open_comparator' => 'Open comparator',
    ],
    'compare' => [
        'title' => 'Compare foods',
        'subtitle' => 'Find out how much of one food is equivalent to another in calories.',
        'food_a_section' => 'Reference food',
        'search_food' => 'Search for a food',
        'search_placeholder' => 'Type the food name',
        'no_foods_found' => 'No foods found.',
        'quantity_label' => 'Amount',
        'quantity_placeholder' => 'Enter the amount in grams.',
        'quantity_too_high' => 'Enter an amount of up to :max g to compare.',
        'calorie_data_unavailable' => 'Calorie data unavailable for comparison.',
        'quantity_must_be_numeric' => 'Enter a valid amount in grams.',
        'quantity_must_be_positive' => 'Enter an amount greater than zero.',
        'grams_unit' => 'g',
        'calories_unit' => 'kcal',
        'change_food' => 'Change',
        'food_b_section' => 'Food to compare',
        'submit' => 'Compare',
        'calorie_equivalence' => ':foodAWeight g of :foodAName ≈ :foodBWeight g of :foodBName.',
        'calorie_equivalence_less_than' => ':foodAWeight g of :foodAName ≈ less than :foodBWeight g of :foodBName.',
        'calorie_equivalence_description' => 'To match the calories in :foodAWeight g of :foodAName, you would need about :foodBWeight g of :foodBName.',
        'calorie_equivalence_less_than_description' => 'To match the calories in :foodAWeight g of :foodAName, you would need less than :foodBWeight g of :foodBName.',
    ],
];
