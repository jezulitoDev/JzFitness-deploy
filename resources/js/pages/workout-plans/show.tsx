import { Form, Head, Link, router } from '@inertiajs/react';
import {
    Archive,
    ArchiveRestore,
    ChevronDown,
    ChevronUp,
    Copy,
    Pencil,
    Plus,
    Trash2,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { archive, duplicate, edit, index } from '@/routes/workout-plans';
import { destroy as destroyDay, reorder as reorderDays, store as storeDay } from '@/routes/workout-plans/days';
import {
    destroy as destroyDayExercise,
    reorder as reorderDayExercises,
    store as storeDayExercise,
    update as updateDayExercise,
} from '@/routes/workout-plans/days/exercises';
import type {
    Exercise,
    WorkoutPlan,
    WorkoutPlanDay,
    WorkoutPlanDayExercise,
} from '@/types/fitness';

function targetSummary(pe: WorkoutPlanDayExercise): string | null {
    if (!pe.target_sets && !pe.target_reps && !pe.target_weight) {
        return null;
    }

    const sets = pe.target_sets ?? '?';
    const reps = pe.target_reps ?? '?';
    const weight = pe.target_weight ? ` @ ${pe.target_weight} kg` : '';

    return `${sets} × ${reps}${weight}`;
}

function DayExerciseRow({
    planId,
    day,
    pe,
    isFirst,
    isLast,
    onMove,
}: {
    planId: number;
    day: WorkoutPlanDay;
    pe: WorkoutPlanDayExercise;
    isFirst: boolean;
    isLast: boolean;
    onMove: (direction: 'up' | 'down') => void;
}) {
    const [editing, setEditing] = useState(false);
    const summary = targetSummary(pe);

    if (editing) {
        return (
            <li className="rounded-lg border bg-muted/30 p-3">
                <Form
                    {...updateDayExercise.form.patch([planId, day.id, pe.id])}
                    onSuccess={() => setEditing(false)}
                    className="flex flex-wrap items-end gap-2"
                >
                    <div className="flex flex-col gap-1">
                        <label className="text-xs text-muted-foreground">
                            Series
                        </label>
                        <Input
                            name="target_sets"
                            type="number"
                            min={1}
                            max={20}
                            defaultValue={pe.target_sets ?? ''}
                            className="w-20"
                        />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs text-muted-foreground">
                            Reps
                        </label>
                        <Input
                            name="target_reps"
                            type="number"
                            min={1}
                            max={100}
                            defaultValue={pe.target_reps ?? ''}
                            className="w-20"
                        />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs text-muted-foreground">
                            Peso (kg)
                        </label>
                        <Input
                            name="target_weight"
                            type="number"
                            step="0.5"
                            min={0}
                            defaultValue={pe.target_weight ?? ''}
                            className="w-24"
                        />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs text-muted-foreground">
                            Descanso (s)
                        </label>
                        <Input
                            name="default_rest_seconds"
                            type="number"
                            min={0}
                            max={600}
                            defaultValue={pe.default_rest_seconds}
                            className="w-24"
                        />
                    </div>
                    <Button type="submit" size="sm">
                        Guardar
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => setEditing(false)}
                    >
                        <X className="size-4" />
                    </Button>
                </Form>
            </li>
        );
    }

    return (
        <li className="flex items-center justify-between gap-2 text-sm">
            <span className="min-w-0 flex-1 truncate">
                {pe.exercise?.name}{' '}
                {summary && (
                    <Badge variant="secondary" className="ml-1">
                        {summary}
                    </Badge>
                )}{' '}
                <span className="text-muted-foreground">
                    ({pe.default_rest_seconds}s descanso)
                </span>
            </span>
            <span className="flex shrink-0 items-center">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    disabled={isFirst}
                    onClick={() => onMove('up')}
                >
                    <ChevronUp className="size-3" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    disabled={isLast}
                    onClick={() => onMove('down')}
                >
                    <ChevronDown className="size-3" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => setEditing(true)}
                >
                    <Pencil className="size-3" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() =>
                        router.delete(
                            destroyDayExercise([planId, day.id, pe.id]).url,
                        )
                    }
                >
                    <Trash2 className="size-3" />
                </Button>
            </span>
        </li>
    );
}

