import { Form, Head, Link } from '@inertiajs/react';
import { Play, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index, play, show, store } from '@/routes/gym-sessions';
import type { GymSession, WorkoutPlan } from '@/types/fitness';

export default function GymSessionsIndex({
    sessions,
    workoutPlans,
    activeSession,
}: {
    sessions: GymSession[];
    workoutPlans: WorkoutPlan[];
    activeSession: GymSession | null;
}) {
    return (
        <>
            <Head title="Entrenamientos" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Entrenamientos</h1>
                    {activeSession ? (
                        <Button asChild>
                            <Link href={play(activeSession.id).url}>
                                <Play className="mr-2 size-4" />
                                Continuar entrenamiento
                            </Link>
                        </Button>
                    ) : (
                        <Form {...store.form()} className="flex items-end gap-2">
                            <div className="w-48">
                                <Select name="workout_plan_day_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Desde rutina (opcional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {workoutPlans.flatMap((plan) =>
                                            (plan.days ?? []).map((day) => (
                                                <SelectItem
                                                    key={day.id}
                                                    value={String(day.id)}
                                                >
                                                    {plan.name} - {day.name}
                                                </SelectItem>
                                            )),
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button type="submit">
                                <Plus className="mr-2 size-4" />
                                Iniciar
                            </Button>
                        </Form>
                    )}
                </div>
                <div className="grid gap-2">
                    {sessions.map((session) => (
                        <Link
                            key={session.id}
                            href={
                                session.ended_at
                                    ? show(session.id).url
                                    : play(session.id).url
                            }
                            className="rounded-xl border p-4 hover:bg-muted/50"
                        >
                            <div className="flex justify-between">
                                <span className="font-medium">
                                    {new Date(session.started_at).toLocaleString()}
                                </span>
                                <span className="text-sm text-muted-foreground">
                                    {session.ended_at ? 'Completado' : 'En curso'}
                                </span>
                            </div>
                            {session.workout_plan && (
                                <p className="text-sm text-muted-foreground">
                                    {session.workout_plan.name}
                                </p>
                            )}
                        </Link>
                    ))}
                </div>
            </div>
        </>
    );
}

GymSessionsIndex.layout = {
    breadcrumbs: [{ title: 'Entrenamientos', href: index() }],
};
