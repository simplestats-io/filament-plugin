<?php

return [

    /*
     |--------------------------------------------------------------------------
     | SimpleStats API Credentials
     |--------------------------------------------------------------------------
     |
     | Define your API credentials here. The API URL defaults to the hosted
     | SimpleStats instance. If you are self-hosting, change it to your own
     | URL. The API token is the same one used by the Laravel client package.
     |
     */

    'api_url' => env('SIMPLESTATS_API_URL', 'https://simplestats.io/api/v1'),

    'api_token' => env('SIMPLESTATS_API_TOKEN'),

    /*
     |--------------------------------------------------------------------------
     | Cache Duration
     |--------------------------------------------------------------------------
     |
     | API responses are cached to avoid unnecessary requests on every page
     | load and Livewire re-render. Multiple widgets sharing the same filters
     | will reuse a single cached response. Set to 0 to disable caching.
     |
     */

    'cache_ttl' => 60,

];
