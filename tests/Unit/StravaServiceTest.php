<?php

use App\Exceptions\StravaAuthorizationException;
use App\Models\StravaAccount;
use App\Models\User;
use App\Services\StravaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.strava.client_id' => 'test-client-id',
        'services.strava.client_secret' => 'test-client-secret',
    ]);
});

test('is configured when client id and secret are present', function () {
    expect(app(StravaService::class)->isConfigured())->toBeTrue();
});

test('is not configured when credentials are missing', function () {
    config([
        'services.strava.client_id' => null,
        'services.strava.client_secret' => null,
    ]);

    expect(app(StravaService::class)->isConfigured())->toBeFalse();
});

test('needs refresh when token expires within one hour', function () {
    $account = StravaAccount::factory()->create([
        'expires_at' => now()->addMinutes(30),
    ]);

    expect($account->needsRefresh())->toBeTrue()
        ->and($account->isExpired())->toBeFalse();
});

test('proactive refresh runs before syncing activities', function () {
    $user = User::factory()->create();
    $account = StravaAccount::factory()->for($user)->create([
        'expires_at' => now()->addMinutes(20),
        'refresh_token' => 'old-refresh-token',
        'access_token' => 'old-access-token',
    ]);

    Http::fake([
        'https://www.strava.com/oauth/token' => Http::response([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_at' => now()->addHours(6)->timestamp,
            'expires_in' => 21600,
        ]),
        'https://www.strava.com/api/v3/athlete/activities*' => Http::response([]),
    ]);

    app(StravaService::class)->syncActivities($user);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://www.strava.com/oauth/token'
            && $request['grant_type'] === 'refresh_token'
            && $request['refresh_token'] === 'old-refresh-token';
    });

    $account->refresh();

    expect($account->access_token)->toBe('new-access-token')
        ->and($account->refresh_token)->toBe('new-refresh-token');
});

test('401 on activities triggers refresh and retry', function () {
    $user = User::factory()->create();
    StravaAccount::factory()->for($user)->create([
        'expires_at' => now()->addHours(3),
        'refresh_token' => 'refresh-token',
        'access_token' => 'stale-access-token',
    ]);

    Http::fake([
        'https://www.strava.com/api/v3/athlete/activities*' => Http::sequence()
            ->push([], 401)
            ->push([], 200),
        'https://www.strava.com/oauth/token' => Http::response([
            'access_token' => 'fresh-access-token',
            'refresh_token' => 'rotated-refresh-token',
            'expires_at' => now()->addHours(6)->timestamp,
            'expires_in' => 21600,
        ]),
    ]);

    app(StravaService::class)->syncActivities($user);

    Http::assertSentCount(3);

    expect($user->fresh()->stravaAccount->access_token)->toBe('fresh-access-token')
        ->and($user->fresh()->stravaAccount->refresh_token)->toBe('rotated-refresh-token');
});

test('failed refresh throws authorization exception', function () {
    $user = User::factory()->create();
    StravaAccount::factory()->for($user)->create([
        'expires_at' => now()->subMinute(),
        'refresh_token' => 'invalid-refresh',
    ]);

    Http::fake([
        'https://www.strava.com/oauth/token' => Http::response(['message' => 'invalid'], 400),
    ]);

    expect(fn () => app(StravaService::class)->syncActivities($user))
        ->toThrow(StravaAuthorizationException::class);
});

test('mark account disconnected removes strava account', function () {
    $user = User::factory()->create();
    StravaAccount::factory()->for($user)->create();

    app(StravaService::class)->markAccountDisconnected($user, 'test');

    expect($user->fresh()->stravaAccount)->toBeNull();
});
