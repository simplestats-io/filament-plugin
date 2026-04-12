<?php

namespace SimpleStatsIo\FilamentPlugin;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimplestatsApiClient
{
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

    protected function cachedRequest(string $endpoint, array $params = []): array
    {
        $cacheKey = 'simplestats_'.md5($endpoint.'_'.json_encode($params));

        return Cache::remember($cacheKey, $this->cacheTtl, fn () => $this->request($endpoint, $params));
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
