<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleStatsIo\FilamentPlugin\SimplestatsApiClient;

beforeEach(function () {
    $this->client = new SimplestatsApiClient(
        apiToken: 'test-token',
        apiUrl: 'https://simplestats.io/api/v1',
        cacheTtl: 60,
    );

    Cache::flush();
});

it('sends correct authorization header', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->client->getStats(['preset' => 'last_7_days']);

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer test-token');
    });
});

it('sends preset as query parameter', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->client->getStats(['preset' => 'last_30_days']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'preset=last_30_days');
    });
});

it('returns stats data from api', function () {
    $responseData = [
        'data' => [
            ['date' => '2026-04-11', 'pd_visitor' => 100, 'pd_reg' => 5],
            ['date' => '2026-04-12', 'pd_visitor' => 120, 'pd_reg' => 8],
        ],
        'meta' => ['range_type' => 'day'],
    ];

    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response($responseData),
    ]);

    $result = $this->client->getStats();

    expect($result)
        ->toHaveKey('data')
        ->toHaveKey('meta')
        ->and($result['data'])->toHaveCount(2)
        ->and($result['data'][0]['pd_visitor'])->toBe(100);
});

it('sends stats_type for grouped stats', function () {
    Http::fake([
        'simplestats.io/api/v1/stats/grouped*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->client->getGroupedStats('track_source', ['preset' => 'last_7_days']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'stats_type=track_source')
            && str_contains($request->url(), 'preset=last_7_days');
    });
});

it('returns groups data', function () {
    Http::fake([
        'simplestats.io/api/v1/stats/groups*' => Http::response([
            'data' => [
                ['id' => 1, 'name' => 'Google'],
                ['id' => 2, 'name' => 'Facebook'],
            ],
        ]),
    ]);

    $result = $this->client->getGroups('track_source');

    expect($result['data'])->toHaveCount(2)
        ->and($result['data'][0]['name'])->toBe('Google');
});

it('caches responses', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response(['data' => [['pd_visitor' => 50]], 'meta' => []]),
    ]);

    $this->client->getStats(['preset' => 'today']);
    $this->client->getStats(['preset' => 'today']);

    Http::assertSentCount(1);
});

it('uses different cache keys for different filters', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->client->getStats(['preset' => 'today']);
    $this->client->getStats(['preset' => 'last_7_days']);

    Http::assertSentCount(2);
});

it('returns empty response on connection error', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => fn () => throw new ConnectionException('Timeout'),
    ]);

    $result = $this->client->getStats();

    expect($result)
        ->toHaveKey('data')
        ->toHaveKey('meta')
        ->and($result['data'])->toBeEmpty();
});

it('returns empty response on 401', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response(['message' => 'Unauthenticated'], 401),
    ]);

    $result = $this->client->getStats();

    expect($result['data'])->toBeEmpty();
});

it('returns empty response on 500', function () {
    Http::fake([
        'simplestats.io/api/v1/stats*' => Http::response('Server Error', 500),
    ]);

    $result = $this->client->getStats();

    expect($result['data'])->toBeEmpty();
});
