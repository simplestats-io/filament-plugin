<?php

namespace SimpleStatsIo\FilamentPlugin;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimplestatsApiClient
{
    public const DEFAULT_GROUPED_TYPES = [
        'track_referer',
        'track_source',
        'location_country',
        'page_entry',
    ];

    protected array $memo = [];

    public function __construct(
        protected string $apiToken,
        protected string $apiUrl = 'https://simplestats.io/api/v1',
        protected int $cacheTtl = 60,
    ) {}

    /**
     * @return array{data: array, meta: array, data_previous?: array}
     */
    public function getStats(array $filters = []): array
    {
        return $this->cachedRequest('stats', $filters);
    }

    /**
     * @return array{data: array, meta: array}
     */
    public function getGroupedStats(string $statsType, array $filters = []): array
    {
        return $this->cachedRequest('stats/grouped', [
            'stats_type' => $statsType,
            ...$filters,
        ]);
    }

    /**
     * @return array{data: array}
     */
    public function getGroups(string $statsType): array
    {
        return $this->cachedRequest('stats/groups', [
            'stats_type' => $statsType,
        ]);
    }

    /**
     * Bundle stats + grouped stats for the standard dashboard widgets in a single call.
     * Subsequent calls to getStats / getGroupedStats with the same filters hit the
     * in-memory memo populated here, so the dashboard renders without redundant work.
     *
     * @return array{stats: array, grouped: array<string, array>}
     */
    public function getAll(array $filters = [], array $groupedTypes = self::DEFAULT_GROUPED_TYPES): array
    {
        $grouped = [];
        foreach ($groupedTypes as $type) {
            $grouped[$type] = $this->getGroupedStats($type, $filters);
        }

        return [
            'stats' => $this->getStats($filters),
            'grouped' => $grouped,
        ];
    }

    protected function cachedRequest(string $endpoint, array $params = []): array
    {
        $cacheKey = 'simplestats_'.md5($endpoint.'_'.json_encode($params));

        if (isset($this->memo[$cacheKey])) {
            return $this->memo[$cacheKey];
        }

        return $this->memo[$cacheKey] = Cache::remember($cacheKey, $this->cacheTtl, function () use ($endpoint, $params, $cacheKey) {
            $response = $this->request($endpoint, $params);

            if (empty($response['data']) && empty($response['meta'])) {
                Cache::forget($cacheKey);
            }

            return $response;
        });
    }

    protected function request(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->httpClient()->get($endpoint, $params);

            if ($response->failed()) {
                Log::warning('SimpleStats API request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                ]);

                return $this->emptyResponse();
            }

            return $response->json() ?? $this->emptyResponse();
        } catch (ConnectionException $e) {
            Log::warning('SimpleStats API connection failed', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return $this->emptyResponse();
        }
    }

    protected function httpClient(): PendingRequest
    {
        return Http::withToken($this->apiToken)
            ->acceptJson()
            ->timeout(10)
            ->baseUrl($this->apiUrl);
    }

    protected function emptyResponse(): array
    {
        return ['data' => [], 'meta' => []];
    }
}
