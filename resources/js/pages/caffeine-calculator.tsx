import {
    calculate as calculateRoute,
    event as eventRoute,
    search as searchRoute,
    signupCta as signupCtaRoute,
    sleepCutoff as sleepCutoffRoute,
} from '@/actions/App/Http/Controllers/CaffeineCalculatorController';
import { Head, Link, useHttp } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';

interface DrinkOption {
    id: number;
    name: string;
    category: string | null;
    caffeine_mg: number;
    rank: number;
}

interface DrinkResult {
    id: number;
    name: string;
    slug: string;
    caffeine_mg: number;
    source: string | null;
    license_url: string | null;
    attribution: string | null;
}

interface CalculateResult {
    lacks_caffeine_estimate: boolean;
    safe_mg?: number;
    safe_cups?: number;
    per_cup_mg?: number;
    drink?: DrinkResult;
}

interface SleepCutoffResult {
    state: 'past' | 'cutoff' | 'unavailable';
    time?: string;
}

interface Props {
    unit: 'kg' | 'lb';
    hasDrinks: boolean;
    minWeightKg: number;
    maxWeightKg: number;
    registerUrl: string;
    isGuest: boolean;
}

const SENSITIVITY_LABELS: Record<number, string> = {
    1: 'More tolerant',
    2: 'Tolerant',
    3: 'Normal',
    4: 'Sensitive',
    5: 'More sensitive',
};

const SENSITIVITY_MULTIPLIERS: Record<number, number> = {
    1: 0.7,
    2: 0.85,
    3: 1.0,
    4: 1.15,
    5: 1.3,
};

const LB_PER_KG = 2.2046226218;

