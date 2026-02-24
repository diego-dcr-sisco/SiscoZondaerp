# Sistema de Seguimiento de Ubicación GPS

Este documento explica cómo funciona el sistema de seguimiento de ubicación GPS implementado para SISCO_ZONDA.

## 📋 Descripción General

El sistema captura automáticamente la ubicación GPS de los usuarios cada 20 minutos desde la aplicación móvil y la envía al servidor ERP para su almacenamiento y análisis.

## 🏗️ Arquitectura

### **Aplicación Móvil (sisco_zonda_app)**
- Servicio de ubicación en segundo plano que se ejecuta cada 20 minutos
- Verifica conectividad antes de obtener ubicación
- Cola local para ubicaciones pendientes cuando no hay internet
- Envío automático al servidor cuando se recupera la conexión

### **Backend ERP (sisco_zonda_erp)**
- API REST para recibir y almacenar ubicaciones
- Tabla `user_location` para historial completo
- Campos en tabla `user` para última ubicación conocida
- Endpoints para consultar ubicaciones

## 📁 Archivos Creados/Modificados

### Backend (sisco_zonda_erp)

#### Migraciones:
1. **`2025_02_24_000001_create_user_location.php`**
   - Tabla para almacenar historial de ubicaciones
   - Campos: latitude, longitude, accuracy, altitude, speed, recorded_at

2. **`2025_02_24_000002_add_location_fields_to_user.php`**
   - Añade campos de última ubicación a la tabla `user`
   - Campos: last_latitude, last_longitude, last_location_accuracy, last_location_at

#### Modelos:
- **`app/Models/UserLocation.php`**
  - Modelo para manejar ubicaciones
  - Métodos útiles:
    - `getLastLocation($userId)` - Última ubicación de un usuario
    - `getLocationHistory($userId, $start, $end)` - Historial por fechas
    - `getRecentLocations($minutes)` - Ubicaciones recientes
    - `distanceTo($otherLocation)` - Calcular distancia entre dos puntos

- **`app/Models/User.php`** (modificado)
  - Añadidos campos de ubicación a `$fillable` y `$casts`
  - Relaciones: `locations()`, `lastLocation()`

#### Controladores:
- **`app/Http/Controllers/Api/LocationController.php`**
  - Endpoints API para manejo de ubicaciones:
    - `POST /api/location/update` - Recibir ubicación desde app
    - `GET /api/location/last/{userId}` - Última ubicación
    - `GET /api/location/history/{userId}` - Historial
    - `GET /api/location/recent` - Ubicaciones recientes
    - `GET /api/location/all-users` - Todas las ubicaciones

#### Rutas:
- **`routes/api_locations.php`**
  - Define todas las rutas API protegidas con Sanctum

### App Móvil (sisco_zonda_app)

#### Servicios:
- **`app/services/locationService.ts`** (modificado)
  - Añadida integración con API del servidor
  - Sistema de cola para ubicaciones pendientes
  - Función `processLocationQueue()` para reintentar envíos fallidos

## 🚀 Instalación

### 1. Backend (Laravel)

```bash
cd sisco_zonda_erp

# Ejecutar migraciones
php artisan migrate

# Opcional: Si necesitas revertir
php artisan migrate:rollback --step=2
```

### 2. Agregar rutas API

En `routes/api.php`, añade al final:

```php
// Incluir rutas de ubicación
require __DIR__.'/api_locations.php';
```

### 3. App Móvil

Ya está configurada automáticamente. Solo necesitas:

```bash
cd sisco_zonda_app

# Reconstruir la app nativa
npx expo prebuild --clean

# Ejecutar en Android
npx expo run:android

# O en iOS
npx expo run:ios
```

## 📡 API Endpoints

### **POST** `/api/location/update`
Envía la ubicación del usuario autenticado.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "latitude": -17.78629188,
  "longitude": -63.18116966,
  "accuracy": 15.5,
  "altitude": 420.5,
  "speed": 0.0,
  "timestamp": 1709654400000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Ubicación guardada exitosamente",
  "data": {
    "location_id": 123,
    "recorded_at": "2025-02-24T10:30:00.000000Z"
  }
}
```

### **GET** `/api/location/last/{userId}`
Obtiene la última ubicación de un usuario.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "user_id": 5,
    "latitude": "-17.78629188",
    "longitude": "-63.18116966",
    "accuracy": "15.50",
    "recorded_at": "2025-02-24T10:30:00.000000Z"
  }
}
```

### **GET** `/api/location/history/{userId}`
Obtiene el historial de ubicaciones.

