<?php

use App\Models\StravaAccount;
use App\Models\StravaActivity;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    config([
        'services.strava.client_id' => 'test-client-id',
        'services.strava.client_secret' => 'test-client-secret',
    ]);
});

test('guests cannot access strava page', function () {
    $this->get(route('strava.index'))->assertRedirect(route('login'));
});

test('authenticated users can view strava page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('strava.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('strava/index')
            ->where('stravaConfigured', true)
            ->where('account', null)
            ->where('needsReconnect', false));
});

test('strava page shows not configured when credentials are missing', function () {
    config([
        'services.strava.client_id' => null,
        'services.strava.client_secret' => null,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('strava.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('strava/index')
            ->where('stravaConfigured', false)
            ->where('account', null));
});

test('connect redirects with error when strava is not configured', function () {
    config([
        'services.strava.client_id' => '',
        'services.strava.client_secret' => '',
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('strava.connect'))
        ->assertRedirect(route('strava.index'));
});

test('strava page includes server formatted activity dates', function () {
    $user = User::factory()->create();
    $activity = StravaActivity::factory()->for($user)->create([
        'started_at' => '2026-05-30 08:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('strava.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('strava/index')
            ->has('activities', 1)
            ->where('activities.0.started_at_label', '30/05/2026')
            ->where('activities.0.id', $activity->id));
});

test('authenticated users see token expiry when connected', function () {
    $user = User::factory()->create();
    StravaAccount::factory()->for($user)->create([
        'expires_at' => now()->addHours(2),
    ]);

    $this->actingAs($user)
        ->get(route('strava.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('strava/index')
            ->has('account')
            ->has('tokenExpiresAt')
            ->where('tokenExpiresInMinutes', fn ($minutes) => $minutes > 0));
});

test('authenticated users can disconnect strava', function () {
    $user = User::factory()->create();
    StravaAccount::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('strava.disconnect'))
        ->assertRedirect(route('strava.index'));

    expect($user->fresh()->stravaAccount)->toBeNull();
});

test('sync shows error flash when refresh fails', function () {
    $user = User::factory()->create();
    StravaAccount::factory()->for($user)->create([
        'expires_at' => now()->subMinute(),
        'refresh_token' => 'bad-token',
    ]);

    Http::fake([
        'https://www.strava.com/oauth/token' => Http::response([], 401),
    ]);

    $this->actingAs($user)
        ->post(route('strava.sync'))
        ->assertRedirect(route('strava.index'));

    expect($user->fresh()->stravaAccount)->toBeNull();
});

test('callback connects account and syncs activities', function () {
    $user = User::factory()->create();

    Socialite::fake('strava', (new SocialiteUser)->map([
        'id' => 12345,
        'name' => 'Test Athlete',
    ])->setToken('oauth-access-token')
        ->setRefreshToken('oauth-refresh-token')
        ->setExpiresIn(21600));

    Http::fake([
        'https://www.strava.com/api/v3/athlete/activities*' => Http::response([
            [
                'id' => 9001,
                'name' => 'Morning Run',
                'sport_type' => 'Run',
                'type' => 'Run',
                'distance' => 5000,
                'moving_time' => 1800,
                'elapsed_time' => 1900,
                'total_elevation_gain' => 50,
                'start_date' => now()->toIso8601String(),
            ],
        ]),
    ]);

    $this->actingAs($user)
        ->get(route('strava.callback'))
        ->assertRedirect(route('strava.index'));

    expect($user->fresh()->stravaAccount)
        ->not->toBeNull()
        ->strava_id->toBe(12345);

    expect(StravaActivity::query()->where('user_id', $user->id)->count())->toBe(1);
});