export default function CaffeineCalculator({
    unit: initialUnit,
    hasDrinks,
    minWeightKg,
    maxWeightKg,
    registerUrl,
    isGuest,
}: Props) {
    const calculateHttp = useHttp<{
        weight: string;
        weight_unit: 'kg' | 'lb';
        sensitivity: number;
        drink_id: number | null;
    }, CalculateResult>(calculateRoute(), {
        weight: '',
        weight_unit: initialUnit,
        sensitivity: 3,
        drink_id: null,
    });

    const searchHttp = useHttp<{ q: string }, { results: DrinkOption[] }>(searchRoute(), {
        q: '',
    });

    const sleepHttp = useHttp<{
        bedtime: string;
        per_cup_mg: number;
        safe_cups: number;
    }, SleepCutoffResult>(sleepCutoffRoute(), {
        bedtime: '',
        per_cup_mg: 0,
        safe_cups: 0,
    });

    const eventHttp = useHttp<{
        event: string;
        properties: Record<string, unknown>;
    }>(eventRoute(), {
        event: '',
        properties: {},
    });

    const [drinkQuery, setDrinkQuery] = useState<string>('');
    const [drinkOptions, setDrinkOptions] = useState<DrinkOption[]>([]);
    const [activeOption, setActiveOption] = useState<number>(0);
    const [open, setOpen] = useState<boolean>(false);
    const [searchTouched, setSearchTouched] = useState<boolean>(false);
    const [result, setResult] = useState<CalculateResult | null>(null);
    const [optimiseForSleep, setOptimiseForSleep] = useState<boolean>(false);
    const [bedtime, setBedtime] = useState<string>('');
    const [sleepCutoff, setSleepCutoff] = useState<SleepCutoffResult | null>(null);

    const drinkContainerRef = useRef<HTMLDivElement | null>(null);
    const stepRefs = useRef<Array<HTMLButtonElement | null>>([]);
    const searchDebounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    function logEvent(event: string, properties: Record<string, unknown> = {}): void {
        eventHttp.clearErrors();
        eventHttp.transform(() => ({ event, properties }));
        void eventHttp.submit({ onError: () => undefined });
    }

    useEffect(() => {
        function handleClick(event: MouseEvent) {
            if (drinkContainerRef.current && !drinkContainerRef.current.contains(event.target as Node)) {
                setOpen(false);
            }
        }
        function handleEscape(event: KeyboardEvent) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClick);
        document.addEventListener('keydown', handleEscape);
        return () => {
            document.removeEventListener('mousedown', handleClick);
            document.removeEventListener('keydown', handleEscape);
        };
    }, []);

    const isWeightOutOfRange = useMemo(() => {
        const numeric = Number(calculateHttp.data.weight);
        if (!calculateHttp.data.weight || Number.isNaN(numeric) || numeric <= 0) {
            return false;
        }
        const kg = calculateHttp.data.weight_unit === 'kg' ? numeric : numeric / LB_PER_KG;
        return kg < minWeightKg || kg > maxWeightKg;
    }, [calculateHttp.data.weight, calculateHttp.data.weight_unit, minWeightKg, maxWeightKg]);

    function setUnit(unit: 'kg' | 'lb') {
        if (calculateHttp.data.weight_unit === unit) {
            return;
        }
        calculateHttp.setData('weight_unit', unit);
        logEvent('unit_toggled', { unit });
    }

    function changeSensitivity(step: number) {
        if (step < 1 || step > 5 || step === calculateHttp.data.sensitivity) {
            return;
        }
        calculateHttp.setData('sensitivity', step);
        logEvent('sensitivity_changed', { sensitivity_step: step });
    }

    function onWeightBlur() {
        const raw = calculateHttp.data.weight;
        const numeric = Number(raw);
        if (raw === '' || Number.isNaN(numeric) || numeric <= 0) {
            return;
        }
        logEvent('weight_entered', { weight: raw, unit: calculateHttp.data.weight_unit });
    }

    function runDrinkSearch(value: string) {
        setDrinkQuery(value);
        setActiveOption(0);
        setOpen(true);

        const trimmed = value.trim().toLowerCase();
        if (searchDebounceRef.current) {
            clearTimeout(searchDebounceRef.current);
            searchDebounceRef.current = null;
        }

        if (trimmed === '') {
            setDrinkOptions([]);
            setSearchTouched(false);
            return;
        }

        searchDebounceRef.current = setTimeout(() => {
            searchHttp.setData('q', trimmed);
            void searchHttp.submit({
                onSuccess: (response) => {
                    setDrinkOptions(response?.results ?? []);
                    setSearchTouched(true);
                },
                onError: () => {
                    setDrinkOptions([]);
                    setSearchTouched(true);
                },
            });
        }, 150);
    }

    function selectDrink(option: DrinkOption) {
        calculateHttp.setData('drink_id', option.id);
        setDrinkQuery(option.name);
        setOpen(false);

        logEvent('drink_picked', { drink_id: option.id });
        logEvent('search_result_selected', {
            drink_id: option.id,
            rank: option.rank,
            query_length: option.name.toLowerCase().length,
        });
    }

    function calculate() {
        if (!hasDrinks || calculateHttp.data.drink_id === null) {
            return;
        }
        void calculateHttp.submit({
            onSuccess: (response) => setResult(response),
        });
    }

    useEffect(() => {
        if (!optimiseForSleep || result === null || result.lacks_caffeine_estimate) {
            setSleepCutoff(null);
            return;
        }
        if (bedtime === '' || !/^([01]\d|2[0-3]):[0-5]\d$/.test(bedtime)) {
            setSleepCutoff(null);
            return;
        }
        if (result.per_cup_mg === undefined || result.safe_cups === undefined) {
            setSleepCutoff(null);
            return;
        }

        const perCup = result.per_cup_mg;
        const cups = result.safe_cups;

        sleepHttp.transform(() => ({
            bedtime,
            per_cup_mg: perCup,
            safe_cups: cups,
        }));
        void sleepHttp.submit({
            onSuccess: (response) => setSleepCutoff(response),
        });
    }, [optimiseForSleep, bedtime, result]);

    function toggleOptimiseForSleep() {
        const next = !optimiseForSleep;
        setOptimiseForSleep(next);
        if (!next) {
            setBedtime('');
            setSleepCutoff(null);
            return;
        }
        logEvent('sleep_disclosure_opened');
    }

    const hasResult = result !== null && !result.lacks_caffeine_estimate
        && result.safe_mg !== undefined && result.safe_cups !== undefined && result.per_cup_mg !== undefined;

    const lacksEstimate = result !== null && result.lacks_caffeine_estimate;

    const safeMgRounded = hasResult ? Math.round(result!.safe_mg!) : 0;
    const perCupRounded = hasResult ? Math.round(result!.per_cup_mg!) : 0;
    const breakdownTotal = hasResult ? Math.round(result!.per_cup_mg! * result!.safe_cups!) : 0;
    const currentMultiplier = SENSITIVITY_MULTIPLIERS[calculateHttp.data.sensitivity] ?? 1.0;

    const showNoResults = drinkQuery !== '' && drinkOptions.length === 0 && searchTouched;
    const showListbox = drinkQuery !== '' && drinkOptions.length > 0;

    const weightError = calculateHttp.errors.weight;

    return (
        <div className="min-h-screen w-full bg-gray-50 dark:bg-slate-900">
            <Head title="Coffee Caffeine Calculator: How Much Is Too Much?">
                <meta
                    head-key="description"
                    name="description"
                    content="Free caffeine calculator: estimate your safe daily caffeine dose and find out when to stop drinking coffee for better sleep."
                />
                <meta
                    head-key="keywords"
                    name="keywords"
                    content="caffeine calculator, safe caffeine dose, caffeine sleep cutoff, coffee calculator, caffeine half life"
                />
            </Head>
            <style>{`
                @keyframes caffeine-result-in {
                    from { opacity: 0; transform: translateY(8px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                [data-caffeine-result-enter] {
                    animation: caffeine-result-in 200ms ease-out both;
                }
                @media (prefers-reduced-motion: reduce) {
                    [data-caffeine-result-enter] {
                        animation: none;
                    }
                }
            `}</style>
            <div className="mx-auto max-w-2xl px-4 py-12">
                <h1 className="text-[32px] font-bold leading-tight tracking-tight text-gray-900 md:text-5xl dark:text-slate-50">
                    Coffee Caffeine Calculator: How Much Is Too Much?
                </h1>
                <p className="mt-4 text-lg text-gray-600 dark:text-slate-400">
                    Choose your drink, tell us about you, and find your safe daily limit.
                </p>

                <div
                    data-testid="caffeine-form-card"
                    className="mt-8 rounded-xl border border-gray-200 bg-white p-6 md:p-8 dark:border-slate-700 dark:bg-slate-800"
                >
                    <div data-testid="caffeine-form-rows" className="space-y-6">
                        <div data-testid="caffeine-form-row-weight">
                            <div className="flex items-center justify-between gap-4">
                                <label htmlFor="caffeine-weight" className="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                    Your weight
                                </label>
                                <div
                                    data-testid="caffeine-weight-unit-toggle"
                                    role="group"
                                    aria-label="Weight unit"
                                    className="inline-flex gap-2"
                                >
                                    <button
                                        type="button"
                                        onClick={() => setUnit('kg')}
                                        data-testid="caffeine-weight-unit-kg"
                                        aria-pressed={calculateHttp.data.weight_unit === 'kg'}
                                        className={
                                            'rounded-full border px-3 py-1 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-500/30 ' +
                                            (calculateHttp.data.weight_unit === 'kg'
                                                ? 'border-emerald-600 bg-emerald-600 text-white dark:hover:bg-emerald-400'
                                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700')
                                        }
                                    >
                                        Kilos
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setUnit('lb')}
                                        data-testid="caffeine-weight-unit-lb"
                                        aria-pressed={calculateHttp.data.weight_unit === 'lb'}
                                        className={
                                            'rounded-full border px-3 py-1 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-500/30 ' +
                                            (calculateHttp.data.weight_unit === 'lb'
                                                ? 'border-emerald-600 bg-emerald-600 text-white dark:hover:bg-emerald-400'
                                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700')
                                        }
                                    >
                                        Pounds
                                    </button>
                                </div>
                            </div>
                            <input
                                type="number"
                                id="caffeine-weight"
                                value={calculateHttp.data.weight}
                                onChange={(e) => calculateHttp.setData('weight', e.target.value)}
                                onBlur={onWeightBlur}
                                inputMode="decimal"
                                min={0}
                                step={0.1}
                                placeholder="e.g. 70"
                                aria-describedby="caffeine-weight-error"
                                className={
                                    'mt-1 block w-full rounded-md border bg-white px-3.5 py-2.5 text-base text-gray-900 placeholder-gray-400 outline-none focus:ring-2 dark:bg-slate-900 dark:text-slate-50 dark:placeholder-slate-500 ' +
                                    (weightError
                                        ? 'border-red-600 focus:border-red-600 focus:ring-red-600/15 dark:border-red-500'
                                        : 'border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/15 dark:border-slate-700')
                                }
                            />
                            {weightError ? (
                                <p
                                    id="caffeine-weight-error"
                                    data-testid="caffeine-weight-error"
                                    className="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {weightError}
                                </p>
                            ) : isWeightOutOfRange ? (
                                <p
                                    data-testid="caffeine-weight-clamp-notice"
                                    className="mt-1 text-sm text-amber-700 dark:text-amber-400"
                                >
                                    That&apos;s outside our documented range. Calculations are clamped to typical adult weights of 30&ndash;250 kg (66&ndash;551 lb).
                                </p>
                            ) : (
                                <p
                                    data-testid="caffeine-weight-typical-adult-note"
                                    className="mt-1 text-xs text-gray-500 dark:text-slate-400"
                                >
                                    Calibrated for typical adult weights of 30&ndash;250 kg (66&ndash;551 lb).
                                </p>
                            )}
                        </div>

                        <div data-testid="caffeine-form-row-drink">
                            <label htmlFor="caffeine-drink" className="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                Choose a coffee
                            </label>
                            {!hasDrinks ? (
                                <p
                                    data-testid="caffeine-drink-empty-state"
                                    className="mt-1 text-sm text-gray-500 dark:text-slate-400"
                                >
                                    We&apos;re refreshing our drinks list&mdash;please check back soon.
                                </p>
                            ) : (
                                <div ref={drinkContainerRef} className="relative mt-1">
                                    <input
                                        type="text"
                                        id="caffeine-drink"
                                        data-testid="caffeine-drink-input"
                                        value={drinkQuery}
                                        onChange={(e) => runDrinkSearch(e.target.value)}
                                        onFocus={() => setOpen(true)}
                                        onKeyDown={(e) => {
                                            if (e.key === 'ArrowDown') {
                                                e.preventDefault();
                                                setOpen(true);
                                                if (drinkOptions.length > 0) {
                                                    setActiveOption((prev) => (prev + 1) % drinkOptions.length);
                                                }
                                            } else if (e.key === 'ArrowUp') {
                                                e.preventDefault();
                                                setOpen(true);
                                                if (drinkOptions.length > 0) {
                                                    setActiveOption((prev) => (prev - 1 + drinkOptions.length) % drinkOptions.length);
                                                }
                                            } else if (e.key === 'Enter') {
                                                e.preventDefault();
                                                if (drinkOptions[activeOption]) {
                                                    selectDrink(drinkOptions[activeOption]);
                                                }
                                            } else if (e.key === 'Escape') {
                                                e.preventDefault();
                                                setOpen(false);
                                            }
                                        }}
                                        role="combobox"
                                        autoComplete="off"
                                        aria-autocomplete="list"
                                        aria-controls="caffeine-drink-listbox"
                                        aria-expanded={open && drinkOptions.length > 0}
                                        aria-activedescendant={open ? `caffeine-drink-option-${activeOption}` : undefined}
                                        placeholder="eg. Americano"
                                        className="block w-full rounded-md border border-gray-200 bg-white px-3.5 py-2.5 text-base text-gray-900 placeholder-gray-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-50 dark:placeholder-slate-500"
                                    />

                                    {showListbox && open && (
                                        <ul
                                            id="caffeine-drink-listbox"
                                            data-testid="caffeine-drink-listbox"
                                            role="listbox"
                                            className="absolute left-0 right-0 z-10 mt-1 max-h-64 overflow-y-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800"
                                        >
                                            {drinkOptions.map((option, index) => (
                                                <li
                                                    key={`caffeine-drink-option-${option.id}`}
                                                    id={`caffeine-drink-option-${index}`}
                                                    data-testid={`caffeine-drink-option-${option.id}`}
                                                    role="option"
                                                    aria-selected={activeOption === index}
                                                    onMouseEnter={() => setActiveOption(index)}
                                                    onMouseDown={(e) => e.preventDefault()}
                                                    onClick={() => selectDrink(option)}
                                                    className={
                                                        activeOption === index
                                                            ? 'cursor-pointer px-3 py-2 text-sm bg-emerald-50 text-emerald-900 dark:bg-slate-700 dark:text-slate-50'
                                                            : 'cursor-pointer px-3 py-2 text-sm text-gray-900 dark:text-slate-100'
                                                    }
                                                >
                                                    <span className="font-medium">{option.name}</span>
                                                    <span className="ml-2 text-xs text-gray-500 dark:text-slate-400">
                                                        {Math.round(option.caffeine_mg)} mg
                                                    </span>
                                                </li>
                                            ))}
                                        </ul>
                                    )}

                                    {showNoResults && open && (
                                        <div
                                            data-testid="caffeine-drink-no-results"
                                            className="absolute left-0 right-0 z-10 mt-1 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 shadow-lg dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                                        >
                                            No drinks found — try a different term.
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        <div data-testid="caffeine-form-row-sensitivity">
                            <span className="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                Caffeine sensitivity
                            </span>
                            <div
                                data-testid="caffeine-sensitivity-rail"
                                role="radiogroup"
                                aria-label="Caffeine sensitivity"
                                className="relative mt-3"
                            >
                                <div
                                    aria-hidden="true"
                                    className="absolute left-0 right-0 top-1/2 h-0.5 -translate-y-1/2 bg-gray-200 dark:bg-slate-700"
                                />
                                <div className="relative flex items-center justify-between">
                                    {[1, 2, 3, 4, 5].map((step) => (
                                        <button
                                            key={`caffeine-sensitivity-step-${step}`}
                                            ref={(el) => {
                                                stepRefs.current[step - 1] = el;
                                            }}
                                            type="button"
                                            role="radio"
                                            onClick={() => changeSensitivity(step)}
                                            data-testid={`caffeine-sensitivity-step-${step}`}
                                            aria-checked={calculateHttp.data.sensitivity === step}
                                            aria-label={`Sensitivity step ${step} of 5: ${SENSITIVITY_LABELS[step]}`}
                                            tabIndex={calculateHttp.data.sensitivity === step ? 0 : -1}
                                            onKeyDown={(e) => {
                                                if (e.key === 'ArrowRight' && step < 5) {
                                                    e.preventDefault();
                                                    stepRefs.current[step]?.focus();
                                                    changeSensitivity(step + 1);
                                                } else if (e.key === 'ArrowLeft' && step > 1) {
                                                    e.preventDefault();
                                                    stepRefs.current[step - 2]?.focus();
                                                    changeSensitivity(step - 1);
                                                }
                                            }}
                                            className={
                                                'h-7 w-7 rounded-full border transition focus:outline-none focus:ring-2 focus:ring-emerald-500/30 ' +
                                                (calculateHttp.data.sensitivity === step
                                                    ? 'border-emerald-600 bg-emerald-600 ring-2 ring-inset ring-white dark:ring-slate-800'
                                                    : 'border-gray-300 bg-white hover:border-gray-400 dark:border-slate-600 dark:bg-slate-800 dark:hover:border-slate-500')
                                            }
                                        />
                                    ))}
                                </div>
                            </div>
                            <div className="mt-2 flex items-center justify-between text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                <span>More tolerant</span>
                                <span>Normal</span>
                                <span>More sensitive</span>
                            </div>
                            <div
                                data-testid="caffeine-sensitivity-announcement"
                                className="sr-only"
                                aria-live="polite"
                                aria-atomic="true"
                            >
                                Sensitivity: {SENSITIVITY_LABELS[calculateHttp.data.sensitivity]}, step {calculateHttp.data.sensitivity} of 5
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={calculate}
                        data-testid="caffeine-cta-calculate"
                        disabled={!hasDrinks || calculateHttp.processing}
                        aria-disabled={!hasDrinks || calculateHttp.processing}
                        className="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-emerald-500 px-6 py-3 text-base font-semibold text-white transition duration-150 hover:-translate-y-px hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 active:translate-y-0 active:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:bg-emerald-500 sm:w-auto dark:hover:bg-emerald-400 dark:focus:ring-offset-slate-900"
                    >
                        How Much Coffee?
                    </button>
                </div>

                {lacksEstimate && (
                    <div
                        data-testid="caffeine-result-fallback"
                        data-caffeine-result-enter=""
                        role="status"
                        aria-live="polite"
                        className="mt-6 rounded-xl border border-amber-200 border-t-4 border-t-amber-500 bg-amber-50 p-6 md:p-8 dark:border-amber-900/50 dark:border-t-amber-500 dark:bg-amber-950/30"
                    >
                        <p
                            data-testid="caffeine-result-fallback-headline"
                            className="text-lg font-semibold text-amber-900 dark:text-amber-200"
                        >
                            We don&apos;t have a confident estimate for this drink yet.
                        </p>
                        <p
                            data-testid="caffeine-result-fallback-suggestion"
                            className="mt-2 text-sm text-amber-800 dark:text-amber-300"
                        >
                            Try picking another drink from the list above so we can calculate your safe daily limit.
                        </p>
                    </div>
                )}

                {hasResult && (
                    <>
                        <div
                            data-testid="caffeine-result-panel"
                            data-caffeine-result-enter=""
                            role="status"
                            aria-live="polite"
                            className="mt-6 rounded-xl border border-gray-200 border-t-4 border-t-emerald-500 bg-white p-6 md:p-8 dark:border-slate-700 dark:border-t-emerald-500 dark:bg-slate-800"
                        >
                            <p
                                data-testid="caffeine-result-cups"
                                className="text-5xl font-bold leading-none tracking-tight text-gray-900 tabular-nums md:text-6xl dark:text-slate-50"
                            >
                                ≈ {result!.safe_cups} {result!.safe_cups === 1 ? 'cup' : 'cups'}
                            </p>
                            <p
                                data-testid="caffeine-result-safe-mg"
                                className="mt-3 text-lg text-gray-700 dark:text-slate-300"
                            >
                                Your safe daily limit is about <span className="font-semibold tabular-nums">{safeMgRounded} mg</span> of caffeine.
                            </p>
                            <p
                                data-testid="caffeine-result-breakdown"
                                className="mt-2 text-sm text-gray-500 dark:text-slate-400"
                            >
                                ≈ <span className="tabular-nums">{perCupRounded}</span> mg per cup × <span className="tabular-nums">{result!.safe_cups}</span> {result!.safe_cups === 1 ? 'cup' : 'cups'} ≈ <span className="tabular-nums">{breakdownTotal}</span> mg
                            </p>
                        </div>

                        {isGuest && (
                            <div
                                data-testid="caffeine-signup-cta"
                                className="mt-4 rounded-xl border border-gray-200 bg-white p-6 md:p-8 dark:border-slate-700 dark:bg-slate-800"
                            >
                                <p
                                    data-testid="caffeine-signup-cta-headline"
                                    className="text-lg font-semibold text-gray-900 dark:text-slate-50"
                                >
                                    Save your results and track your daily caffeine.
                                </p>
                                <p className="mt-2 text-sm text-gray-600 dark:text-slate-400">
                                    Create a free account to log drinks, watch your sleep cutoff, and see how your habits change over time.
                                </p>
                                <Link
                                    href={signupCtaRoute()}
                                    as="button"
                                    data-testid="caffeine-signup-cta-button"
                                    data-register-url={registerUrl}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-emerald-500 px-6 py-3 text-base font-semibold text-white transition duration-150 hover:-translate-y-px hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 active:translate-y-0 active:bg-emerald-700 sm:w-auto dark:hover:bg-emerald-400 dark:focus:ring-offset-slate-900"
                                >
                                    Create a free account
                                </Link>
                            </div>
                        )}

                        <details
                            data-testid="caffeine-how-calculated"
                            className="mt-4 rounded-xl border border-gray-200 bg-white p-4 md:p-6 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <summary
                                data-testid="caffeine-how-calculated-summary"
                                className="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:text-slate-200 dark:hover:text-slate-50"
                            >
                                How we calculated this
                            </summary>
                            <div className="mt-3 space-y-3 text-sm text-gray-700 dark:text-slate-300">
                                <p data-testid="caffeine-how-calculated-formula">
                                    <span className="font-semibold">Formula:</span>{' '}
                                    safe daily mg = weight (kg) × 5.7 mg/kg × sensitivity multiplier.
                                    With your inputs: <span className="tabular-nums">{calculateHttp.data.weight}</span> {calculateHttp.data.weight_unit} × 5.7 × <span className="tabular-nums">{currentMultiplier}</span> ≈ <span className="tabular-nums">{safeMgRounded}</span> mg.
                                </p>
                                <p data-testid="caffeine-how-calculated-sensitivity">
                                    Your sensitivity multiplier (<span className="tabular-nums">{currentMultiplier}</span>) is based on your self-reported caffeine sensitivity step.
                                </p>
                                <p data-testid="caffeine-how-calculated-fda">
                                    <span className="font-semibold">FDA reference:</span>{' '}
                                    the U.S. Food &amp; Drug Administration considers up to 400 mg/day of caffeine generally safe for healthy adults.{' '}
                                    <a
                                        href="https://www.fda.gov/consumers/consumer-updates/spilling-beans-how-much-caffeine-too-much"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-emerald-600 underline hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                                    >
                                        FDA: Spilling the Beans &mdash; How Much Caffeine is Too Much?
                                    </a>
                                </p>
                                {result?.drink && (
                                    <p data-testid="caffeine-how-calculated-drink-citation">
                                        <span className="font-semibold">Per-drink caffeine ({result.drink.name}, {Math.round(result.drink.caffeine_mg)} mg):</span>{' '}
                                        sourced from {result.drink.source ?? 'public-domain nutrition data'}{result.drink.attribution ? `, ${result.drink.attribution}` : ''}.
                                        {result.drink.license_url && (
                                            <>
                                                {' '}
                                                <a
                                                    href={result.drink.license_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="text-emerald-600 underline hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                                                >
                                                    View source
                                                </a>
                                            </>
                                        )}
                                    </p>
                                )}
                            </div>
                        </details>

                        <div data-testid="caffeine-optimise-sleep" className="mt-4">
                            <button
                                type="button"
                                onClick={toggleOptimiseForSleep}
                                data-testid="caffeine-optimise-sleep-toggle"
                                aria-expanded={optimiseForSleep}
                                aria-controls="caffeine-bedtime-panel"
                                className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                Also, when should I stop drinking?
                            </button>
                            {optimiseForSleep && (
                                <div
                                    id="caffeine-bedtime-panel"
                                    data-testid="caffeine-bedtime-panel"
                                    className="mt-4 rounded-xl border border-gray-200 bg-white p-4 md:p-6 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <label
                                        htmlFor="caffeine-bedtime"
                                        className="block text-sm font-medium text-gray-700 dark:text-slate-200"
                                    >
                                        What time do you go to bed?
                                    </label>
                                    <input
                                        type="time"
                                        id="caffeine-bedtime"
                                        data-testid="caffeine-bedtime-input"
                                        value={bedtime}
                                        onChange={(e) => setBedtime(e.target.value)}
                                        className="mt-1 block w-full rounded-md border border-gray-200 bg-white px-3.5 py-2.5 text-base text-gray-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-50"
                                    />
                                    {sleepCutoff?.state === 'past' && (
                                        <p
                                            data-testid="caffeine-sleep-cutoff-empty"
                                            className="mt-3 text-sm text-amber-700 dark:text-amber-400"
                                        >
                                            Pick tonight&apos;s bedtime.
                                        </p>
                                    )}
                                    {sleepCutoff?.state === 'cutoff' && sleepCutoff.time && (
                                        <p
                                            data-testid="caffeine-sleep-cutoff"
                                            className="mt-3 text-sm text-gray-700 dark:text-slate-300"
                                        >
                                            Stop drinking after ≈ <span className="font-semibold tabular-nums">{sleepCutoff.time}</span> to be below 50mg at bedtime.
                                        </p>
                                    )}
                                </div>
                            )}
                        </div>

                        <p
                            data-testid="caffeine-disclaimer"
                            className="mt-4 text-xs text-gray-500 dark:text-slate-400"
                        >
                            Estimates from public sources. Talk to a clinician for medical caffeine guidance.
                        </p>
                    </>
                )}
            </div>
        </div>
    );
}
