<?php

declare(strict_types=1);

namespace App\Enums;

enum PostCategory: string
{
    case DiabetesManagement = 'diabetes_management';
    case NutritionTips = 'nutrition_tips';
    case Recipes = 'recipes';
    case Research = 'research';
    case Lifestyle = 'lifestyle';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function label(): string
    {
        return match ($this) {
            self::DiabetesManagement => 'Diabetes Management',
            self::NutritionTips => 'Nutrition Tips',
            self::Recipes => 'Recipes',
            self::Research => 'Research',
            self::Lifestyle => 'Lifestyle',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::DiabetesManagement => 'Diabetes Management Tips & Blood Sugar Control Guides',
            self::NutritionTips => 'Nutrition Tips for Diabetics: Eating Well with Diabetes',
            self::Recipes => 'Diabetes-Friendly Recipes: Low GI Meals & Snacks',
            self::Research => 'Latest Diabetes Research & Nutrition Science',
            self::Lifestyle => 'Living Well with Diabetes: Lifestyle & Wellness',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DiabetesManagement => 'Practical guides for managing Type 2 diabetes, monitoring blood sugar, and preventing glucose spikes.',
            self::NutritionTips => 'Evidence-based nutrition advice for diabetics—understanding carbs, glycemic index, and making smarter food choices.',
            self::Recipes => 'Delicious, diabetes-friendly recipes designed to keep your blood sugar stable without sacrificing flavor.',
            self::Research => 'Summaries of the latest research in diabetes nutrition, glucose management, and metabolic health.',
            self::Lifestyle => 'Tips for exercising, sleeping, and managing stress—all essential for keeping blood sugar in check.',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::DiabetesManagement => 1,
            self::NutritionTips => 2,
            self::Recipes => 3,
            self::Research => 4,
            self::Lifestyle => 5,
        };
    }
}
