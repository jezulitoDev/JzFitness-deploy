<?php

namespace App\Services;

use App\Enums\SportType;
use App\Models\GymSet;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class StatisticsService
{
    /**
     * @return array{
     *     gym_sessions: int,
     *     strava_runs: int,
     *     strava_rides: int,
     *     strava_walks: int,
     *     weekly_volume: float,
     *     training_time_minutes: int,
     *     active_streak_days: int
     * }
     */
    public function weeklySummary(User $user): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $gymSessions = $user->gymSessions()
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->count();

        $stravaActivities = $user->stravaActivities()
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->get();

        $stravaRuns = $stravaActivities->filter(
            fn ($a) => SportType::tryFromStrava($a->sport_type) === SportType::Run,
        )->count();

        $stravaRides = $stravaActivities->filter(
            fn ($a) => SportType::tryFromStrava($a->sport_type) === SportType::Ride,
        )->count();

        $stravaWalks = $stravaActivities->filter(
            fn ($a) => in_array(SportType::tryFromStrava($a->sport_type), [SportType::Walk, SportType::Hike], true),
        )->count();

        $weeklyVolume = $this->weeklyVolume($user, $startOfWeek, $endOfWeek);

        $gymMinutes = (int) $user->gymSessions()
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->whereNotNull('ended_at')
            ->get()
            ->sum(fn ($session) => (int) Carbon::parse($session->started_at)
                ->diffInMinutes(Carbon::parse($session->ended_at)));

        $stravaSeconds = (int) $stravaActivities->sum('moving_time');
        $trainingTimeMinutes = $gymMinutes + (int) floor($stravaSeconds / 60);

        return [
            'gym_sessions' => $gymSessions,
            'strava_runs' => $stravaRuns,
            'strava_rides' => $stravaRides,
            'strava_walks' => $stravaWalks,
            'weekly_volume' => $weeklyVolume,
            'training_time_minutes' => $trainingTimeMinutes,
            'active_streak_days' => $this->activeStreakDays($user),
        ];
    }

    public function weeklyVolume(User $user, ?CarbonInterface $start = null, ?CarbonInterface $end = null): float
    {
        $start ??= now()->startOfWeek();
        $end ??= now()->endOfWeek();

        $sessionIds = $user->gymSessions()
            ->whereBetween('started_at', [$start, $end])
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return 0.0;
        }

        return (float) GymSet::query()
            ->where('completed', true)
            ->whereHas('gymSessionExercise', function ($query) use ($sessionIds): void {
                $query->whereIn('gym_session_id', $sessionIds);
            })
            ->selectRaw('COALESCE(SUM(weight * reps), 0) as volume')
            ->value('volume');
    }

    public function activeStreakDays(User $user): int
    {
        $gymDates = $user->gymSessions()
            ->get()
            ->map(fn ($s) => $s->started_at->toDateString());

        $stravaDates = $user->stravaActivities()
            ->get()
            ->map(fn ($a) => $a->started_at->toDateString());

        $dates = collect($gymDates)->merge($stravaDates)->unique()->sortDesc()->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $mostRecent = Carbon::parse($dates->first())->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        if ($mostRecent->lt($yesterday)) {
            return 0;
        }

        $streak = 0;
        $expected = now()->startOfDay();

        foreach ($dates as $date) {
            $activityDate = Carbon::parse($date)->startOfDay();

            if ($activityDate->equalTo($expected)) {
                $streak++;
                $expected = $expected->copy()->subDay();
            } elseif ($activityDate->lt($expected)) {
                break;
            }
        }

        return $streak;
    }

    /**
     * @return list<array{date: string, type: string, label: string}>
     */
    public function recentActivityDays(User $user, int $limit = 7): array
    {
        $days = [];

        for ($i = 0; $i < $limit; $i++) {
            $date = now()->subDays($i)->toDateString();

            $hasGym = $user->gymSessions()
                ->whereDate('started_at', $date)
                ->exists();

            if ($hasGym) {
                $days[] = ['date' => $date, 'type' => 'gym', 'label' => 'Gym'];
            }

            $stravaTypes = $user->stravaActivities()
                ->whereDate('started_at', $date)
                ->pluck('sport_type')
                ->unique();

            foreach ($stravaTypes as $sportType) {
                $enum = SportType::tryFromStrava($sportType);
                $days[] = ['date' => $date, 'type' => $enum->icon(), 'label' => $enum->label()];
            }
        }

        return $days;
    }
}