**Query params:**
- `start_date` (opcional): Fecha inicio (YYYY-MM-DD)
- `end_date` (opcional): Fecha fin (YYYY-MM-DD)

**Example:** `/api/location/history/5?start_date=2025-02-01&end_date=2025-02-24`

### **GET** `/api/location/recent`
Obtiene ubicaciones recientes de todos los usuarios.

**Query params:**
- `minutes` (opcional, default: 60): Últimos N minutos

### **GET** `/api/location/all-users`
Obtiene la última ubicación de todos los usuarios activos.

## 💾 Estructura de Base de Datos

### Tabla `user_location`
```sql
id                 - BIGINT (PK)
user_id            - BIGINT (FK -> user.id)
latitude           - DECIMAL(10,8)
longitude          - DECIMAL(11,8)
accuracy           - DECIMAL(8,2)
altitude           - DECIMAL(8,2)
speed              - DECIMAL(8,2)
source             - VARCHAR (mobile_app, web, manual)
recorded_at        - TIMESTAMP
created_at         - TIMESTAMP
updated_at         - TIMESTAMP
```

### Campos añadidos a tabla `user`
```sql
last_latitude           - DECIMAL(10,8)
last_longitude          - DECIMAL(11,8)
last_location_accuracy  - DECIMAL(8,2)
last_location_at        - TIMESTAMP
```

## 🔍 Consultas Útiles

### Obtener última ubicación de todos los técnicos:
```php
$technicians = User::where('role_id', 3)
    ->where('status_id', 2)
    ->whereNotNull('last_latitude')
    ->get(['id', 'name', 'last_latitude', 'last_longitude', 'last_location_at']);
```

### Historial de un usuario hoy:
```php
$today = now()->startOfDay();
$locations = UserLocation::where('user_id', 5)
    ->where('recorded_at', '>=', $today)
    ->orderBy('recorded_at', 'desc')
    ->get();
```

### Calcular distancia entre dos ubicaciones:
```php
$location1 = UserLocation::find(1);
$location2 = UserLocation::find(2);
$distance = $location1->distanceTo($location2); // en kilómetros
```

## 🔐 Seguridad

- Todas las rutas API requieren autenticación con Laravel Sanctum
- El token se almacena en la app móvil con AsyncStorage
- Solo usuarios autenticados pueden enviar/consultar ubicaciones
- Los permisos de ubicación deben ser otorgados por el usuario

## ⚙️ Configuración

### Cambiar intervalo de actualización:

En `app/services/locationService.ts`:
```typescript
const LOCATION_INTERVAL = 20 * 60 * 1000; // 20 minutos
// Cambiar a 10 minutos:
const LOCATION_INTERVAL = 10 * 60 * 1000;
```

### Desactivar seguimiento:

```typescript
import { stopLocationTracking } from './services/locationService';

await stopLocationTracking();
```

## 📱 Funcionamiento en la App

1. **Al iniciar la app**: Se registra el servicio de ubicación en segundo plano
2. **Cada 20 minutos**: 
   - Verifica conexión a internet
   - Si hay conexión: obtiene ubicación y envía al servidor
   - Si no hay conexión: marca como pendiente
3. **Al recuperar conexión**: 
   - Obtiene ubicación pendiente
   - Procesa cola de ubicaciones no enviadas
4. **La app cerrada**: El servicio continúa ejecutándose (sujeto a límites del SO)

## 🐛 Debugging

Ver logs en la consola:
```bash
# Android
npx react-native log-android

# iOS
npx react-native log-ios
```

Buscar mensajes con el prefijo:
- `[Background Location]`
- `[Location Service]`

## 📝 Notas Importantes

- **iOS**: Background Fetch no es exacto, iOS decide cuándo ejecutar
- **Android**: Algunas ROMs personalizadas pueden matar procesos en segundo plano
- **Batería**: El uso de ubicación consume batería, se usa `Accuracy.Balanced`
- **Privacidad**: Informar a los usuarios sobre el seguimiento de ubicación
- **Datos**: Las ubicaciones se acumulan; considerar limpieza periódica

## 🎯 Próximos Pasos

1. Crear vista en el ERP para visualizar ubicaciones en mapa
2. Implementar notificaciones si un usuario no reporta ubicación
3. Añadir análisis de rutas y patrones de movimiento
4. Integrar con órdenes de servicio para verificar visitas

## 📞 Soporte

Para dudas sobre la implementación, revisar los comentarios en el código o contactar al desarrollador.
