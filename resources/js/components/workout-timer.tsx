import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { useTimerStore } from '@/stores/timer';

function formatTime(seconds: number): string {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;

    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

export function WorkoutTimer() {
    const { secondsRemaining, isRunning, start, stop, tick, defaultRestSeconds } =
        useTimerStore();

    useEffect(() => {
        if (!isRunning) {
            return;
        }

        const interval = setInterval(() => tick(), 1000);

        return () => clearInterval(interval);
    }, [isRunning, tick]);

    return (
        <div className="flex items-center gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
            <div className="font-mono text-3xl font-semibold tabular-nums">
                {formatTime(secondsRemaining)}
            </div>
            <div className="flex gap-2">
                {!isRunning ? (
                    <Button
                        type="button"
                        size="sm"
                        onClick={() => start()}
                    >
                        {defaultRestSeconds}s
                    </Button>
                ) : (
                    <Button type="button" size="sm" variant="outline" onClick={stop}>
                        Stop
                    </Button>
                )}
            </div>
        </div>
    );
}

export function startRestTimer(seconds?: number): void {
    useTimerStore.getState().start(seconds);
}
