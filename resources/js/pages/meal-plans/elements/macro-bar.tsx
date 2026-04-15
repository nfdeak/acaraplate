import { cn } from '@/lib/utils';
import { MacroPercentages } from '@/types/meal-plan';
import { useTranslation } from 'react-i18next';

interface MacroBarProps {
    macros: MacroPercentages;
    className?: string;
    showLegend?: boolean;
}

export function MacroBar({
    macros,
    className,
    showLegend = false,
}: MacroBarProps) {
    const { protein, carbs, fat } = macros;
    const { t } = useTranslation('common');
    const segments = [
        {
            label: t('meal_plans.nutrition.protein'),
            value: protein,
            className: 'bg-cyan-500',
        },
        {
            label: t('meal_plans.nutrition.carbs'),
            value: carbs,
            className: 'bg-emerald-500',
        },
        {
            label: t('meal_plans.nutrition.fat'),
            value: fat,
            className: 'bg-amber-500',
        },
    ];

    return (
        <div className={cn('space-y-2', className)}>
            <div className="sr-only">
                {segments
                    .map((segment) => `${segment.label}: ${segment.value}%`)
                    .join(', ')}
            </div>
            <div
                className="flex h-2.5 w-full overflow-hidden rounded-full bg-muted"
                aria-hidden="true"
            >
                {segments.map((segment) => (
                    <div
                        key={segment.label}
                        className={cn(
                            'transition-[width] duration-300 ease-out',
                            segment.className,
                        )}
                        style={{ width: `${clampPercentage(segment.value)}%` }}
                        title={`${segment.label}: ${segment.value}%`}
                    />
                ))}
            </div>

            {showLegend && (
                <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted-foreground">
                    {segments.map((segment) => (
                        <div
                            key={segment.label}
                            className="flex items-center gap-1.5"
                        >
                            <span
                                className={cn(
                                    'h-2.5 w-2.5 rounded-full',
                                    segment.className,
                                )}
                                aria-hidden="true"
                            />
                            <span>
                                {segment.label} {segment.value}%
                            </span>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

function clampPercentage(value: number): number {
    return Math.min(100, Math.max(0, value));
}
