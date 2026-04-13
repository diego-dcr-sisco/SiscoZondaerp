<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (GoogleDriveAuthException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'google_drive_auth_failed',
                    'message' => $e->getMessage(),
                ], 401);
            }

            return response()->view('google-drive-auth-error', [
                'error_message' => $e->getMessage(),
            ], 401);
        });
    }
}
