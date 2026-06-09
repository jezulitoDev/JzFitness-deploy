import {
    addDays,
    addMonths,
    eachDayOfInterval,
    endOfMonth,
    endOfWeek,
    format,
    isSameMonth,
    isToday,
    parseISO,
    startOfMonth,
    startOfWeek,
    subMonths,
} from 'date-fns';
import { es } from 'date-fns/locale';
import {
    Bike,
    CalendarCheck,
    CircleDashed,
    Dumbbell,
    Footprints,
    Moon,
} from 'lucide-react';
import type { CalendarEvent } from '@/types/fitness';

const iconMap: Record<string, typeof Dumbbell> = {
    gym: Dumbbell,
    running: Footprints,
    cycling: Bike,
    walking: Footprints,
    hiking: Footprints,
    swimming: Footprints,
    rest: Moon,
};

function PlannedChip({
    event,
    onClick,
}: {
    event: CalendarEvent;
    onClick?: (event: CalendarEvent) => void;
}) {
    const Icon = event.completed ? CalendarCheck : CircleDashed;

    return (
        <button
            type="button"
            title={event.name}
            onClick={(e) => {
                e.stopPropagation();
                onClick?.(event);
            }}
            className={`flex w-full items-center gap-1 truncate rounded px-1 py-0.5 text-left text-[11px] leading-tight transition-colors ${
                event.completed
                    ? 'bg-emerald-500/15 text-emerald-700 line-through hover:bg-emerald-500/25 dark:text-emerald-400'
                    : 'bg-primary/10 text-primary hover:bg-primary/20'
            }`}
        >
            <Icon className="size-3 shrink-0" />
            <span className="truncate">{event.name}</span>
        </button>
    );
}

export function CalendarGrid({
    view,
    year,
    month,
    weekStart,
    events,
    onDayClick,
    onPlannedClick,
}: {
    view: 'month' | 'week';
    year: number;
    month: number;
    weekStart: string;
    events: Record<string, CalendarEvent[]>;
    onDayClick?: (date: string) => void;
    onPlannedClick?: (event: CalendarEvent, date: string) => void;
}) {
    let days: Date[];
    const monthStart = startOfMonth(new Date(year, month - 1));

    if (view === 'week') {
        const start = parseISO(weekStart);
        days = eachDayOfInterval({ start, end: addDays(start, 6) });
    } else {
        const calendarStart = startOfWeek(monthStart, { weekStartsOn: 1 });
        const calendarEnd = endOfWeek(endOfMonth(monthStart), {
            weekStartsOn: 1,
        });
        days = eachDayOfInterval({ start: calendarStart, end: calendarEnd });
    }

    return (
        <div>
            <div className="mb-2 grid grid-cols-7 gap-1 text-center text-xs font-medium text-muted-foreground">
                {['L', 'M', 'X', 'J', 'V', 'S', 'D'].map((d) => (
                    <div key={d}>{d}</div>
                ))}
            </div>
            <div className="grid grid-cols-7 gap-1">
                {days.map((day) => {
                    const dateKey = format(day, 'yyyy-MM-dd');
                    const dayEvents = events[dateKey] ?? [];
                    const inMonth =
                        view === 'week' || isSameMonth(day, monthStart);
                    const planned = dayEvents.filter(
                        (e) => e.type === 'planned',
                    );
                    const done = dayEvents.filter((e) => e.type !== 'planned');

                    return (
                        <button
                            type="button"
                            key={dateKey}
                            onClick={() => onDayClick?.(dateKey)}
                            className={`flex flex-col rounded-lg border p-1 text-left text-xs transition-colors ${
                                view === 'week' ? 'min-h-36' : 'min-h-24'
                            } ${
                                inMonth
                                    ? 'border-sidebar-border/70 bg-card hover:bg-muted/50 dark:border-sidebar-border'
                                    : 'border-transparent bg-muted/30 opacity-50'
                            } ${isToday(day) ? 'ring-2 ring-primary/50' : ''}`}
                        >
                            <div className="font-medium">
                                {format(day, 'd', { locale: es })}
                            </div>
                            <div className="mt-1 flex w-full flex-col gap-0.5">
                                {planned.map((event) => (
                                    <PlannedChip
                                        key={`planned-${event.id}`}
                                        event={event}
                                        onClick={(e) =>
                                            onPlannedClick?.(e, dateKey)
                                        }
                                    />
                                ))}
                            </div>
                            <div className="mt-1 flex flex-wrap gap-0.5">
                                {dayEvents.length === 0 && inMonth && (
                                    <Moon className="size-3 text-muted-foreground/50" />
                                )}
                                {done.map((event, i) => {
                                    const Icon =
                                        iconMap[event.type] ?? Footprints;

                                    return (
                                        <span
                                            key={`${event.type}-${event.id}-${i}`}
                                            title={event.name}
                                            className="inline-flex"
                                        >
                                            <Icon className="size-3.5" />
                                        </span>
                                    );
                                })}
                            </div>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}

export function calendarNavMonth(
    year: number,
    month: number,
    direction: 'prev' | 'next',
): { year: number; month: number } {
    const date =
        direction === 'prev'
            ? subMonths(new Date(year, month - 1), 1)
            : addMonths(new Date(year, month - 1), 1);

    return { year: date.getFullYear(), month: date.getMonth() + 1 };
}

export function calendarNavWeek(
    weekStart: string,
    direction: 'prev' | 'next',
): string {
    const next = addDays(parseISO(weekStart), direction === 'prev' ? -7 : 7);

    return format(next, 'yyyy-MM-dd');
}
