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

/**
 * A throwaway RSA private key (PEM) for PushNotificationService tests — it
 * signs a real JWT with PHP's openssl extension, so a structurally valid
 * key is needed, but it never talks to a real Google/Firebase account.
 *
 * Static/pre-generated (via the `openssl` CLI) rather than created at test
 * time with openssl_pkey_new(): that function requires a working openssl.cnf,
 * which this XAMPP PHP install can't locate (a config-path issue specific to
 * key *generation* — openssl_sign() against an existing key, which is all
 * PushNotificationService actually does, works fine regardless).
 */
function generateTestRsaPrivateKey(): string
{
    return <<<'PEM'
    -----BEGIN PRIVATE KEY-----
    MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCPSy24xxZQ3BT5
    KrgbN6+UsimnGd0AGmZjWRwge4y5lJp4qKEQdEVnyKSmCAbdla7d/8JW9RBqO2yo
    WlLIenp+rjJ2iwjIdnOCAork0OYy63JdovXYsDMpOEwP1U6Qenifz7LUGGrtjy3m
    7BwKDgPxKi+WNx4gRu+i3+i5DVhHdqj8dTfZ4y5bcVDxSTRgqSp7agM9Eg9RDTOa
    gfChF0L81ewWi9Uzooc3bRTUOENZzwpcZnhJKan9pqeNK5sD5sI32E2vGu2cANJW
    LH00blowWckmgMl71bSRfJCCYujPPp2MY4Xj7ZokY7M+CujLTYOCqYf6cYBvYuea
    McQN/w7DAgMBAAECggEABmpPiAWQ+02T7ZqnttkI4trd7SD5MuJ7sZ7EZbRL8H91
    mC/qIH71ubff0Z3rx/esf9YazZYK+WT8sEYO877/OFtOX6rjQBPrIVrbL6a/z6LL
    5Xyz9Gx7D34TDBhsL1RUWmJ/N2pHPnVNERjo1Nub+iv91Z27gXPVK1EXLbj27VTf
    aF/jRgkQ8ZjzZ8MV78k+CkMHtqvfzNg9PxuqFplyqn+t2+rohENRIDXZYg/dlnv4
    PvQK3K4rpv/acl1T9B2OwSlF72+D6ynErt8qFCb+VPkc3VDbRSStJ/E05BOJ3XNs
    BYD3odyLUNN/buw3uAObgc+cyEIkPIfi+aPmxz0VwQKBgQDHO/30DcMDXc0lXHi+
    jPvSzSmV6FsuAQCBtDC0MMuftNQ3j42xMSbUpI2JU3wIdlNmlqFCX7pgwiOR4W6L
    7p8hALvMCAiU//35V9Jd3hp2axRbBEpZf/OlOi52WDb62u351tXhjVZT30t8jWWF
    9IUsBSRDnNTX0oE66DC3olyTwQKBgQC4HuoQZ7cImZwXNRNjyTn1/stvkHtMtPxP
    ZiwjQvCWOYB08tYkblav5sidwSx/tzmLl/TZA8651MQbpVqhdWJ2Mw1DonXgSPEN
    p3So0MJbtQ5oOW2SZ26Vr9UFyN4vrAMaVzX/Cm65A83lzrvth1kULOZiskBC7On0
    PyRAkzAzgwKBgHv6ewd54Nahcl1Dc+evt6zv+mbMlqDEAtqzlHMswil5z21bqWKQ
    IYfFdkX0a4SgjTmTlnnFNCet55CJ5c6yCNfyqupGPYDq49Jbsg6zfWgfLiWNP+79
    zlINKIiAUIY94kTMSX/4AbZjh4fUPU7kYFKKSIzCdThwR/auMlKzeESBAoGAZQKA
    wrNDlPJA0fKKSqowpCY+kVwgaLq52Q1ilY2505CESqSWmrH12NaxpOo/cWmhplWd
    PBl8pjJ1y/zxNbbiZ5omYr6UEJGzvRvrQloU5p7z4nXvCOy5nGE0atICIwEpSqAh
    vWOJzvKpWLQMIYmpcwoW8np8CrtStm6Vgt+9B+UCgYEAobspNTo7fgooo8BqaWzK
    BesEd/ehz5S2b2b2mzJhKc1YNdECJsElgeywwYGM8mWJngrkrqI3vMb8jw/CY5iH
    LU44UJ2yS4GxTs8ICa4+E3YWCmHdNWe/HFHWfqAu3vDWf7L/USzRh1xamXpkC2IX
    IHLMWmoi1pTQMZqZjW/GwGI=
    -----END PRIVATE KEY-----
    PEM;
}

afterEach(function () {
    ApiRequestor::setHttpClient(null);
});
