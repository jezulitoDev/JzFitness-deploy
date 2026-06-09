import { Form, Head, Link } from '@inertiajs/react';
import WorkoutPlanController from '@/actions/App/Http/Controllers/WorkoutPlanController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, show } from '@/routes/workout-plans';
import type { WorkoutPlan } from '@/types/fitness';

export default function WorkoutPlansEdit({
    workoutPlan,
}: {
    workoutPlan: WorkoutPlan;
}) {
    return (
        <>
            <Head title={`Editar ${workoutPlan.name}`} />
            <div className="mx-auto max-w-lg p-4">
                <Form
                    {...WorkoutPlanController.update.form.put(workoutPlan.id)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={workoutPlan.name}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Descripción</Label>
                                <Input
                                    id="description"
                                    name="description"
                                    defaultValue={workoutPlan.description ?? ''}
                                />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Guardar
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={show(workoutPlan.id).url}>
                                        Volver
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

WorkoutPlansEdit.layout = {
    breadcrumbs: [
        { title: 'Rutinas', href: index() },
        { title: 'Editar', href: '#' },
    ],
};