export default function WorkoutPlansShow({
    workoutPlan,
    exercises,
}: {
    workoutPlan: WorkoutPlan;
    exercises: Exercise[];
}) {
    const days = workoutPlan.days ?? [];
    const isArchived = workoutPlan.archived_at !== null;

    const moveDay = (dayId: number, direction: 'up' | 'down') => {
        const ids = days.map((d) => d.id);
        const from = ids.indexOf(dayId);
        const to = direction === 'up' ? from - 1 : from + 1;

        if (to < 0 || to >= ids.length) {
            return;
        }

        [ids[from], ids[to]] = [ids[to], ids[from]];
        router.patch(reorderDays(workoutPlan.id).url, { day_ids: ids });
    };

    const moveDayExercise = (
        day: WorkoutPlanDay,
        peId: number,
        direction: 'up' | 'down',
    ) => {
        const ids = (day.exercises ?? []).map((e) => e.id);
        const from = ids.indexOf(peId);
        const to = direction === 'up' ? from - 1 : from + 1;

        if (to < 0 || to >= ids.length) {
            return;
        }

        [ids[from], ids[to]] = [ids[to], ids[from]];
        router.patch(reorderDayExercises([workoutPlan.id, day.id]).url, {
            exercise_ids: ids,
        });
    };

    return (
        <>
            <Head title={workoutPlan.name} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-semibold">
                            {workoutPlan.name}
                            {isArchived && (
                                <Badge variant="outline">Archivada</Badge>
                            )}
                        </h1>
                        {workoutPlan.description && (
                            <p className="text-muted-foreground">
                                {workoutPlan.description}
                            </p>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() =>
                                router.post(duplicate(workoutPlan.id).url)
                            }
                        >
                            <Copy className="mr-1 size-4" />
                            Duplicar
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() =>
                                router.patch(archive(workoutPlan.id).url)
                            }
                        >
                            {isArchived ? (
                                <>
                                    <ArchiveRestore className="mr-1 size-4" />
                                    Restaurar
                                </>
                            ) : (
                                <>
                                    <Archive className="mr-1 size-4" />
                                    Archivar
                                </>
                            )}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={edit(workoutPlan.id).url}>Editar</Link>
                        </Button>
                    </div>
                </div>

                <Form {...storeDay.form(workoutPlan.id)} resetOnSuccess className="flex gap-2">
                    <Input name="name" placeholder="Nombre del día (Push)" required />
                    <Button type="submit" size="sm">
                        <Plus className="mr-1 size-4" />
                        Añadir día
                    </Button>
                </Form>

                {days.map((day, dayIndex) => (
                    <div key={day.id} className="rounded-xl border p-4">
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="font-medium">{day.name}</h2>
                            <div className="flex items-center">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    disabled={dayIndex === 0}
                                    onClick={() => moveDay(day.id, 'up')}
                                >
                                    <ChevronUp className="size-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    disabled={dayIndex === days.length - 1}
                                    onClick={() => moveDay(day.id, 'down')}
                                >
                                    <ChevronDown className="size-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    onClick={() =>
                                        router.delete(
                                            destroyDay([
                                                workoutPlan.id,
                                                day.id,
                                            ]).url,
                                        )
                                    }
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </div>
                        </div>
                        <ul className="mb-3 space-y-1">
                            {day.exercises?.map((pe, peIndex) => (
                                <DayExerciseRow
                                    key={pe.id}
                                    planId={workoutPlan.id}
                                    day={day}
                                    pe={pe}
                                    isFirst={peIndex === 0}
                                    isLast={
                                        peIndex ===
                                        (day.exercises?.length ?? 0) - 1
                                    }
                                    onMove={(direction) =>
                                        moveDayExercise(day, pe.id, direction)
                                    }
                                />
                            ))}
                        </ul>
                        <Form
                            {...storeDayExercise.form([workoutPlan.id, day.id])}
                            resetOnSuccess
                            className="flex flex-wrap items-center gap-2"
                        >
                            <Select name="exercise_id" required>
                                <SelectTrigger className="min-w-48 flex-1">
                                    <SelectValue placeholder="Añadir ejercicio" />
                                </SelectTrigger>
                                <SelectContent>
                                    {exercises.map((ex) => (
                                        <SelectItem
                                            key={ex.id}
                                            value={String(ex.id)}
                                        >
                                            {ex.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Input
                                name="target_sets"
                                type="number"
                                min={1}
                                max={20}
                                placeholder="Series"
                                className="w-20"
                            />
                            <Input
                                name="target_reps"
                                type="number"
                                min={1}
                                max={100}
                                placeholder="Reps"
                                className="w-20"
                            />
                            <Input
                                name="target_weight"
                                type="number"
                                step="0.5"
                                min={0}
                                placeholder="Peso kg"
                                className="w-24"
                            />
                            <Button type="submit" size="sm">
                                Añadir
                            </Button>
                        </Form>
                    </div>
                ))}
            </div>
        </>
    );
}

WorkoutPlansShow.layout = {
    breadcrumbs: [
        { title: 'Rutinas', href: index() },
        { title: 'Detalle', href: '#' },
    ],
};
