# Guía de Importación Excel - Instrucciones de Uso y Testing

## ✅ Implementación Completada

Se ha implementado una funcionalidad completa de importación Excel para **dos hojas independientes** con validación, manejo de errores y transacciones.

---

## 🚀 Acceso Rápido

**Ruta Principal:** `/daily-trackings/import`

O desde el panel de administración:
- Ir a **CRM → Actividades diarias**
- Hacer clic en botón **Importar Excel**

---

## 📋 Estructura de Archivos

### Archivos Creados
```
app/
  ├── Imports/
  │   ├── DailyTrackingImport.php       (Procesa hoja 1)
  │   └── CommercialProspectsImport.php (Procesa hoja 2)
  ├── Models/
  │   └── CommercialProspect.php         (Nuevo modelo)
  ├── Services/
  │   └── ExcelImportService.php        (Orquestador)
  ├── Http/
  │   ├── Controllers/
  │   │   └── DailyTrackingController.php (métodos: showImportForm, importFromExcel)
  │   └── Requests/
  │       └── ImportExcelRequest.php    (Validación archivo)
database/
  └── migrations/
      └── 2026_04_16_create_commercial_prospects_table.php
resources/
  └── views/crm/daily-tracking/
      └── import-excel.blade.php        (UI profesional)
routes/
  └── web.php                           (2 nuevas rutas)
```

---

## 🧪 Testing

### Opción 1: Excel Manual (Recomendado)

1. **Crear archivo de prueba en Excel:**
   - Hoja 1: Nombre `Registro_Diario_CRM`
   - Hoja 2: Nombre `PROSPECTOS COMERCIALES`

2. **Hoja 1: Registro_Diario_CRM**
   Agregar estos headers y datos:
   ```
   Fecha | Cliente / Empresa | Teléfono | Estado / Ciudad | Medio de contacto | ¿Contestó? | Se cotizó | Monto cotizado | Se cerró | Monto facturado | Tipo de Servicio | ...
   2026-04-15 | Empresa A | 555-1234 | Buenos Aires | google | yes | yes | 1500 | yes | 2000 | comercial | ...
   2026-04-16 | Empresa B | 555-5678 | CABA | llamada | yes | no | 0 | no | 0 | industrial | ...
   ```

3. **Hoja 2: PROSPECTOS COMERCIALES**
   Agregar estos headers y datos:
   ```
   NOMBRE COMERCIAL | FECHA | TIPO DE COMERCIO | COTIZACION | CERRO O MOTIVO DE NO CIERRE | MEDIO DE CONTACTO | FECHA PROGRAMADA
   LocalCom XYZ | 2026-04-15 | Retail | Enviada | Aceptada | google | 2026-04-20
   TiendaPlus ABC | 2026-04-16 | Mayorista | Pendiente | - | llamada | 2026-04-25
   ```

4. **Subir archivo:**
   - Ir a `/daily-trackings/import`
   - Arrastrar el archivo `.xlsx` o hacer clic
   - Hacer clic en **Importar**

5. **Verificar resultados:**
   - Ver estadísticas en página
   - Ir a **Ver Registros** para confirmar inserciones
   - Revisar logs en `storage/logs/laravel.log`

### Opción 2: Testing Artisan (CLI)

```bash
# Ver migraciones ejecutadas
php artisan migrate:status

# Ver tabla creada
php artisan tinker
>>> \DB::table('commercial_prospects')->get();
>>> \DB::table('daily_trackings')->count();

# Limpiar datos de prueba (si es necesario)
php artisan tinker
>>> \App\Models\CommercialProspect::truncate();
>>> \App\Models\DailyTracking::where('customer_name', 'Empresa A')->delete();
```

### Opción 3: Testing Programático (Código)

```php
// Dentro de tinker o en un test
$service = new \App\Services\ExcelImportService();
$result = $service->importFile('/ruta/archivo.xlsx');

dump($result);
// Array con:
// - success: bool
// - message: string
// - data: [...estadísticas...]
// - total_records: int
// - import_time: int
```

---

## 📊 Funcionalidades Clave

### ✓ Validación Automática
- ✅ Fechas en múltiples formatos (Excel serial, Y-m-d, etc.)
- ✅ Números con comas y puntos
- ✅ Booleanos (yes, sí, si, 1, true)
- ✅ Campus obligatorios (customer_name, commercial_name)
- ✅ Máximo 5MB por archivo

### ✓ Manejo de Errores
- ✅ Errores por fila sin detención
- ✅ Logging automático a `storage/logs/laravel.log`
- ✅ Muestra errores en UI (primeros 10 + contador)
- ✅ Rollback de transacción si hay error crítico

