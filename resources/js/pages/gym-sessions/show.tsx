import { Head, Link } from '@inertiajs/react';
import { index } from '@/routes/gym-sessions';
import type { GymSession } from '@/types/fitness';

export default function GymSessionsShow({
    session,
    stats,
}: {
    session: GymSession;
    stats: { volume: number; sets: number; duration_minutes: number };
}) {
    return (
        <>
            <Head title="Resumen entrenamiento" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">Resumen</h1>
                <p className="text-muted-foreground">
                    {new Date(session.started_at).toLocaleString()}
                </p>
                <div className="grid gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-4">
                        <p className="text-sm text-muted-foreground">Volumen</p>
                        <p className="text-2xl font-semibold">
                            {Math.round(stats.volume).toLocaleString()} kg
                        </p>
                    </div>
                    <div className="rounded-xl border p-4">
                        <p className="text-sm text-muted-foreground">Series</p>
                        <p className="text-2xl font-semibold">{stats.sets}</p>
                    </div>
                    <div className="rounded-xl border p-4">
                        <p className="text-sm text-muted-foreground">Duración</p>
                        <p className="text-2xl font-semibold">
                            {stats.duration_minutes} min
                        </p>
                    </div>
                </div>
                {session.exercises?.map((se) => (
                    <div key={se.id} className="rounded-xl border p-4">
                        <h3 className="font-medium">{se.exercise?.name}</h3>
                        <ul className="mt-2 space-y-1 text-sm">
                            {se.sets
                                ?.filter((s) => s.completed)
                                .map((s) => (
                                    <li key={s.id}>
                                        {s.weight}kg x {s.reps}
                                    </li>
                                ))}
                        </ul>
                    </div>
                ))}
                <Link href={index().url} className="text-sm text-primary">
                    Volver a entrenamientos
                </Link>
            </div>
        </>
    );
}

GymSessionsShow.layout = {
    breadcrumbs: [
        { title: 'Entrenamientos', href: index() },
        { title: 'Resumen', href: '#' },
    ],
};
