<?php

namespace App\Services;

use App\Models\IntegrationCredential;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class GoogleDriveTokenStore
{
    private const SERVICE = 'google_drive';
    private const REFRESH_TOKEN_KEY = 'refresh_token';

    public function getRefreshToken(): ?string
    {
        try {
            if (!Schema::hasTable('integration_credentials')) {
                return config('filesystems.disks.google.refreshToken');
            }

            $credential = IntegrationCredential::query()
                ->where('service', self::SERVICE)
                ->where('key', self::REFRESH_TOKEN_KEY)
                ->first();

            return $credential?->value ?: config('filesystems.disks.google.refreshToken');
        } catch (QueryException) {
            return config('filesystems.disks.google.refreshToken');
        }
    }

    public function saveRefreshToken(string $refreshToken): IntegrationCredential
    {
        if (!Schema::hasTable('integration_credentials')) {
            throw new \RuntimeException(
                'La tabla integration_credentials no existe. Ejecuta php artisan migrate antes de guardar credenciales de Google Drive.'
            );
        }

        return IntegrationCredential::query()->updateOrCreate(
            [
                'service' => self::SERVICE,
                'key' => self::REFRESH_TOKEN_KEY,
            ],
            [
                'value' => $refreshToken,
            ]
        );
    }
}
