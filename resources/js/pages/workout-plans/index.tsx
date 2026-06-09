import { Head, Link, router } from '@inertiajs/react';
import { Archive, ArchiveRestore, Copy, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    archive,
    create,
    destroy,
    duplicate,
    index,
    show,
} from '@/routes/workout-plans';
import type { WorkoutPlan } from '@/types/fitness';

function PlanCard({
    plan,
    archived,
}: {
    plan: WorkoutPlan;
    archived: boolean;
}) {
    return (
        <div className="flex items-center justify-between rounded-xl border p-4 transition-colors hover:bg-muted/50">
            <Link href={show(plan.id).url} className="min-w-0 flex-1">
                <h3 className="truncate font-medium">{plan.name}</h3>
                <p className="text-sm text-muted-foreground">
                    {plan.days_count ?? 0} días
                </p>
            </Link>
            <div className="flex shrink-0 gap-1">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    title="Duplicar"
                    onClick={() => router.post(duplicate(plan.id).url)}
                >
                    <Copy className="size-4" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    title={archived ? 'Restaurar' : 'Archivar'}
                    onClick={() => router.patch(archive(plan.id).url)}
                >
                    {archived ? (
                        <ArchiveRestore className="size-4" />
                    ) : (
                        <Archive className="size-4" />
                    )}
                </Button>
                {archived && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        title="Borrar definitivamente"
                        onClick={() => {
                            if (
                                confirm(
                                    '¿Borrar esta rutina definitivamente? Esta acción no se puede deshacer.',
                                )
                            ) {
                                router.delete(destroy(plan.id).url);
                            }
                        }}
                    >
                        <Trash2 className="size-4 text-destructive" />
                    </Button>
                )}
            </div>
        </div>
    );
}

export default function WorkoutPlansIndex({
    workoutPlans,
    archivedPlans,
}: {
    workoutPlans: WorkoutPlan[];
    archivedPlans: WorkoutPlan[];
}) {
    return (
        <>
            <Head title="Rutinas" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Rutinas</h1>
                    <Button asChild>
                        <Link href={create().url}>
                            <Plus className="mr-2 size-4" />
                            Nueva rutina
                        </Link>
                    </Button>
                </div>
                <div className="grid gap-3">
                    {workoutPlans.length === 0 && (
                        <p className="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                            Aún no tienes rutinas. Crea la primera para empezar
                            a planificar tus entrenamientos.
                        </p>
                    )}
                    {workoutPlans.map((plan) => (
                        <PlanCard key={plan.id} plan={plan} archived={false} />
                    ))}
                </div>

                {archivedPlans.length > 0 && (
                    <>
                        <h2 className="mt-4 text-sm font-medium text-muted-foreground">
                            Archivadas
                        </h2>
                        <div className="grid gap-3 opacity-70">
                            {archivedPlans.map((plan) => (
                                <PlanCard
                                    key={plan.id}
                                    plan={plan}
                                    archived={true}
                                />
                            ))}
                        </div>
                    </>
                )}
            </div>
        </>
    );
}

WorkoutPlansIndex.layout = {
    breadcrumbs: [{ title: 'Rutinas', href: index() }],
};
