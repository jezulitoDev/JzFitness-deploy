import { Form, Head, Link } from '@inertiajs/react';
import WorkoutPlanController from '@/actions/App/Http/Controllers/WorkoutPlanController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/workout-plans';

export default function WorkoutPlansCreate() {
    return (
        <>
            <Head title="Nueva rutina" />
            <div className="mx-auto max-w-lg p-4">
                <h1 className="mb-6 text-2xl font-semibold">Nueva rutina</h1>
                <Form
                    {...WorkoutPlanController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Descripción</Label>
                                <Input id="description" name="description" />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Crear
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

WorkoutPlansCreate.layout = {
    breadcrumbs: [
        { title: 'Rutinas', href: index() },
        { title: 'Nueva', href: '#' },
    ],
};
