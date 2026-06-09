<?php

use App\Models\GymSession;
use App\Models\StravaActivity;
use App\Models\User;

test('guests cannot access calendar', function () {
    $this->get(route('calendar.index'))->assertRedirect(route('login'));
});

test('calendar shows gym and strava events for the month', function () {
    $user = User::factory()->create();
    $date = now()->startOfMonth()->addDays(3);

    GymSession::factory()->for($user)->create([
        'started_at' => $date,
        'ended_at' => $date->copy()->addHour(),
    ]);

    StravaActivity::factory()->for($user)->create([
        'started_at' => $date,
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', [
            'year' => $date->year,
            'month' => $date->month,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calendar/index')
            ->has('events'));
});
