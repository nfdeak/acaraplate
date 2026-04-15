import {
    store as generateGroceryList,
    show as showGroceryList,
    toggleItem,
} from '@/actions/App/Http/Controllers/GroceryListController';
import printGroceryList from '@/actions/App/Http/Controllers/PrintGroceryListController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Progress } from '@/components/ui/progress';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import mealPlans from '@/routes/meal-plans';
import { type BreadcrumbItem } from '@/types';
import {
    GroceryCategory,
    GroceryStatus,
    type GroceryItem,
    type GroceryList,
    type MealPlanSummary,
} from '@/types/grocery-list';
import { Head, Link, router, useForm, usePoll } from '@inertiajs/react';
import {
    ArrowLeft,
    Beef,
    BottleWine,
    CalendarDays,
    Carrot,
    CircleAlert,
    CircleCheck,
    Croissant,
    CupSoda,
    LayoutGrid,
    Leaf,
    Loader2,
    Milk,
    Package,
    Package2,
    Printer,
    RefreshCw,
    ShoppingCart,
    Snowflake,
    type LucideIcon,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

interface GroceryListPageProps {
    mealPlan: MealPlanSummary;
    groceryList: GroceryList | null;
}

type ViewMode = 'category' | 'day';

const groceryCategoryIcons: Record<
    string,
    { Icon: LucideIcon; className: string }
> = {
    [GroceryCategory.Produce]: {
        Icon: Carrot,
        className:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    },
    [GroceryCategory.Dairy]: {
        Icon: Milk,
        className:
            'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
    },
    [GroceryCategory.MeatAndSeafood]: {
        Icon: Beef,
        className:
            'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    },
    [GroceryCategory.Pantry]: {
        Icon: Package2,
        className:
            'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    },
    [GroceryCategory.Frozen]: {
        Icon: Snowflake,
        className:
            'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
    },
    [GroceryCategory.Bakery]: {
        Icon: Croissant,
        className:
            'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
    },
    [GroceryCategory.Beverages]: {
        Icon: CupSoda,
        className:
            'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
    },
    [GroceryCategory.CondimentsAndSauces]: {
        Icon: BottleWine,
        className:
            'bg-lime-100 text-lime-700 dark:bg-lime-500/15 dark:text-lime-300',
    },
    [GroceryCategory.HerbsAndSpices]: {
        Icon: Leaf,
        className:
            'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300',
    },
    [GroceryCategory.Other]: {
        Icon: Package,
        className:
            'bg-muted text-muted-foreground dark:bg-muted dark:text-muted-foreground',
    },
};

export default function GroceryListPage({
    mealPlan,
    groceryList,
}: GroceryListPageProps) {
    const regenerateForm = useForm({});
    const [viewMode, setViewMode] = useState<ViewMode>('category');
    const { t } = useTranslation('common');

    const isGenerating = groceryList?.status === GroceryStatus.Generating;
    const hasNoList = !groceryList;

    const { start, stop } = usePoll(
        4000,
        { only: ['groceryList'] },
        {
            autoStart: false,
        },
    );

    useEffect(() => {
        if (isGenerating) {
            start();
        } else {
            stop();
        }
    }, [isGenerating, start, stop]);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('meal_plans.title'),
            href: mealPlans.index().url,
        },
        {
            title: t('grocery_list.title'),
            href: showGroceryList(mealPlan.id).url,
        },
    ];

    const handleToggleItem = (item: GroceryItem) => {
        router.patch(toggleItem(item.id).url, {}, { preserveScroll: true });
    };

    const handleRegenerate = () => {
        regenerateForm.post(generateGroceryList.url(mealPlan.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('grocery_list.title')} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-hidden p-4 md:p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm sm:flex-row sm:items-start sm:justify-between md:p-5">
                    <div className="space-y-2">
                        <div className="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label={t('grocery_list.back_to_meal_plan')}
                                asChild
                            >
                                <Link href={mealPlans.index().url}>
                                    <ArrowLeft className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Badge variant="default" className="gap-1.5">
                                <ShoppingCart className="h-3.5 w-3.5" />
                                {t('grocery_list.title')}
                            </Badge>
                        </div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {t('grocery_list.title')}
                        </h1>
                        <p className="text-muted-foreground">
                            {t('grocery_list.day_meal_plan', {
                                days: mealPlan.duration_days,
                            })}
                        </p>
                    </div>

                    {/* Actions */}
                    <div className="grid gap-2 sm:flex sm:items-center">
                        <Button
                            variant="outline"
                            size="sm"
                            className="min-h-10 w-full sm:w-auto"
                            onClick={handleRegenerate}
                            disabled={regenerateForm.processing || isGenerating}
                        >
                            {regenerateForm.processing || isGenerating ? (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            ) : (
                                <RefreshCw className="mr-2 h-4 w-4" />
                            )}
                            {hasNoList
                                ? t('grocery_list.generate')
                                : t('grocery_list.regenerate')}
                        </Button>
                        {groceryList && (
                            <Button
                                variant="outline"
                                size="sm"
                                className="min-h-10 w-full sm:w-auto"
                                asChild
                                disabled={isGenerating}
                            >
                                <a
                                    href={printGroceryList(mealPlan.id).url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Printer className="mr-2 h-4 w-4" />
                                    {t('grocery_list.print')}
                                </a>
                            </Button>
                        )}
                    </div>
                </div>

                {hasNoList ? (
                    <EmptyGroceryListState
                        onGenerate={handleRegenerate}
                        isGenerating={regenerateForm.processing}
                    />
                ) : isGenerating ? (
                    <GroceryListSkeleton />
                ) : (
                    <GroceryListContent
                        groceryList={groceryList}
                        onToggleItem={handleToggleItem}
                        onRegenerate={handleRegenerate}
                        isRegenerating={regenerateForm.processing}
                        viewMode={viewMode}
                        onViewModeChange={setViewMode}
                    />
                )}
            </div>
        </AppLayout>
    );
}

interface EmptyGroceryListStateProps {
    onGenerate: () => void;
    isGenerating: boolean;
}

function EmptyGroceryListState({
    onGenerate,
    isGenerating,
}: EmptyGroceryListStateProps) {
    const { t } = useTranslation('common');
    return (
        <div className="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed bg-card p-8 text-center shadow-sm md:p-12">
            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <ShoppingCart className="h-8 w-8" />
            </div>
            <h3 className="text-xl font-semibold">
                {t('grocery_list.empty.title')}
            </h3>
            <p className="mt-2 max-w-md text-muted-foreground">
                {t('grocery_list.empty.description')}
            </p>
            <Button
                className="mt-6 w-full sm:w-auto"
                onClick={onGenerate}
                disabled={isGenerating}
            >
                {isGenerating ? (
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                    <ShoppingCart className="mr-2 h-4 w-4" />
                )}
                {t('grocery_list.empty.button')}
            </Button>
        </div>
    );
}

function GroceryListSkeleton() {
    const { t } = useTranslation('common');
    return (
        <div className="space-y-6">
            {/* Loading Alert */}
            <Alert className="border-primary/30 bg-primary/5">
                <Loader2 className="h-4 w-4 animate-spin text-primary" />
                <AlertTitle className="text-primary">
                    {t('grocery_list.generating.title')}
                </AlertTitle>
                <AlertDescription className="text-muted-foreground">
                    {t('grocery_list.generating.description')}
                </AlertDescription>
            </Alert>

            {/* Skeleton Cards */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {[1, 2, 3, 4, 5, 6].map((i) => (
                    <Card key={i} className="overflow-hidden">
                        <CardHeader className="pb-3">
                            <div className="flex items-center justify-between">
                                <div className="h-6 w-32 animate-pulse rounded bg-muted" />
                                <div className="h-5 w-12 animate-pulse rounded bg-muted" />
                            </div>
                        </CardHeader>
                        <CardContent className="pt-0">
                            <div className="space-y-3">
                                {[1, 2, 3, 4].map((j) => (
                                    <div
                                        key={j}
                                        className="flex items-center gap-3"
                                    >
                                        <div className="h-4 w-4 animate-pulse rounded bg-muted" />
                                        <div className="h-4 flex-1 animate-pulse rounded bg-muted" />
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </div>
    );
}

interface GroceryListContentProps {
    groceryList: GroceryList;
    onToggleItem: (item: GroceryItem) => void;
    onRegenerate: () => void;
    isRegenerating: boolean;
    viewMode: ViewMode;
    onViewModeChange: (mode: ViewMode) => void;
}

function GroceryListContent({
    groceryList,
    onToggleItem,
    onRegenerate,
    isRegenerating,
    viewMode,
    onViewModeChange,
}: GroceryListContentProps) {
    const { t } = useTranslation('common');
    const progress =
        groceryList.total_items > 0
            ? (groceryList.checked_items / groceryList.total_items) * 100
            : 0;

    const categories = Object.keys(groceryList.items_by_category);
    const days = Object.keys(groceryList.items_by_day || {})
        .map(Number)
        .sort((a, b) => a - b);

    return (
        <>
            {/* Progress */}
            <div className="space-y-3 rounded-xl border bg-card p-4 shadow-sm">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-2">
                        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <ShoppingCart className="h-4 w-4" />
                        </div>
                        <div>
                            <p className="font-medium">
                                {t('grocery_list.progress')}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {groceryList.checked_items}{' '}
                                {t('grocery_list.of')} {groceryList.total_items}{' '}
                                {t('grocery_list.items')}
                            </p>
                        </div>
                    </div>
                    <span className="text-sm font-semibold text-primary tabular-nums">
                        {Math.round(progress)}%
                    </span>
                </div>
                <Progress value={progress} className="h-2.5" />
            </div>

            {/* View Mode Toggle */}
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <ToggleGroup
                    type="single"
                    value={viewMode}
                    onValueChange={(value) =>
                        value && onViewModeChange(value as ViewMode)
                    }
                    variant="outline"
                    size="sm"
                    className="grid w-full grid-cols-1 sm:w-auto sm:grid-cols-2"
                >
                    <ToggleGroupItem
                        value="category"
                        aria-label={t('grocery_list.view_modes.by_category')}
                        className="min-h-11 justify-center"
                    >
                        <LayoutGrid className="h-4 w-4" />
                        {t('grocery_list.view_modes.by_category')}
                    </ToggleGroupItem>
                    <ToggleGroupItem
                        value="day"
                        aria-label={t('grocery_list.view_modes.by_day')}
                        className="min-h-11 justify-center"
                    >
                        <CalendarDays className="h-4 w-4" />
                        {t('grocery_list.view_modes.by_day')}
                    </ToggleGroupItem>
                </ToggleGroup>

                {viewMode === 'day' && days.length > 0 && (
                    <span className="text-sm text-muted-foreground">
                        {days.length === 1
                            ? t('grocery_list.view_modes.days_with_items', {
                                  count: days.length,
                              })
                            : t(
                                  'grocery_list.view_modes.days_with_items_plural',
                                  { count: days.length },
                              )}
                    </span>
                )}
            </div>

            {/* Status Alerts */}
            {groceryList.status === GroceryStatus.Completed && (
                <Alert className="border-green-500/30 bg-green-500/5">
                    <CircleCheck className="h-4 w-4 text-green-600" />
                    <AlertTitle className="text-green-700 dark:text-green-400">
                        {t('grocery_list.status.complete_title')}
                    </AlertTitle>
                    <AlertDescription className="text-green-600 dark:text-green-300">
                        {t('grocery_list.status.complete_description')}
                    </AlertDescription>
                </Alert>
            )}

            {groceryList.status === GroceryStatus.Failed && (
                <Alert className="border-red-500/30 bg-red-500/5">
                    <CircleAlert className="h-4 w-4 text-red-600" />
                    <AlertTitle className="text-red-700 dark:text-red-400">
                        {t('grocery_list.status.failed_title')}
                    </AlertTitle>
                    <AlertDescription className="text-red-600 dark:text-red-300">
                        {t('grocery_list.status.failed_description')}
                    </AlertDescription>
                </Alert>
            )}

            {/* Grocery Items by Category */}
            {viewMode === 'category' && (
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {categories.map((category) => {
                        const items = groceryList.items_by_category[category];
                        const checkedCount = items.filter(
                            (item) => item.is_checked,
                        ).length;

                        return (
                            <Card key={category} className="overflow-hidden">
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center justify-between text-lg">
                                        <GroceryCategoryLabel
                                            category={category}
                                        />
                                        <Badge
                                            variant="secondary"
                                            className="font-normal"
                                        >
                                            {checkedCount}/{items.length}
                                        </Badge>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="pt-0">
                                    <ul className="space-y-2">
                                        {items.map((item) => (
                                            <GroceryItemRow
                                                key={item.id}
                                                item={item}
                                                onToggle={onToggleItem}
                                                showDays
                                            />
                                        ))}
                                    </ul>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            )}

            {/* Grocery Items by Day */}
            {viewMode === 'day' && (
                <div className="space-y-6">
                    {days.map((day) => {
                        const items = groceryList.items_by_day[day] || [];
                        const checkedCount = items.filter(
                            (item) => item.is_checked,
                        ).length;

                        // Group items by category within each day
                        const itemsByCategory = items.reduce<
                            Record<string, GroceryItem[]>
                        >((acc, item) => {
                            if (!acc[item.category]) {
                                acc[item.category] = [];
                            }
                            acc[item.category].push(item);
                            return acc;
                        }, {});

                        const dayCategories = Object.keys(itemsByCategory);

                        return (
                            <div key={day} className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="flex items-center gap-2 text-xl font-semibold">
                                        <CalendarDays className="h-5 w-5 text-primary" />
                                        {t('grocery_list.day', { number: day })}
                                    </h3>
                                    <Badge
                                        variant="outline"
                                        className="font-normal"
                                    >
                                        {checkedCount}/{items.length}{' '}
                                        {t('grocery_list.items')}
                                    </Badge>
                                </div>

                                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    {dayCategories.map((category) => {
                                        const categoryItems =
                                            itemsByCategory[category];
                                        const categoryCheckedCount =
                                            categoryItems.filter(
                                                (item) => item.is_checked,
                                            ).length;

                                        return (
                                            <Card
                                                key={`${day}-${category}`}
                                                className="overflow-hidden"
                                            >
                                                <CardHeader className="pb-3">
                                                    <CardTitle className="flex items-center justify-between text-lg">
                                                        <GroceryCategoryLabel
                                                            category={category}
                                                        />
                                                        <Badge
                                                            variant="secondary"
                                                            className="font-normal"
                                                        >
                                                            {
                                                                categoryCheckedCount
                                                            }
                                                            /
                                                            {
                                                                categoryItems.length
                                                            }
                                                        </Badge>
                                                    </CardTitle>
                                                </CardHeader>
                                                <CardContent className="pt-0">
                                                    <ul className="space-y-2">
                                                        {categoryItems.map(
                                                            (item) => (
                                                                <GroceryItemRow
                                                                    key={`${day}-${item.id}`}
                                                                    item={item}
                                                                    onToggle={
                                                                        onToggleItem
                                                                    }
                                                                    currentDay={
                                                                        day
                                                                    }
                                                                />
                                                            ),
                                                        )}
                                                    </ul>
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Empty State */}
            {categories.length === 0 && (
                <div className="flex flex-col items-center justify-center rounded-xl border border-dashed bg-card p-8 text-center shadow-sm md:p-12">
                    <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <ShoppingCart className="h-6 w-6" />
                    </div>
                    <h3 className="text-lg font-semibold">
                        {t('grocery_list.empty_items.title')}
                    </h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {t('grocery_list.empty_items.description')}
                    </p>
                    <Button
                        variant="outline"
                        className="mt-4 w-full sm:w-auto"
                        onClick={onRegenerate}
                        disabled={isRegenerating}
                    >
                        {isRegenerating ? (
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        ) : (
                            <RefreshCw className="mr-2 h-4 w-4" />
                        )}
                        {t('grocery_list.empty_items.button')}
                    </Button>
                </div>
            )}
        </>
    );
}

interface GroceryCategoryLabelProps {
    category: string;
}

function GroceryCategoryLabel({ category }: GroceryCategoryLabelProps) {
    const config =
        groceryCategoryIcons[category] ??
        groceryCategoryIcons[GroceryCategory.Other];
    const Icon = config.Icon;

    return (
        <span className="flex min-w-0 items-center gap-2">
            <span
                className={cn(
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                    config.className,
                )}
                aria-hidden="true"
            >
                <Icon className="h-4 w-4" />
            </span>
            <span className="truncate">{category}</span>
        </span>
    );
}

interface GroceryItemRowProps {
    item: GroceryItem;
    onToggle: (item: GroceryItem) => void;
    showDays?: boolean;
    currentDay?: number;
}

function GroceryItemRow({
    item,
    onToggle,
    showDays,
    currentDay,
}: GroceryItemRowProps) {
    const days = item.days ?? [];
    const otherDays = currentDay ? days.filter((d) => d !== currentDay) : [];
    const hasOtherDays = otherDays.length > 0;
    const { t } = useTranslation('common');

    const itemId = `item-${item.id}${currentDay ? `-day-${currentDay}` : ''}`;

    return (
        <li className="flex min-h-12 items-start gap-3 rounded-lg px-2 py-2 transition-colors hover:bg-muted/50">
            <Checkbox
                id={itemId}
                checked={item.is_checked}
                className="mt-0.5 size-5 rounded-md"
                onCheckedChange={() => onToggle(item)}
            />
            <label
                htmlFor={itemId}
                className="flex min-h-8 flex-1 cursor-pointer flex-col justify-center gap-1 text-sm"
            >
                <span
                    className={cn(
                        'flex flex-wrap items-baseline gap-x-2 gap-y-1',
                        item.is_checked && 'text-muted-foreground line-through',
                    )}
                >
                    <span className="font-medium">{item.name}</span>
                    <span className="text-muted-foreground">
                        {item.quantity}
                    </span>
                </span>
                {(showDays && days.length > 1) || hasOtherDays ? (
                    <span className="flex flex-wrap gap-1.5">
                        {showDays && days.length > 1 && (
                            <Badge
                                variant="outline"
                                className="h-6 rounded-md px-2 text-xs font-normal"
                            >
                                {t('grocery_list.day', {
                                    number: days.join(', '),
                                })}
                            </Badge>
                        )}
                        {hasOtherDays && (
                            <Badge
                                variant="outline"
                                className="h-6 rounded-md px-2 text-xs font-normal"
                            >
                                {t('grocery_list.also_day', {
                                    days: otherDays.join(', '),
                                })}
                            </Badge>
                        )}
                    </span>
                ) : null}
            </label>
        </li>
    );
}