### ✓ Búsqueda Flexible de Headers
- ✅ Ignora espacios y puntuación
- ✅ Maneja acentos y tíldes
- ✅ Matching parcial (ej: "¿Contestó?" → "contesto")

### ✓ Duplicados
- ✅ No inserCómo testear)
- ✅ Actualiza registros existentes
- ✅ Usa claves naturales (nombre + fecha)

---

## Mapeo de Columnas

### Hoja 1 → daily_trackings
| Excel | DB | Tipo |
|-------|-----|------|
| Fecha | service_date | date |
| Cliente / Empresa | customer_name | string (req) |
| Teléfono | phone | string |
| Medio de contacto | contact_method | string |
| ¿Contestó? | responded | boolean |
| Se cotizó | quoted | string (yes/no) |
| Monto cotizado | quoted_amount | decimal |
| Se cerró | closed | string (yes/no) |
| Monto facturado | billed_amount | decimal |
| Tipo de Servicio | service_type | string |
| ... | ... | ... |

### Hoja 2 → commercial_prospects
| Excel | DB | Tipo |
|-------|-----|------|
| NOMBRE COMERCIAL | commercial_name | string (req) |
| FECHA | date | date |
| TIPO DE COMERCIO | commerce_type | string |
| COTIZACION | quotation_status | string |
| CERRO O MOTIVO | close_reason | text |
| MEDIO DE CONTACTO | contact_method | string |
| FECHA PROGRAMADA | scheduled_date | date |

---

## 🔍 Logs y Debugging

### Ver logs en tiempo real
```bash
tail -f storage/logs/laravel.log | grep -i "import"
```

### Logs esperados (ejemplo exitoso)
```
[2026-04-16 10:30:45] local.INFO: Iniciando importación de Registro_Diario_CRM
[2026-04-16 10:30:46] local.INFO: Hoja Registro_Diario_CRM importada {"inserted":2,"skipped":0}
[2026-04-16 10:30:46] local.INFO: Iniciando importación de PROSPECTOS COMERCIALES
[2026-04-16 10:30:47] local.INFO: Hoja PROSPECTOS COMERCIALES importada {"inserted":2,"skipped":0}
```

### Logs con errores
```
[2026-04-16 10:30:45] local.ERROR: Error importando Registro_Diario_CRM: La hoja "Registro_Diario_CRM" no contiene datos.
[2026-04-16 10:30:46] local.ERROR: Error en importación Excel: {exception_details}
```

---

## ⚠️ Consideraciones Importantes

### Requisitos
- ✅ Librería **laravel/excel** (Maatwebsite\Excel) instalada y configurada
- ✅ Service por defecto en tabla `services` (para daily_tracking)
- ✅ Permisos de lectura/escritura en `storage/`

### Limitaciones Actuales
- ❌ No soporta importar solo 1 hoja (ambas deben estar)
- ❌ Máximo 5MB por archivo
- ❌ Sin previsualización antes de importar
- ❌ Sin anulación de importación (debe ser manual)

### Mejoras Futuras
- [ ] Permitir importar hoja individual
- [ ] Queue para archivos >10MB
- [ ] Previsualización con validación preliminar
- [ ] Histórico de importaciones (tabla audit)
- [ ] Mapeo dinámico de columnas en UI

---

## 📝 Notas de Desarrollo

### Rutas
- **GET** `/daily-trackings/import` → Formulario
- **POST** `/daily-trackings/import` → Procesar

### Nombres de Rutas
- `daily-tracking.import-form` → Formulario
- `daily-tracking.import-excel` → Procesador

### Middleware
- Requiere autenticación (middleware `auth`)
- Requiere permiso de usuario (verificado en FormRequest)

### Almacenamiento Temporal
- Archivos se guardan en `storage/imports/`
- Se eliminan automáticamente después de procesar

---

## 🎨 UI/UX

### Página de Importación
- ✅ Drag-drop zone interactivo
- ✅ Validación en cliente (tipo archivo, tamaño)
- ✅ Estilos con paleta corporativa
- ✅ Resumen de resultados
- ✅ Listado de errores (primeros 10)
- ✅ Botones de navegación rápida

### Respuesta POST
- ✅ Redirección a `/daily-tracking` (éxito)
- ✅ Regreso a formulario (error)
- ✅ Sesión con estadísticas para mostrar

---

## 📞 Soporte

Para agregar más funcionalidades:
1. Ver documentación en `/memories/repo/excel-import-implementation.md`
2. Revisar clases Import en `app/Imports/`
3. Extender validación en `app/Http/Requests/ImportExcelRequest.php`
4. Agregar logging en `app/Services/ExcelImportService.php`

---

**Fecha de Implementación:** 16 de abril de 2026  
**Versión:** 1.0  
**Estado:** Producción
