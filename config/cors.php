<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Only /api/* needs this — the lead-capture endpoint is meant to be
    | called from external static landing pages (GitHub Pages, etc.) via
    | browser JS fetch(), which the same-origin policy would otherwise block.
    | A wildcard origin is safe here specifically because the endpoint is
    | public/stateless (no cookies, no session, supports_credentials stays
    | false) — CORS only gates browser-JS cross-origin calls anyway, not
    | server-to-server ones, so it isn't an access-control boundary for this
    | endpoint. Restrict CORS_ALLOWED_ORIGINS in .env to specific domains if
    | you'd rather lock it down once your landing pages' domains are final.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
