import { Head, router, useForm } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import {
    CalendarGrid,
    calendarNavMonth,
    calendarNavWeek,
} from '@/components/calendar-grid';
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
import { index } from '@/routes/calendar';
import {
    destroy as destroyScheduled,
    store as storeScheduled,
    update as updateScheduled,
} from '@/routes/scheduled-workouts';
import type { CalendarEvent, CalendarPlanOption } from '@/types/fitness';

function ScheduleDialog({
    date,
    workoutPlans,
    onClose,
}: {
    date: string;
    workoutPlans: CalendarPlanOption[];
    onClose: () => void;
}) {
    const { data, setData, post, processing, errors } = useForm({
        scheduled_date: date,
        workout_plan_id: '',
        workout_plan_day_id: '',
        title: '',
        notes: '',
    });

    const selectedPlan = workoutPlans.find(
        (p) => String(p.id) === data.workout_plan_id,
    );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(storeScheduled().url, {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        Planificar entrenamiento ·{' '}
                        {format(parseISO(date), "d 'de' MMMM", { locale: es })}
                    </DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="flex flex-col gap-4">
                    <div className="grid gap-2">
                        <Label>Rutina (opcional)</Label>
                        <Select
                            value={data.workout_plan_id || 'none'}
                            onValueChange={(value) => {
                                setData((prev) => ({
                                    ...prev,
                                    workout_plan_id:
                                        value === 'none' ? '' : value,
                                    workout_plan_day_id: '',
                                }));
                            }}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Sin rutina" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Sin rutina</SelectItem>
                                {workoutPlans.map((plan) => (
                                    <SelectItem
                                        key={plan.id}
                                        value={String(plan.id)}
                                    >
                                        {plan.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.workout_plan_id && (
                            <p className="text-sm text-destructive">
                                {errors.workout_plan_id}
                            </p>
                        )}
                    </div>

                    {selectedPlan && (selectedPlan.days?.length ?? 0) > 0 && (
                        <div className="grid gap-2">
                            <Label>Día de la rutina (opcional)</Label>
                            <Select
                                value={data.workout_plan_day_id || 'none'}
                                onValueChange={(value) =>
                                    setData(
                                        'workout_plan_day_id',
                                        value === 'none' ? '' : value,
                                    )
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Toda la rutina" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">
                                        Toda la rutina
                                    </SelectItem>
                                    {selectedPlan.days?.map((day) => (
                                        <SelectItem
                                            key={day.id}
                                            value={String(day.id)}
                                        >
                                            {day.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    )}

                    {!data.workout_plan_id && (
                        <div className="grid gap-2">
                            <Label htmlFor="schedule-title">Título</Label>
                            <Input
                                id="schedule-title"
                                value={data.title}
                                onChange={(e) =>
                                    setData('title', e.target.value)
                                }
                                placeholder="Cardio, movilidad, descanso activo..."
                            />
                            {errors.title && (
                                <p className="text-sm text-destructive">
                                    {errors.title}
                                </p>
                            )}
                        </div>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="schedule-notes">Notas</Label>
                        <Input
                            id="schedule-notes"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            placeholder="Opcional"
                        />
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
                            Planificar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function PlannedEventDialog({
    event,
    date,
    onClose,
}: {
    event: CalendarEvent;
    date: string;
    onClose: () => void;
}) {
    const [newDate, setNewDate] = useState(date);

    const toggleCompleted = () => {
        router.patch(
            updateScheduled(event.id).url,
            { completed: !event.completed },
            { preserveScroll: true, onSuccess: () => onClose() },
        );
    };

    const reschedule = () => {
        router.patch(
            updateScheduled(event.id).url,
            { scheduled_date: newDate },
            { preserveScroll: true, onSuccess: () => onClose() },
        );
    };

    const remove = () => {
        router.delete(destroyScheduled(event.id).url, {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{event.name}</DialogTitle>
                </DialogHeader>
                <div className="flex flex-col gap-4">
                    {event.notes && (
                        <p className="text-sm text-muted-foreground">
                            {event.notes}
                        </p>
                    )}
                    <Button
                        type="button"
                        variant={event.completed ? 'outline' : 'default'}
                        onClick={toggleCompleted}
                    >
                        {event.completed
                            ? 'Marcar como pendiente'
                            : 'Marcar como completado'}
                    </Button>
                    <div className="grid gap-2">
                        <Label htmlFor="reschedule-date">Mover a otro día</Label>
                        <div className="flex gap-2">
                            <Input
                                id="reschedule-date"
                                type="date"
                                value={newDate}
                                onChange={(e) => setNewDate(e.target.value)}
                            />
                            <Button
                                type="button"
                                variant="outline"
                                onClick={reschedule}
                                disabled={newDate === date}
                            >
                                Mover
                            </Button>
                        </div>
                    </div>
                    <Button
                        type="button"
                        variant="destructive"
                        onClick={remove}
                    >
                        Eliminar planificación
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

export default function CalendarIndex({
    view,
    year,
    month,
    weekStart,
    events,
    workoutPlans,
}: {
    view: 'month' | 'week';
    year: number;
    month: number;
    weekStart: string;
    events: Record<string, CalendarEvent[]>;
    workoutPlans: CalendarPlanOption[];
}) {
    const [scheduleDate, setScheduleDate] = useState<string | null>(null);
    const [plannedEvent, setPlannedEvent] = useState<{
        event: CalendarEvent;
        date: string;
    } | null>(null);

    const title =
        view === 'week'
            ? `Semana del ${format(parseISO(weekStart), "d 'de' MMMM yyyy", { locale: es })}`
            : new Date(year, month - 1).toLocaleString('es', {
                  month: 'long',
                  year: 'numeric',
              });

    const navigate = (direction: 'prev' | 'next') => {
        if (view === 'week') {
            router.get(
                index().url,
                {
                    view: 'week',
                    week_start: calendarNavWeek(weekStart, direction),
                },
                { preserveState: true },
            );
        } else {
            router.get(
                index().url,
                calendarNavMonth(year, month, direction),
                { preserveState: true },
            );
        }
    };

    const switchView = (next: 'month' | 'week') => {
        if (next === view) {
            return;
        }

        router.get(
            index().url,
            next === 'week' ? { view: 'week' } : {},
            { preserveState: true },
        );
    };

    return (
        <>
            <Head title="Calendario" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-2xl font-semibold capitalize">
                        {title}
                    </h1>
                    <div className="flex items-center gap-2">
                        <div className="flex rounded-lg border p-0.5">
                            <Button
                                type="button"
                                variant={view === 'month' ? 'secondary' : 'ghost'}
                                size="sm"
                                onClick={() => switchView('month')}
                            >
                                Mes
                            </Button>
                            <Button
                                type="button"
                                variant={view === 'week' ? 'secondary' : 'ghost'}
                                size="sm"
                                onClick={() => switchView('week')}
                            >
                                Semana
                            </Button>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() => navigate('prev')}
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() => navigate('next')}
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                    </div>
                </div>
                <p className="text-sm text-muted-foreground">
                    Haz clic en un día para planificar un entrenamiento, o en
                    un entrenamiento planificado para gestionarlo.
                </p>
                <CalendarGrid
                    view={view}
                    year={year}
                    month={month}
                    weekStart={weekStart}
                    events={events}
                    onDayClick={(date) => setScheduleDate(date)}
                    onPlannedClick={(event, date) =>
                        setPlannedEvent({ event, date })
                    }
                />
            </div>

            {scheduleDate && (
                <ScheduleDialog
                    date={scheduleDate}
                    workoutPlans={workoutPlans}
                    onClose={() => setScheduleDate(null)}
                />
            )}
            {plannedEvent && (
                <PlannedEventDialog
                    event={plannedEvent.event}
                    date={plannedEvent.date}
                    onClose={() => setPlannedEvent(null)}
                />
            )}
        </>
    );
}

CalendarIndex.layout = {
    breadcrumbs: [{ title: 'Calendario', href: index() }],
};
