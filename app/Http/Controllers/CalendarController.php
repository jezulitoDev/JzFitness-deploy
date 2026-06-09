<?php

namespace App\Http\Controllers;

use App\Services\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(Request $request, CalendarService $calendar): Response
    {
        $view = $request->string('view')->toString() === 'week' ? 'week' : 'month';
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        if ($view === 'week') {
            $weekStart = Carbon::parse(
                $request->string('week_start', now()->startOfWeek()->toDateString())->toString(),
            )->startOfWeek();

            $events = $calendar->weekEvents($request->user(), $weekStart);
        } else {
            $weekStart = now()->startOfWeek();
            $events = $calendar->monthEvents($request->user(), $year, $month);
        }

        return Inertia::render('calendar/index', [
            'view' => $view,
            'year' => $year,
            'month' => $month,
            'weekStart' => $weekStart->toDateString(),
            'events' => $events,
            'workoutPlans' => $request->user()
                ->workoutPlans()
                ->whereNull('archived_at')
                ->with('days:id,workout_plan_id,name,order')
                ->get(['id', 'name']),
        ]);
    }
}
