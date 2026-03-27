<?php

use Rawand\FilamentReveal\Support\RevealTokenGenerator;
use Rawand\FilamentReveal\Concerns\HasRevealableColumns;

it('generates a valid encrypted token', function () {
    $token = RevealTokenGenerator::generate('1', 'email', 'App\\Models\\User');
    expect($token)->toBeString()->not->toBeEmpty();
});

it('decodes a valid token successfully', function () {
    $token = RevealTokenGenerator::generate('1', 'email', 'App\\Models\\User');
    $payload = RevealTokenGenerator::decode($token);
    expect($payload)->toBeArray()
        ->and($payload['r'])->toBe('1')
        ->and($payload['c'])->toBe('email')
        ->and($payload['m'])->toBe('App\\Models\\User');
});

it('returns null for a tampered token', function () {
    $payload = RevealTokenGenerator::decode('tampered.invalid.token');
    expect($payload)->toBeNull();
});

it('generates a consistent obfuscated endpoint', function () {
    $endpoint1 = RevealTokenGenerator::generateEndpoint();
    $endpoint2 = RevealTokenGenerator::generateEndpoint();
    expect($endpoint1)->toBe($endpoint2);
});

it('model trait enforces column whitelist', function () {
    $model = new class {
        use HasRevealableColumns;
        protected array $revealableColumns = ['email', 'api_token'];
    };

    expect($model->isColumnRevealable('email'))->toBeTrue()
        ->and($model->isColumnRevealable('api_token'))->toBeTrue()
        ->and($model->isColumnRevealable('password'))->toBeFalse();
});
