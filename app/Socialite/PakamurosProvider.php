<?php

namespace App\Socialite;

use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class PakamurosProvider extends AbstractProvider
{
    protected $scopes = [];

    protected function baseUrl(): string
    {
        return rtrim(config('services.pakamuros.url'), '/');
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            $this->baseUrl() . '/oauth/authorize',
            $state
        );
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl() . '/oauth/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl() . '/api/oauth/user',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        Log::info('Pakamuros /api/oauth/user respuesta', ['data' => $data]);

        return $data['data'] ?? $data;
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['nombre_completo'],
            'email' => $user['email'],
            'nickname' => $user['usuario'],
        ]);
    }
}
