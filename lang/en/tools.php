<?php

declare(strict_types=1);

return [
    'caffeine_calculator' => [
        'meta' => [
            'title' => 'Coffee Caffeine Calculator: How Much Is Too Much?',
            'description' => 'Free caffeine calculator: estimate your safe daily caffeine dose and find out when to stop drinking coffee for better sleep.',
            'keywords' => 'caffeine calculator, safe caffeine dose, caffeine sleep cutoff, coffee calculator, caffeine half life',
        ],
        'heading' => 'Coffee Caffeine Calculator: How Much Is Too Much?',
        'subheading' => 'Choose your drink, tell us about you, and find your safe daily limit.',
        'form' => [
            'weight' => [
                'label' => 'Your weight',
                'placeholder' => 'e.g. 70',
                'unit_group_label' => 'Weight unit',
                'unit_kg' => 'Kilos',
                'unit_lb' => 'Pounds',
                'errors' => [
                    'required' => 'Enter your weight to calculate.',
                    'numeric' => 'Weight must be a number.',
                    'gt' => 'Weight must be greater than 0.',
                ],
            ],
            'sensitivity' => [
                'label' => 'Caffeine sensitivity',
                'group_label' => 'Caffeine sensitivity',
                'step_aria_label' => 'Sensitivity step :step of 5',
                'more_tolerant' => 'More tolerant',
                'normal' => 'Normal',
                'more_sensitive' => 'More sensitive',
            ],
            'drink' => [
                'label' => 'Your drink',
                'placeholder' => 'Choose a drink',
            ],
            'bedtime' => [
                'label' => 'Your bedtime',
                'placeholder' => 'e.g. 11:00 PM',
            ],
            'cta' => 'How Much Coffee?',
        ],
        'result' => [
            'heading' => 'Your safe daily limit',
            'safe_dose' => ':mg mg of caffeine per day',
            'cups' => 'About :cups cups of your drink',
            'cups_one' => 'About 1 cup of your drink',
            'sleep_cutoff' => 'Stop drinking caffeine by :time for better sleep',
            'no_cutoff_needed' => 'Your intake should not affect your sleep tonight.',
            'recalculate' => 'Recalculate',
        ],
        'disclosures' => [
            'not_medical_advice' => 'This calculator provides general estimates and is not medical advice. Consult a healthcare professional for guidance specific to you.',
            'pregnancy' => 'If you are pregnant, breastfeeding, or have a health condition, lower limits may apply.',
            'sources' => 'Caffeine values are sourced from the USDA FoodData Central public-domain database.',
            'half_life' => 'Sleep cutoff uses a 5-hour caffeine half-life with a 50 mg residual threshold at bedtime.',
        ],
    ],
];
