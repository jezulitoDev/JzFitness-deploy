import { Head, router, useForm, useHttp } from '@inertiajs/react';
import { addDays, format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import {
    Beef,
    ChevronLeft,
    ChevronRight,
    Droplet,
    Flame,
    Pencil,
    Plus,
    Search,
    Trash2,
    Wheat,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { search as searchFoods } from '@/routes/foods';
import {
    destroy as destroyEntry,
    index,
    store as storeEntry,
    update as updateEntry,
} from '@/routes/nutrition';
import type {
    CalorieTarget,
    CatalogFood,
    FoodLogEntry,
    NutritionMeal,
    NutritionTotals,
    NutritionWeekDay,
    SelectOption,
} from '@/types/fitness';

function FoodCatalogSearch({
    categories,
    onSelect,
}: {
    categories: SelectOption[];
    onSelect: (food: CatalogFood) => void;
}) {
    const [query, setQuery] = useState('');
    const [category, setCategory] = useState('all');
    const [results, setResults] = useState<CatalogFood[]>([]);
    const [hasSearched, setHasSearched] = useState(false);
    const { get, processing } = useHttp<
        Record<string, never>,
        { foods: CatalogFood[] }
    >({});

    useEffect(() => {
        if (query.trim().length < 2 && category === 'all') {
            setResults([]);
            setHasSearched(false);

            return;
        }

        const timeout = setTimeout(() => {
            get(
                searchFoods.url({
                    query: {
                        ...(query.trim() ? { q: query.trim() } : {}),
                        ...(category !== 'all' ? { category } : {}),
                    },
                }),
                {
                    onSuccess: (data) => {
                        setResults(data.foods);
                        setHasSearched(true);
                    },
                },
            );
        }, 250);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [query, category]);

    return (
        <div className="grid gap-2 rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
            <Label htmlFor="food-search">Buscar en el catálogo</Label>
            <div className="flex gap-2">
                <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="food-search"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Pollo, arroz, manzana..."
                        className="pl-9"
                    />
                </div>
                <Select value={category} onValueChange={setCategory}>
                    <SelectTrigger className="w-36 shrink-0">
                        <SelectValue placeholder="Categoría" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas</SelectItem>
                        {categories.map((option) => (
                            <SelectItem key={option.value} value={option.value}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            {results.length > 0 && (
                <ul className="max-h-44 divide-y divide-border overflow-y-auto rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                    {results.map((food) => (
                        <li key={food.id}>
                            <button
                                type="button"
                                onClick={() => onSelect(food)}
                                className="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-muted"
                            >
                                <span className="min-w-0">
                                    <span className="block truncate font-medium">
                                        {food.name}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        {food.category_label}
                                    </span>
                                </span>
                                <span className="shrink-0 text-xs text-muted-foreground">
                                    {food.calories_per_100g} kcal/100 g
                                </span>
                            </button>
                        </li>
                    ))}
                </ul>
            )}
            {processing && (
                <p className="text-xs text-muted-foreground">Buscando...</p>
            )}
            {!processing && hasSearched && results.length === 0 && (
                <p className="text-xs text-muted-foreground">
                    Sin resultados. Puedes rellenar la entrada manualmente.
                </p>
            )}
        </div>
    );
}

function EntryDialog({
    date,
    mealTypes,
    foodCategories,
    entry,
    defaultMealType,
    onClose,
}: {
    date: string;
    mealTypes: SelectOption[];
    foodCategories: SelectOption[];
    entry: FoodLogEntry | null;
    defaultMealType: string;
    onClose: () => void;
}) {
    const [selectedFood, setSelectedFood] = useState<CatalogFood | null>(null);
    const [grams, setGrams] = useState('100');
    const { data, setData, post, patch, processing, errors } = useForm({
        consumed_on: date,
        meal_type: entry?.meal_type ?? defaultMealType,
        name: entry?.name ?? '',
        quantity: entry?.quantity ?? '',
        calories: entry?.calories?.toString() ?? '',
        protein_g: entry?.protein_g?.toString() ?? '',
        carbs_g: entry?.carbs_g?.toString() ?? '',
        fat_g: entry?.fat_g?.toString() ?? '',
    });

    const applyFood = (food: CatalogFood, gramsValue: string) => {
        const amount = parseFloat(gramsValue);

        if (Number.isNaN(amount) || amount <= 0) {
            return;
        }

        const factor = amount / 100;

        setData((previous) => ({
            ...previous,
            name: food.name,
            quantity: `${amount} g`,
            calories: String(Math.round(food.calories_per_100g * factor)),
            protein_g: (food.protein_per_100g * factor).toFixed(1),
            carbs_g: (food.carbs_per_100g * factor).toFixed(1),
            fat_g: (food.fat_per_100g * factor).toFixed(1),
        }));
    };

    const selectFood = (food: CatalogFood) => {
        const defaultGrams = String(food.serving_size_g ?? 100);

        setSelectedFood(food);
        setGrams(defaultGrams);
        applyFood(food, defaultGrams);
    };

    const changeGrams = (value: string) => {
        setGrams(value);

        if (selectedFood) {
            applyFood(selectedFood, value);
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => onClose(),
        };

        if (entry) {
            patch(updateEntry(entry.id).url, options);
        } else {
            post(storeEntry().url, options);
        }
    };

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {entry ? 'Editar alimento' : 'Añadir alimento'}
                    </DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="flex flex-col gap-4">
                    {!selectedFood && (
                        <FoodCatalogSearch
                            categories={foodCategories}
                            onSelect={selectFood}
                        />
                    )}
                    {selectedFood && (
                        <div className="flex items-center gap-2 rounded-lg border border-sidebar-border/70 bg-muted/40 p-3 dark:border-sidebar-border">
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium">
                                    {selectedFood.name}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {selectedFood.calories_per_100g} kcal · P{' '}
                                    {selectedFood.protein_per_100g} g · C{' '}
                                    {selectedFood.carbs_per_100g} g · G{' '}
                                    {selectedFood.fat_per_100g} g por 100 g
                                    {selectedFood.serving_label &&
                                        selectedFood.serving_size_g !== null &&
                                        ` · ${selectedFood.serving_label} ≈ ${selectedFood.serving_size_g} g`}
                                </p>
                            </div>
                            <Input
                                type="number"
                                min={1}
                                step="any"
                                value={grams}
                                onChange={(e) => changeGrams(e.target.value)}
                                className="w-20 shrink-0"
                                aria-label="Cantidad en gramos"
                            />
                            <span className="text-sm text-muted-foreground">
                                g
                            </span>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() => setSelectedFood(null)}
                                aria-label="Quitar alimento seleccionado"
                            >
                                <X className="size-4" />
                            </Button>
                        </div>
                    )}
                    <div className="grid gap-2">
                        <Label>Comida</Label>
                        <Select
                            value={data.meal_type}
                            onValueChange={(value) =>
                                setData('meal_type', value)
                            }
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {mealTypes.map((type) => (
                                    <SelectItem
                                        key={type.value}
                                        value={type.value}
                                    >
                                        {type.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="food-name">Alimento</Label>
                        <Input
                            id="food-name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Pollo a la plancha"
                            required
                        />
                        {errors.name && (
                            <p className="text-sm text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div className="grid gap-2">
                            <Label htmlFor="food-quantity">Cantidad</Label>
                            <Input
                                id="food-quantity"
                                value={data.quantity}
                                onChange={(e) =>
                                    setData('quantity', e.target.value)
                                }
                                placeholder="150 g"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="food-calories">Calorías</Label>
                            <Input
                                id="food-calories"
                                type="number"
                                min={0}
                                max={10000}
                                value={data.calories}
                                onChange={(e) =>
                                    setData('calories', e.target.value)
                                }
                                required
                            />
                            {errors.calories && (
                                <p className="text-sm text-destructive">
                                    {errors.calories}
                                </p>
                            )}
                        </div>
                    </div>
                    <div className="grid grid-cols-3 gap-3">
                        <div className="grid gap-2">
                            <Label htmlFor="food-protein">Proteína (g)</Label>
                            <Input
                                id="food-protein"
                                type="number"
                                step="0.1"
                                min={0}
                                value={data.protein_g}
                                onChange={(e) =>
                                    setData('protein_g', e.target.value)
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="food-carbs">Carbohidr. (g)</Label>
                            <Input
                                id="food-carbs"
                                type="number"
                                step="0.1"
                                min={0}
                                value={data.carbs_g}
                                onChange={(e) =>
                                    setData('carbs_g', e.target.value)
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="food-fat">Grasas (g)</Label>
                            <Input
                                id="food-fat"
                                type="number"
                                step="0.1"
                                min={0}
                                value={data.fat_g}
                                onChange={(e) =>
                                    setData('fat_g', e.target.value)
                                }
                            />
                        </div>
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {entry ? 'Guardar' : 'Añadir'}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function MacroStat({
    icon: Icon,
    label,
    value,
}: {
    icon: typeof Beef;
    label: string;
    value: string;
}) {
    return (
        <div className="flex items-center gap-2">
            <Icon className="size-4 text-muted-foreground" />
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="ml-auto text-sm font-medium">{value}</span>
        </div>
    );
}

export default function NutritionIndex({
    date,
    meals,
    totals,
    calorieTarget,
    week,
    mealTypes,
    foodCategories,
}: {
    date: string;
    meals: NutritionMeal[];
    totals: NutritionTotals;
    calorieTarget: CalorieTarget;
    week: NutritionWeekDay[];
    mealTypes: SelectOption[];
    foodCategories: SelectOption[];
    hasFitnessProfile: boolean;
}) {
    const [dialog, setDialog] = useState<{
        entry: FoodLogEntry | null;
        mealType: string;
    } | null>(null);

    const goTo = (target: string) => {
        router.get(index().url, { date: target }, { preserveState: true });
    };

    const percentage =
        calorieTarget.target > 0
            ? Math.min(
                  Math.round((totals.calories / calorieTarget.target) * 100),
                  100,
              )
            : 0;
    const remaining = calorieTarget.target - totals.calories;
    const maxWeekCalories = Math.max(
        ...week.map((d) => d.calories),
        calorieTarget.target,
        1,
    );
    const dayLabel = format(parseISO(date), "EEEE d 'de' MMMM", {
        locale: es,
    });
    const isToday = date === format(new Date(), 'yyyy-MM-dd');

    return (
        <>
            <Head title="Nutrición" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-2xl font-semibold capitalize">
                        {dayLabel}
                    </h1>
                    <div className="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                goTo(
                                    format(
                                        addDays(parseISO(date), -1),
                                        'yyyy-MM-dd',
                                    ),
                                )
                            }
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Input
                            type="date"
                            value={date}
                            onChange={(e) => goTo(e.target.value)}
                            className="w-40"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                goTo(
                                    format(
                                        addDays(parseISO(date), 1),
                                        'yyyy-MM-dd',
                                    ),
                                )
                            }
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                        {!isToday && (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() =>
                                    goTo(format(new Date(), 'yyyy-MM-dd'))
                                }
                            >
                                Hoy
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <div className="rounded-xl border border-sidebar-border/70 p-6 lg:col-span-2 dark:border-sidebar-border">
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-muted-foreground">
                                Calorías de hoy
                            </span>
                            <Flame className="size-5 text-muted-foreground" />
                        </div>
                        <p className="mt-2 text-3xl font-semibold">
                            {totals.calories.toLocaleString()}{' '}
                            <span className="text-base font-normal text-muted-foreground">
                                / {calorieTarget.target.toLocaleString()} kcal
                            </span>
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {remaining >= 0
                                ? `Te quedan ${remaining.toLocaleString()} kcal para tu objetivo`
                                : `Has superado tu objetivo en ${Math.abs(remaining).toLocaleString()} kcal`}
                            {calorieTarget.goal_label &&
                                ` · objetivo: ${calorieTarget.goal_label.toLowerCase()}`}
                        </p>
                        <div className="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                className={`h-full rounded-full transition-all ${
                                    remaining >= 0
                                        ? 'bg-primary'
                                        : 'bg-destructive'
                                }`}
                                style={{ width: `${percentage}%` }}
                            />
                        </div>
                        <div className="mt-4 grid gap-2 sm:grid-cols-3">
                            <MacroStat
                                icon={Beef}
                                label="Proteína"
                                value={`${totals.protein_g} g`}
                            />
                            <MacroStat
                                icon={Wheat}
                                label="Carbohidratos"
                                value={`${totals.carbs_g} g`}
                            />
                            <MacroStat
                                icon={Droplet}
                                label="Grasas"
                                value={`${totals.fat_g} g`}
                            />
                        </div>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <span className="text-sm text-muted-foreground">
                            Resumen semanal
                        </span>
                        <div className="mt-4 flex h-28 items-end gap-1.5">
                            {week.map((day) => (
                                <button
                                    type="button"
                                    key={day.date}
                                    title={`${format(parseISO(day.date), 'EEEE d', { locale: es })}: ${day.calories} kcal`}
                                    onClick={() => goTo(day.date)}
                                    className="group flex h-full flex-1 flex-col items-center justify-end gap-1"
                                >
                                    <div
                                        className={`w-full rounded-t transition-colors ${
                                            day.date === date
                                                ? 'bg-primary'
                                                : 'bg-muted-foreground/30 group-hover:bg-muted-foreground/50'
                                        }`}
                                        style={{
                                            height: `${Math.max((day.calories / maxWeekCalories) * 100, 2)}%`,
                                        }}
                                    />
                                    <span className="text-[10px] text-muted-foreground">
                                        {format(parseISO(day.date), 'EEEEE', {
                                            locale: es,
                                        })}
                                    </span>
                                </button>
                            ))}
                        </div>
                        <p className="mt-3 text-xs text-muted-foreground">
                            Media:{' '}
                            {Math.round(
                                week.reduce((acc, d) => acc + d.calories, 0) /
                                    7,
                            ).toLocaleString()}{' '}
                            kcal/día
                        </p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    {meals.map((meal) => (
                        <div
                            key={meal.meal_type}
                            className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                        >
                            <div className="mb-3 flex items-center justify-between">
                                <h2 className="font-medium">{meal.label}</h2>
                                <span className="text-sm text-muted-foreground">
                                    {meal.calories.toLocaleString()} kcal
                                </span>
                            </div>
                            <ul className="mb-3 space-y-2">
                                {meal.entries.length === 0 && (
                                    <li className="text-sm text-muted-foreground">
                                        Sin alimentos registrados.
                                    </li>
                                )}
                                {meal.entries.map((entry) => (
                                    <li
                                        key={entry.id}
                                        className="flex items-center justify-between gap-2 text-sm"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <span className="font-medium">
                                                {entry.name}
                                            </span>{' '}
                                            {entry.quantity && (
                                                <span className="text-muted-foreground">
                                                    · {entry.quantity}
                                                </span>
                                            )}
                                            <div className="text-xs text-muted-foreground">
                                                {entry.calories} kcal
                                                {entry.protein_g !== null &&
                                                    ` · P ${entry.protein_g}g`}
                                                {entry.carbs_g !== null &&
                                                    ` · C ${entry.carbs_g}g`}
                                                {entry.fat_g !== null &&
                                                    ` · G ${entry.fat_g}g`}
                                            </div>
                                        </div>
                                        <span className="flex shrink-0">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    setDialog({
                                                        entry,
                                                        mealType:
                                                            meal.meal_type,
                                                    })
                                                }
                                            >
                                                <Pencil className="size-3" />
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    router.delete(
                                                        destroyEntry(entry.id)
                                                            .url,
                                                        {
                                                            preserveScroll:
                                                                true,
                                                        },
                                                    )
                                                }
                                            >
                                                <Trash2 className="size-3" />
                                            </Button>
                                        </span>
                                    </li>
                                ))}
                            </ul>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() =>
                                    setDialog({
                                        entry: null,
                                        mealType: meal.meal_type,
                                    })
                                }
                            >
                                <Plus className="mr-1 size-4" />
                                Añadir alimento
                            </Button>
                        </div>
                    ))}
                </div>
            </div>

            {dialog && (
                <EntryDialog
                    date={date}
                    mealTypes={mealTypes}
                    foodCategories={foodCategories}
                    entry={dialog.entry}
                    defaultMealType={dialog.mealType}
                    onClose={() => setDialog(null)}
                />
            )}
        </>
    );
}

NutritionIndex.layout = {
    breadcrumbs: [{ title: 'Nutrición', href: index() }],
};
