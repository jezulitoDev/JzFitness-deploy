import { Link, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { edit } from '@/routes/exercises';
import type { Auth } from '@/types/auth';
import type { Exercise } from '@/types/fitness';

export function ExerciseCard({ exercise }: { exercise: Exercise }) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const isOwn = exercise.user_id === auth.user.id;

    return (
        <div className="flex items-center justify-between rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div>
                <h3 className="font-medium">{exercise.name}</h3>
                <div className="mt-1 flex gap-2">
                    {exercise.muscle_group && (
                        <Badge variant="secondary">{exercise.muscle_group.name}</Badge>
                    )}
                    {exercise.equipment && (
                        <Badge variant="outline">{exercise.equipment}</Badge>
                    )}
                    {isOwn && <Badge>Personalizado</Badge>}
                </div>
            </div>
            {isOwn && (
                <Link
                    href={edit(exercise.id)}
                    className="text-sm text-muted-foreground hover:text-foreground"
                >
                    Editar
                </Link>
            )}
        </div>
    );
}
