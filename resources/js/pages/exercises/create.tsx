import { Form, Head, Link } from '@inertiajs/react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/exercises';
import type { MuscleGroup } from '@/types/fitness';

export default function ExercisesCreate({
    muscleGroups,
}: {
    muscleGroups: MuscleGroup[];
}) {
    return (
        <>
            <Head title="Nuevo ejercicio" />
            <div className="mx-auto max-w-lg p-4">
                <h1 className="mb-6 text-2xl font-semibold">Nuevo ejercicio</h1>
                <Form
                    {...ExerciseController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="muscle_group_id">Grupo muscular</Label>
                                <Select name="muscle_group_id" required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Seleccionar" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {muscleGroups.map((g) => (
                                            <SelectItem
                                                key={g.id}
                                                value={String(g.id)}
                                            >
                                                {g.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.muscle_group_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="equipment">Equipo</Label>
                                <Input id="equipment" name="equipment" />
                                <InputError message={errors.equipment} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Descripción</Label>
                                <Input id="description" name="description" />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Guardar
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={index().url}>Cancelar</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

ExercisesCreate.layout = {
    breadcrumbs: [
        { title: 'Ejercicios', href: index() },
        { title: 'Nuevo', href: '#' },
    ],
};
