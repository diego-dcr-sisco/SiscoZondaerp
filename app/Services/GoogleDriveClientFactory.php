<?php

namespace App\Services;

use App\Exceptions\GoogleDriveAuthException;
use Google\Client as GoogleClient;
use Google\Service\Drive;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleDriveClientFactory
{
    public function makeClient(?array $config = null): GoogleClient
    {
        $config ??= config('filesystems.disks.google');

        $client = new GoogleClient();
        $client->setHttpClient(new GuzzleClient([
            'connect_timeout' => 10,
            'timeout' => 30,
            'read_timeout' => 30,
        ]));
        $client->setClientId($config['clientId'] ?? null);
        $client->setClientSecret($config['clientSecret'] ?? null);
        $client->addScope(Drive::DRIVE);
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');

        return $client;
    }

    public function makeAuthenticatedClient(?array $config = null): GoogleClient
    {
        $config ??= config('filesystems.disks.google');

        $client = $this->makeClient($config);
        $refreshToken = app(GoogleDriveTokenStore::class)->getRefreshToken();

        if (empty($refreshToken)) {
            Log::warning('Google Drive disk loaded without refresh token.');

            return $client;
        }

        $client->setAccessToken($this->getAccessToken($client, $refreshToken));

        return $client;
    }

    private function getAccessToken(GoogleClient $client, string $refreshToken): array
    {
        $tokenHash = hash('sha256', $refreshToken);
        $cacheKey = "google_drive_access_token:{$tokenHash}";
        $lockKey = "google_drive_access_token_lock:{$tokenHash}";

        $cachedToken = Cache::get($cacheKey);

        if (is_array($cachedToken) && !empty($cachedToken['access_token'])) {
            return $cachedToken;
        }

        return Cache::lock($lockKey, 10)->block(10, function () use ($client, $refreshToken, $cacheKey) {
            $cachedToken = Cache::get($cacheKey);

            if (is_array($cachedToken) && !empty($cachedToken['access_token'])) {
                return $cachedToken;
            }

            try {
                $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            } catch (\Throwable $e) {
                Log::error('Google Drive OAuth refresh exception.', [
                    'message' => $e->getMessage(),
                ]);

                throw new GoogleDriveAuthException(
                    'Ocurrió un problema al intentar validar el acceso a tus Carpetas MIP.',
                    previous: $e
                );
            }

            if (!is_array($token) || isset($token['error']) || empty($token['access_token'])) {
                Log::error('Google Drive OAuth refresh failed.', [
                    'error' => $token['error'] ?? 'unknown_error',
                    'error_description' => $token['error_description'] ?? null,
                ]);

                throw new GoogleDriveAuthException(
                    'Ocurrió un problema al intentar validar el acceso a tus Carpetas MIP.'
                );
            }

            $ttl = max(((int) ($token['expires_in'] ?? 3600)) - 60, 60);

            Cache::put($cacheKey, $token, now()->addSeconds($ttl));

            return $token;
        });
    }
}
