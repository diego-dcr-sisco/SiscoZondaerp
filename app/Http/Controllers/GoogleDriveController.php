<?php

/*namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new GoogleClient();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->setRedirectUri(route('google.drive.callback'));
        $client->addScope(Drive::DRIVE);
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new GoogleClient();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->setRedirectUri(route('google.drive.callback'));
        $client->addScope(Drive::DRIVE);

        if ($request->has('code')) {
            try {
                $token = $client->fetchAccessTokenWithAuthCode($request->code);
                
                if (isset($token['refresh_token'])) {
                    // Guardar el refresh token (en .env, base de datos, o cache)
                    $refreshToken = $token['refresh_token'];
                    
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
            Storage::disk('google')->put('test-connection.txt', 'Conexión exitosa: ' . now());
            $content = Storage::disk('google')->get('test-connection.txt');
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa con Google Drive',
                'file_content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}*/

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveController extends Controller
{
    protected function getClient()
    {
        $client = new GoogleClient();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->setRedirectUri(route('google.drive.callback'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');

        // Recuperar refresh token desde base de datos, cache o .env
        $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');

        if ($refreshToken) {
            $client->refreshToken($refreshToken);
        }

        return $client;
    }

    public function redirectToGoogle()
    {
        $client = new GoogleClient();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->setRedirectUri(route('google.drive.callback'));
        $client->addScope(Drive::DRIVE);
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new GoogleClient();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->setRedirectUri(route('google.drive.callback'));
        $client->addScope(Drive::DRIVE);

        if ($request->has('code')) {
            try {
                $token = $client->fetchAccessTokenWithAuthCode($request->code);
                
                if (isset($token['refresh_token'])) {
                    // Guardar refresh token en .env, DB, o cache
                    $refreshToken = $token['refresh_token'];
                    
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
            $service = new Drive($client);

            // Puedes usar directamente el Storage disk si está bien configurado
            Storage::disk('google')->put('test-connection.txt', 'Conexión exitosa: ' . now());
            $content = Storage::disk('google')->get('test-connection.txt');
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa con Google Drive',
                'file_content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
