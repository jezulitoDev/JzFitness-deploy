import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ExerciseCard } from '@/components/exercise-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { create, index } from '@/routes/exercises';
import type { Exercise, MuscleGroup } from '@/types/fitness';

export default function ExercisesIndex({
    exercises,
    muscleGroups,
    filters,
}: {
    exercises: Exercise[];
    muscleGroups: MuscleGroup[];
    filters: { muscle_group_id: number | null; search: string | null };
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                index().url,
                {
                    ...(filters.muscle_group_id
                        ? { muscle_group_id: filters.muscle_group_id }
                        : {}),
                    ...(search ? { search } : {}),
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    return (
        <>
            <Head title="Ejercicios" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Ejercicios</h1>
                    <Button asChild>
                        <Link href={create().url}>
                            <Plus className="mr-2 size-4" />
                            Nuevo ejercicio
                        </Link>
                    </Button>
                </div>
                <div className="flex flex-col gap-2 sm:flex-row">
                    <div className="relative flex-1 sm:max-w-xs">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar ejercicio..."
                            className="pl-9"
                        />
                    </div>
                    <Select
                        value={filters.muscle_group_id?.toString() ?? 'all'}
                        onValueChange={(value) =>
                            router.get(
                                index().url,
                                {
                                    ...(value === 'all'
                                        ? {}
                                        : { muscle_group_id: value }),
                                    ...(search ? { search } : {}),
                                },
                                { preserveState: true },
                            )
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Grupo muscular" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            {muscleGroups.map((g) => (
                                <SelectItem key={g.id} value={String(g.id)}>
                                    {g.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-3">
                    {exercises.length === 0 && (
                        <p className="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground">
                            No hay ejercicios que coincidan con la búsqueda.
                        </p>
                    )}
                    {exercises.map((exercise) => (
                        <ExerciseCard key={exercise.id} exercise={exercise} />
                    ))}
                </div>
            </div>
        </>
    );
}

ExercisesIndex.layout = {
    breadcrumbs: [{ title: 'Ejercicios', href: index() }],
};
