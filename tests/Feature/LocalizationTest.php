<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('localizes api messages based on Accept-Language', function (): void {
    $this->withHeaders(['Accept-Language' => 'es'])
        ->postJson('/v1/auth/password/forgot', [
            'email' => 'unknown@example.com',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Content-Language', 'es')
        ->assertJsonPath('message', 'Si la cuenta existe, se ha enviado un enlace para restablecer la contraseña.');
});

it('falls back to the default locale for unsupported languages', function (): void {
    $this->withHeaders(['Accept-Language' => 'fr'])
        ->postJson('/v1/auth/password/forgot', [
            'email' => 'unknown@example.com',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Content-Language', 'en');
});

it('localizes responses in brazilian portuguese', function (): void {
    $this->withHeaders(['Accept-Language' => 'pt-BR'])
        ->postJson('/v1/auth/password/forgot', [
            'email' => 'unknown@example.com',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Content-Language', 'pt_BR')
        ->assertJsonPath('message', 'Se a conta existir, um link de redefinição de senha foi enviado.');
});

it('localizes validation errors in brazilian portuguese', function (): void {
    $this->withHeaders(['Accept-Language' => 'pt-BR'])
        ->postJson('/v1/auth/login', [])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('errors.email.0', 'O campo email é obrigatório.');
});

it('localizes validation errors', function (): void {
    $this->withHeaders(['Accept-Language' => 'es'])
        ->postJson('/v1/auth/login', [])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('errors.email.0', 'El campo email es obligatorio.');
});
