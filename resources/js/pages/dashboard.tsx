import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    Apple,
    Bike,
    Dumbbell,
    Flame,
    Sparkles,
    Target,
    Timer,
} from 'lucide-react';
import { dashboard } from '@/routes';
import { edit as editFitness } from '@/routes/fitness';
import { index as nutrition } from '@/routes/nutrition';
import type { DashboardPersonalization, WeeklySummary } from '@/types/fitness';

function NutritionCard({
    nutritionSummary,
}: {
    nutritionSummary: { calories_today: number; calorie_target: number };
}) {
    const percentage =
        nutritionSummary.calorie_target > 0
            ? Math.min(
                  Math.round(
                      (nutritionSummary.calories_today /
                          nutritionSummary.calorie_target) *
                          100,
                  ),
                  100,
              )
            : 0;

    return (
        <Link
            href={nutrition()}
            className="rounded-xl border border-sidebar-border/70 p-6 transition-colors hover:bg-muted/50 dark:border-sidebar-border"
        >
            <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground">
                    Calorías hoy
                </span>
                <Apple className="size-5 text-muted-foreground" />
            </div>
            <p className="mt-2 text-3xl font-semibold">
                {nutritionSummary.calories_today.toLocaleString()}
                <span className="text-base font-normal text-muted-foreground">
                    {' '}
                    / {nutritionSummary.calorie_target.toLocaleString()} kcal
                </span>
            </p>
            <div className="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                <div
                    className="h-full rounded-full bg-primary transition-all"
                    style={{ width: `${percentage}%` }}
                />
            </div>
        </Link>
    );
}

function formatMinutes(minutes: number): string {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;

    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function WeeklyGoalCard({
    personalization,
}: {
    personalization: DashboardPersonalization;
}) {
    const target = personalization.weekly_target ?? 0;
    const done = Math.min(personalization.workouts_this_week, target);
    const percentage = target > 0 ? Math.round((done / target) * 100) : 0;

    return (
        <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
            <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground">
                    Meta semanal
                </span>
                <Target className="size-5 text-muted-foreground" />
            </div>
            <p className="mt-2 text-3xl font-semibold">
                {personalization.workouts_this_week} de {target}
            </p>
            <p className="mt-1 text-sm text-muted-foreground">
                entrenamientos esta semana
            </p>
            <div className="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                <div
                    className="h-full rounded-full bg-primary transition-all"
                    style={{ width: `${percentage}%` }}
                />
            </div>
        </div>
    );
}

export default function Dashboard({
    summary,
    personalization,
    nutrition: nutritionSummary,
}: {
    summary: WeeklySummary;
    personalization: DashboardPersonalization;
    nutrition: { calories_today: number; calorie_target: number };
}) {
    const widgets = [
        {
            title: 'Entrenamientos gym',
            value: String(summary.gym_sessions),
            icon: Dumbbell,
        },
        {
            title: 'Carreras',
            value: String(summary.strava_runs),
            icon: Activity,
        },
        {
            title: 'Bicicleta',
            value: String(summary.strava_rides),
            icon: Bike,
        },
        {
            title: 'Volumen semanal',
            value: `${Math.round(personalization.weekly_volume).toLocaleString()} ${personalization.units}`,
            icon: Flame,
        },
        {
            title: 'Tiempo entrenado',
            value: formatMinutes(summary.training_time_minutes),
            icon: Timer,
        },
        {
            title: 'Racha activa',
            value: `${summary.active_streak_days} días`,
            icon: Flame,
        },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Hola, {personalization.first_name}
                    </h1>
                    {personalization.has_fitness_profile ? (
                        <p className="text-muted-foreground">
                            {personalization.goal_label} · nivel{' '}
                            {personalization.level_label?.toLowerCase()} ·{' '}
                            {personalization.goal_tagline}
                        </p>
                    ) : (
                        <p className="text-muted-foreground">
                            Resumen de la semana actual
                        </p>
                    )}
                </div>

                {!personalization.has_fitness_profile && (
                    <div className="flex flex-col gap-3 rounded-xl border border-dashed border-sidebar-border/70 p-6 sm:flex-row sm:items-center sm:justify-between dark:border-sidebar-border">
                        <div className="flex items-start gap-3">
                            <Sparkles className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
                            <div>
                                <p className="font-medium">
                                    Completa tu perfil fitness
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Cuéntanos tu objetivo y nivel para
                                    personalizar tu experiencia.
                                </p>
                            </div>
                        </div>
                        <Link
                            href={editFitness()}
                            className="inline-flex shrink-0 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Configurar
                        </Link>
                    </div>
                )}

                <div className="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {personalization.weekly_target !== null && (
                        <WeeklyGoalCard personalization={personalization} />
                    )}
                    <NutritionCard nutritionSummary={nutritionSummary} />
                    {widgets.map((widget) => (
                        <div
                            key={widget.title}
                            className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                        >
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">
                                    {widget.title}
                                </span>
                                <widget.icon className="size-5 text-muted-foreground" />
                            </div>
                            <p className="mt-2 text-3xl font-semibold">
                                {widget.value}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
};
