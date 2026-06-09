import { Form, Head } from '@inertiajs/react';
import { Activity, Dumbbell, Flame, HeartPulse } from 'lucide-react';
import { useState } from 'react';
import FitnessProfileController from '@/actions/App/Http/Controllers/Settings/FitnessProfileController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/fitness';
import type { FitnessProfile, SelectOption } from '@/types/fitness';

const goalIcons: Record<string, typeof Dumbbell> = {
    lose_weight: Flame,
    gain_muscle: Dumbbell,
    endurance: Activity,
    general_health: HeartPulse,
};

const trainingDays = [1, 2, 3, 4, 5, 6, 7];

export default function Fitness({
    fitnessProfile,
    goals,
    levels,
}: {
    fitnessProfile: FitnessProfile;
    goals: SelectOption[];
    levels: SelectOption[];
}) {
    const [units, setUnits] = useState<'kg' | 'lb'>(
        fitnessProfile.preferred_units,
    );

    return (
        <>
            <Head title="Perfil fitness" />

            <h1 className="sr-only">Perfil fitness</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Perfil fitness"
                    description="Personaliza tu objetivo, nivel y preferencias de entrenamiento"
                />

                <Form
                    {...FitnessProfileController.update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-8"
                >
                    {({ processing, errors }) => (
                        <>
                            <fieldset className="grid gap-2">
                                <legend className="mb-2 text-sm leading-none font-medium">
                                    ¿Cuál es tu objetivo principal?
                                </legend>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {goals.map((goal) => {
                                        const Icon =
                                            goalIcons[goal.value] ?? Dumbbell;

                                        return (
                                            <label
                                                key={goal.value}
                                                className="flex cursor-pointer items-center gap-3 rounded-xl border border-sidebar-border/70 p-4 transition-colors hover:bg-muted/50 has-checked:border-primary has-checked:bg-primary/5 dark:border-sidebar-border"
                                            >
                                                <input
                                                    type="radio"
                                                    name="fitness_goal"
                                                    value={goal.value}
                                                    defaultChecked={
                                                        fitnessProfile.fitness_goal ===
                                                        goal.value
                                                    }
                                                    className="sr-only"
                                                />
                                                <Icon className="size-5 shrink-0 text-muted-foreground" />
                                                <span className="text-sm font-medium">
                                                    {goal.label}
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                                <InputError message={errors.fitness_goal} />
                            </fieldset>

                            <fieldset className="grid gap-2">
                                <legend className="mb-2 text-sm leading-none font-medium">
                                    Nivel de experiencia
                                </legend>
                                <div className="grid grid-cols-3 gap-3">
                                    {levels.map((level) => (
                                        <label
                                            key={level.value}
                                            className="flex cursor-pointer items-center justify-center rounded-xl border border-sidebar-border/70 px-3 py-2.5 text-center text-sm font-medium transition-colors hover:bg-muted/50 has-checked:border-primary has-checked:bg-primary/5 dark:border-sidebar-border"
                                        >
                                            <input
                                                type="radio"
                                                name="experience_level"
                                                value={level.value}
                                                defaultChecked={
                                                    fitnessProfile.experience_level ===
                                                    level.value
                                                }
                                                className="sr-only"
                                            />
                                            {level.label}
                                        </label>
                                    ))}
                                </div>
                                <InputError message={errors.experience_level} />
                            </fieldset>

                            <fieldset className="grid gap-2">
                                <legend className="mb-2 text-sm leading-none font-medium">
                                    Días de entrenamiento por semana
                                </legend>
                                <div className="flex flex-wrap gap-2">
                                    {trainingDays.map((day) => (
                                        <label
                                            key={day}
                                            className="flex size-10 cursor-pointer items-center justify-center rounded-full border border-sidebar-border/70 text-sm font-medium transition-colors hover:bg-muted/50 has-checked:border-primary has-checked:bg-primary has-checked:text-primary-foreground dark:border-sidebar-border"
                                        >
                                            <input
                                                type="radio"
                                                name="training_days_per_week"
                                                value={day}
                                                defaultChecked={
                                                    fitnessProfile.training_days_per_week ===
                                                    day
                                                }
                                                className="sr-only"
                                            />
                                            {day}
                                        </label>
                                    ))}
                                </div>
                                <InputError
                                    message={errors.training_days_per_week}
                                />
                            </fieldset>

                            <fieldset className="grid gap-2">
                                <legend className="mb-2 text-sm leading-none font-medium">
                                    Unidades preferidas
                                </legend>
                                <div className="grid grid-cols-2 gap-3">
                                    {(
                                        [
                                            {
                                                value: 'kg',
                                                label: 'Kilogramos (kg)',
                                            },
                                            { value: 'lb', label: 'Libras (lb)' },
                                        ] as const
                                    ).map((unit) => (
                                        <label
                                            key={unit.value}
                                            className="flex cursor-pointer items-center justify-center rounded-xl border border-sidebar-border/70 px-3 py-2.5 text-sm font-medium transition-colors hover:bg-muted/50 has-checked:border-primary has-checked:bg-primary/5 dark:border-sidebar-border"
                                        >
                                            <input
                                                type="radio"
                                                name="preferred_units"
                                                value={unit.value}
                                                checked={units === unit.value}
                                                onChange={() =>
                                                    setUnits(unit.value)
                                                }
                                                className="sr-only"
                                            />
                                            {unit.label}
                                        </label>
                                    ))}
                                </div>
                                <InputError message={errors.preferred_units} />
                            </fieldset>

                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="weight">
                                        Peso ({units})
                                    </Label>
                                    <Input
                                        id="weight"
                                        type="number"
                                        name="weight"
                                        step="0.1"
                                        min="1"
                                        defaultValue={
                                            fitnessProfile.weight ?? ''
                                        }
                                        placeholder={
                                            units === 'kg' ? '75.0' : '165.0'
                                        }
                                    />
                                    <InputError message={errors.weight} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="height_cm">
                                        Altura (cm)
                                    </Label>
                                    <Input
                                        id="height_cm"
                                        type="number"
                                        name="height_cm"
                                        min="50"
                                        max="300"
                                        defaultValue={
                                            fitnessProfile.height_cm ?? ''
                                        }
                                        placeholder="175"
                                    />
                                    <InputError message={errors.height_cm} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-fitness-profile-button"
                                >
                                    Guardar
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Fitness.layout = {
    breadcrumbs: [
        {
            title: 'Perfil fitness',
            href: edit(),
        },
    ],
};
