import { Form, Head, Link } from '@inertiajs/react';
import { CheckCircle } from 'lucide-react';
import { SetTracker } from '@/components/set-tracker';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { WorkoutTimer } from '@/components/workout-timer';
import { finish, index } from '@/routes/gym-sessions';
import { store as storeExercise } from '@/routes/gym-sessions/exercises';
import type { Exercise, GymSession } from '@/types/fitness';

export default function GymSessionsPlay({
    session,
    exercises,
}: {
    session: GymSession;
    exercises: Exercise[];
}) {
    return (
        <>
            <Head title="Entrenamiento en curso" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Entrenamiento</h1>
                    <Form {...finish.form(session.id)}>
                        <Button type="submit">
                            <CheckCircle className="mr-2 size-4" />
                            Finalizar
                        </Button>
                    </Form>
                </div>

                <WorkoutTimer />

                {session.exercises?.map((sessionExercise) => (
                    <div
                        key={sessionExercise.id}
                        className="rounded-xl border p-4"
                    >
                        <h2 className="mb-3 font-medium">
                            {sessionExercise.exercise?.name}
                        </h2>
                        <SetTracker sessionExercise={sessionExercise} />
                    </div>
                ))}

                <Form
                    {...storeExercise.form(session.id)}
                    className="flex gap-2"
                >
                    <Select name="exercise_id" required>
                        <SelectTrigger className="flex-1">
                            <SelectValue placeholder="Añadir ejercicio" />
                        </SelectTrigger>
                        <SelectContent>
                            {exercises.map((ex) => (
                                <SelectItem key={ex.id} value={String(ex.id)}>
                                    {ex.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button type="submit">Añadir</Button>
                </Form>

                <Button variant="outline" asChild>
                    <Link href={index().url}>Volver al listado</Link>
                </Button>
            </div>
        </>
    );
}

GymSessionsPlay.layout = {
    breadcrumbs: [
        { title: 'Entrenamientos', href: index() },
        { title: 'En curso', href: '#' },
    ],
};
