<!DOCTYPE html>
<html>

<head>
    <title>Google Drive Authentication Success</title>
</head>

<body>
    <h1>✅ Autenticación exitosa con Google Drive</h1>

    <p><strong>Refresh Token:</strong></p>
    <textarea style="width: 100%; height: 100px; font-family: monospace;" readonly>{{ $refresh_token }}</textarea>

    <p>Este refresh token ya fue guardado en base de datos.</p>

    <p><strong>Access Token:</strong> {{ $access_token }}</p>
    <p><strong>Expira en:</strong> {{ $expires_in }} segundos</p>

    <br>
    <a href="{{ route('google.drive.test') }}">Probar conexión</a>
</body>

</html>
