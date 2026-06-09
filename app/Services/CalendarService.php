<?php

namespace App\Services;

use App\Enums\SportType;
use App\Models\User;
use Illuminate\Support\Carbon;

class CalendarService
{
    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function monthEvents(User $user, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->eventsBetween($user, $start, $end);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function weekEvents(User $user, Carbon $weekStart): array
    {
        $start = $weekStart->copy()->startOfDay();
        $end = $weekStart->copy()->addDays(6)->endOfDay();

        return $this->eventsBetween($user, $start, $end);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function eventsBetween(User $user, Carbon $start, Carbon $end): array
    {
        $events = [];

        $scheduledWorkouts = $user->scheduledWorkouts()
            ->with(['workoutPlan:id,name', 'workoutPlanDay:id,name'])
            ->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('scheduled_date')
            ->get();

        foreach ($scheduledWorkouts as $scheduled) {
            $date = $scheduled->scheduled_date->toDateString();
            $events[$date][] = [
                'type' => 'planned',
                'label' => 'Planificado',
                'id' => $scheduled->id,
                'name' => $scheduled->displayName(),
                'completed' => $scheduled->isCompleted(),
                'workout_plan_id' => $scheduled->workout_plan_id,
                'workout_plan_day_id' => $scheduled->workout_plan_day_id,
                'notes' => $scheduled->notes,
            ];
        }

        $gymSessions = $user->gymSessions()
            ->whereBetween('started_at', [$start, $end])
            ->get(['id', 'started_at', 'notes']);

        foreach ($gymSessions as $session) {
            $date = $session->started_at->toDateString();
            $events[$date][] = [
                'type' => 'gym',
                'label' => 'Gym',
                'id' => $session->id,
                'name' => $session->notes ?? 'Entrenamiento',
            ];
        }

        $stravaActivities = $user->stravaActivities()
            ->whereBetween('started_at', [$start, $end])
            ->get(['id', 'name', 'sport_type', 'started_at']);

        foreach ($stravaActivities as $activity) {
            $date = $activity->started_at->toDateString();
            $sport = SportType::tryFromStrava($activity->sport_type);
            $events[$date][] = [
                'type' => $sport->icon(),
                'label' => $sport->label(),
                'id' => $activity->id,
                'name' => $activity->name,
            ];
        }

        ksort($events);

        return $events;
    }
}
