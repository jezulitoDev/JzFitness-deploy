import { router } from '@inertiajs/react';
import { Check, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { startRestTimer } from '@/components/workout-timer';
import { toggle, update, destroy, store } from '@/routes/gym-sets';
import type { GymSessionExercise, GymSet } from '@/types/fitness';

export function SetTracker({
    sessionExercise,
}: {
    sessionExercise: GymSessionExercise;
}) {
    const sets = sessionExercise.sets ?? [];

    const handleToggle = (set: GymSet) => {
        router.patch(toggle(set.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                if (!set.completed) {
                    startRestTimer(sessionExercise.default_rest_seconds);
                }
            },
        });
    };

    const handleUpdate = (set: GymSet, field: 'weight' | 'reps', value: string) => {
        router.patch(
            update(set.id).url,
            { [field]: value },
            { preserveScroll: true },
        );
    };

    return (
        <div className="space-y-2">
            {sets.map((set, index) => (
                <div key={set.id} className="flex items-center gap-2">
                    <span className="w-6 text-sm text-muted-foreground">
                        {index + 1}
                    </span>
                    <Input
                        type="number"
                        className="h-9 w-20"
                        defaultValue={set.weight}
                        onBlur={(e) =>
                            handleUpdate(set, 'weight', e.target.value)
                        }
                    />
                    <span className="text-muted-foreground">kg x</span>
                    <Input
                        type="number"
                        className="h-9 w-16"
                        defaultValue={set.reps}
                        onBlur={(e) => handleUpdate(set, 'reps', e.target.value)}
                    />
                    <Button
                        type="button"
                        size="icon"
                        variant={set.completed ? 'default' : 'outline'}
                        onClick={() => handleToggle(set)}
                    >
                        <Check className="size-4" />
                    </Button>
                    <Button
                        type="button"
                        size="icon"
                        variant="ghost"
                        onClick={() =>
                            router.delete(destroy(set.id).url, {
                                preserveScroll: true,
                            })
                        }
                    >
                        <Trash2 className="size-4" />
                    </Button>
                </div>
            ))}
            <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() =>
                    router.post(store(sessionExercise.id).url, {}, {
                        preserveScroll: true,
                    })
                }
            >
                <Plus className="mr-1 size-4" />
                Add set
            </Button>
        </div>
    );
}
