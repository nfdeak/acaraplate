import { cn } from '@/lib/utils';
import { Dumbbell, Flame, Leaf, type LucideIcon, Wheat } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface NutritionStatsProps {
    calories: number;
    protein: number | null;
    carbs: number | null;
    fat: number | null;
    className?: string;
    size?: 'sm' | 'md' | 'lg';
}

interface NutritionMetric {
    label: string;
    value: string;
    Icon: LucideIcon;
    valueClassName: string;
}

export function NutritionStats({
    calories,
    protein,
    carbs,
    fat,
    className,
    size = 'md',
}: NutritionStatsProps) {
    const { t } = useTranslation('common');
    const labelSizeClasses = {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-sm',
    };

    const valueSizeClasses = {
        sm: 'text-base',
        md: 'text-lg',
        lg: 'text-xl',
    };

    const metrics: NutritionMetric[] = [
        {
            label: t('meal_plans.nutrition.calories'),
            value: Math.round(calories).toString(),
            Icon: Flame,
            valueClassName: 'text-foreground',
        },
        {
            label: t('meal_plans.nutrition.protein'),
            value: formatGrams(protein),
            Icon: Dumbbell,
            valueClassName: 'text-cyan-700 dark:text-cyan-300',
        },
        {
            label: t('meal_plans.nutrition.carbs'),
            value: formatGrams(carbs),
            Icon: Wheat,
            valueClassName: 'text-emerald-700 dark:text-emerald-300',
        },
        {
            label: t('meal_plans.nutrition.fat'),
            value: formatGrams(fat),
            Icon: Leaf,
            valueClassName: 'text-amber-700 dark:text-amber-300',
        },
    ];

    return (
        <div
            className={cn(
                'grid grid-cols-2 gap-2 rounded-xl bg-muted/40 p-2 sm:grid-cols-4',
                className,
            )}
        >
            {metrics.map(({ label, value, Icon, valueClassName }) => (
                <div
                    key={label}
                    className="flex min-h-20 flex-col justify-between rounded-lg border bg-background/80 p-3"
                >
                    <div className="flex items-center gap-1.5 text-muted-foreground">
                        <Icon className="h-4 w-4" aria-hidden="true" />
                        <span
                            className={cn(
                                'font-medium',
                                labelSizeClasses[size],
                            )}
                        >
                            {label}
                        </span>
                    </div>
                    <span
                        className={cn(
                            'mt-2 font-semibold tabular-nums',
                            valueSizeClasses[size],
                            valueClassName,
                        )}
                    >
                        {value}
                    </span>
                </div>
            ))}
        </div>
    );
}

function formatGrams(value: number | null): string {
    if (value === null) {
        return '-';
    }

    return `${Math.round(value)}g`;
}
