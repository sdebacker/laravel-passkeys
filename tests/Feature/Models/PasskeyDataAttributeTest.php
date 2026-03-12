<?php

use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\Tests\TestSupport\Models\User;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialSource;

it('can store and retrieve passkey data as CredentialRecord', function () {
    $user = User::factory()->create();
    $passkey = Passkey::factory()->create(['authenticatable_id' => $user->id]);

    $fresh = Passkey::find($passkey->id);

    expect($fresh->data)->toBeInstanceOf(CredentialRecord::class);
    expect($fresh->data->counter)->toBe(100);
    expect($fresh->data->attestationType)->toBe('none');
});

it('can set data with CredentialRecord and save without type error', function () {
    $user = User::factory()->create();
    $passkey = Passkey::factory()->create(['authenticatable_id' => $user->id]);

    $fresh = Passkey::find($passkey->id);
    $data = $fresh->data;

    $fresh->data = $data;
    $fresh->save();

    $reloaded = Passkey::find($passkey->id);
    expect($reloaded->data)->toBeInstanceOf(CredentialRecord::class);
    expect($reloaded->data->counter)->toBe(100);
});

it('can update passkey data and last_used_at together', function () {
    $user = User::factory()->create();
    $passkey = Passkey::factory()->create(['authenticatable_id' => $user->id]);

    $fresh = Passkey::find($passkey->id);

    $fresh->update([
        'data' => $fresh->data,
        'last_used_at' => now(),
    ]);

    expect($fresh->last_used_at)->not->toBeNull();
});

it('accepts PublicKeyCredentialSource in setter', function () {
    $user = User::factory()->create();
    $passkey = Passkey::factory()->create(['authenticatable_id' => $user->id]);

    $fresh = Passkey::find($passkey->id);
    $data = $fresh->data;

    $publicKeySource = new PublicKeyCredentialSource(
        publicKeyCredentialId: $data->publicKeyCredentialId,
        type: $data->type,
        transports: $data->transports,
        attestationType: $data->attestationType,
        trustPath: $data->trustPath,
        aaguid: $data->aaguid,
        credentialPublicKey: $data->credentialPublicKey,
        userHandle: $data->userHandle,
        counter: $data->counter,
    );

    $fresh->data = $publicKeySource;
    $fresh->save();

    $reloaded = Passkey::find($fresh->id);
    expect($reloaded->data)->toBeInstanceOf(CredentialRecord::class);
});
