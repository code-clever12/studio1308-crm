<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/*
|--------------------------------------------------------------------------
| Stripe test helpers
|--------------------------------------------------------------------------
|
| The real Stripe\StripeClient (bound in the container with a dummy test key
| — see phpunit.xml) resolves real Service objects via magic __get(), which
| Mockery cannot reliably intercept on a concrete class. So instead of mocking
| StripeClient itself, mockStripeHttp() swaps Stripe's HTTP transport layer
| (Stripe\ApiRequestor::setHttpClient) for a Mockery double — the real SDK
| objects still run, but every network call is intercepted and answered with
| a canned JSON response. Reset back to null after each test so a leftover
| mock never leaks into an unrelated test.
|
| stripeWebhookSignature() reproduces Stripe's documented HMAC-SHA256 webhook
| signing scheme offline, so signature verification can be tested without a
| real Stripe account.
|
*/

function mockStripeHttp(): MockInterface
{
    $client = Mockery::mock(ClientInterface::class);
    ApiRequestor::setHttpClient($client);

    return $client;
}

function stripeHttpResponse(array $body, int $status = 200): array
{
    return [json_encode($body), $status, []];
}

function stripeWebhookSignature(string $payload, string $secret, ?int $timestamp = null): string
{
    $timestamp ??= time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

    return "t={$timestamp},v1={$signature}";
}

afterEach(function () {
    ApiRequestor::setHttpClient(null);
});
