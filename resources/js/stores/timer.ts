import { create } from 'zustand';
import { persist } from 'zustand/middleware';

type TimerState = {
    secondsRemaining: number;
    isRunning: boolean;
    defaultRestSeconds: number;
    start: (seconds?: number) => void;
    tick: () => void;
    stop: () => void;
    setDefaultRestSeconds: (seconds: number) => void;
};

export const useTimerStore = create<TimerState>()(
    persist(
        (set, get) => ({
            secondsRemaining: 0,
            isRunning: false,
            defaultRestSeconds: 90,
            start: (seconds) => {
                const duration = seconds ?? get().defaultRestSeconds;
                set({ secondsRemaining: duration, isRunning: true });
            },
            tick: () => {
                const { secondsRemaining, isRunning } = get();

                if (!isRunning) {
                    return;
                }

                if (secondsRemaining <= 1) {
                    set({ secondsRemaining: 0, isRunning: false });

                    if (typeof window !== 'undefined') {
                        const audio = new Audio(
                            'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIGWi77+efTRAMUKfj8LZjHAY4kdfyzHksBSR3x/DdkEAKFF606euoVRQKRp/g8r5sIQUrgc7y2Yk2CBlou+/nn00QDFCn4/C2YxwGOJHX8sx5LAUkd8fw3ZBAC',
                        );
                        audio.play().catch(() => {});
                    }

                    return;
                }

                set({ secondsRemaining: secondsRemaining - 1 });
            },
            stop: () => set({ isRunning: false, secondsRemaining: 0 }),
            setDefaultRestSeconds: (seconds) =>
                set({ defaultRestSeconds: seconds }),
        }),
        { name: 'jzfitness-rest-timer' },
    ),
);
