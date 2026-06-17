<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveCachedService;
use App\Services\GoogleDriveClientFactory;
use App\Services\GoogleDriveTokenStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleDriveController extends Controller
{
    private function isGoogleAuthError(string $message): bool
    {
        $needles = [
            'UNAUTHENTICATED',
            'CREDENTIALS_MISSING',
            'Login Required',
            'required authentication credential',
            '401',
        ];

        foreach ($needles as $needle) {
            if (stripos($message, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function googleAuthErrorView(string $message)
    {
        return response()->view('google-drive-auth-error', [
            'error_message' => $message,
        ], 401);
    }

    protected function getClient()
    {
        $client = app(GoogleDriveClientFactory::class)->makeAuthenticatedClient();
        $client->setRedirectUri(route('google.drive.callback'));

        return $client;
    }

    public function redirectToGoogle()
    {
        $client = app(GoogleDriveClientFactory::class)->makeClient();
        $client->setRedirectUri(route('google.drive.callback'));
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = app(GoogleDriveClientFactory::class)->makeClient();
        $client->setRedirectUri(route('google.drive.callback'));

        if ($request->has('code')) {
            try {
                $token = $client->fetchAccessTokenWithAuthCode($request->code);
                
                if (isset($token['refresh_token'])) {
                    $refreshToken = $token['refresh_token'];
                    app(GoogleDriveTokenStore::class)->saveRefreshToken($refreshToken);
                    
                    return view('google-drive-success', [
                        'refresh_token' => $refreshToken,
                        'access_token' => $token['access_token'],
                        'expires_in' => $token['expires_in']
                    ]);
                } else {
                    return response()->json([
                        'error' => 'No refresh token received',
                        'token' => $token
                    ], 400);
                }
            } catch (\Exception $e) {
                if ($this->isGoogleAuthError($e->getMessage())) {
                    return $this->googleAuthErrorView($e->getMessage());
                }

                return response()->json([
                    'error' => 'Authentication failed',
                    'message' => $e->getMessage()
                ], 400);
            }
        }

        return response()->json(['error' => 'No authorization code received'], 400);
    }

    public function testConnection()
    {
        try {
            $client = $this->getClient();
            $disk = Storage::disk('google');
            $cache = app(GoogleDriveCachedService::class);

            // Puedes usar directamente el Storage disk si está bien configurado
            $disk->put('test-connection.txt', 'Conexión exitosa: ' . now());
            $cache->forgetRelatedTo('test-connection.txt');
            $content = $cache->read($disk, 'test-connection.txt');
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa con Google Drive',
                'file_content' => $content
            ]);
        } catch (\Exception $e) {
            if ($this->isGoogleAuthError($e->getMessage())) {
                return $this->googleAuthErrorView($e->getMessage());
            }

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
