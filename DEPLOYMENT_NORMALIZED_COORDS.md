# Guía de Implementación: Coordenadas Normalizadas para Dispositivos en Planos

## 📋 Descripción General

Esta actualización implementa un sistema de coordenadas normalizadas para los dispositivos en planos, permitiendo que los puntos se reajusten automáticamente cuando se cambia el tamaño de la imagen del plano.

### ¿Por qué es importante?

**Antes:** Los puntos se guardaban con coordenadas absolutas (pixeles). Si cambiabas el plano por uno de diferente tamaño, los puntos quedaban desalineados.

**Ahora:** Los puntos se guardan como porcentaje del tamaño del plano (valores entre 0 y 1). Los puntos siempre quedan en la misma posición relativa, sin importar el tamaño del plano.

---

## 🗂️ Archivos Modificados

### Backend
- `app/Models/Device.php` - Agregados campos `x_norm`, `y_norm` al fillable
- `app/Http/Controllers/FloorPlansController.php` - Persistencia de coordenadas normalizadas al guardar
- `database/migrations/2026_02_09_000000_add_normalized_coords_to_device_table.php` - Nueva migración
- `app/Console/Commands/BackfillDeviceNormalizedCoordinates.php` - Comando para actualizar datos existentes

### Frontend
- `resources/views/floorplans/edit/devices.blade.php` - Uso de coordenadas normalizadas al cargar y redimensionar

---

## 🚀 Pasos para Implementar en Producción

### 📦 PASO 1: Preparación (Local)

#### 1.1 Verificar que todos los cambios estén confirmados
```bash
cd "/home/antonio/Sisco - Zonda/SiscoZondaerp"
git status
git add .
git commit -m "feat: Implementar coordenadas normalizadas para dispositivos en planos"
git push origin dev2
```

#### 1.2 Probar el comando localmente (DRY RUN)
```bash
php artisan devices:backfill-normalized-coords --dry-run
```
Esto mostrará cuántos dispositivos se actualizarían sin hacer cambios reales.

#### 1.3 Ejecutar el backfill localmente
```bash
php artisan devices:backfill-normalized-coords
```

#### 1.4 Verificar en local
- Abre un plano existente con dispositivos
- Verifica que los puntos estén en las posiciones correctas
- Cambia el tamaño del navegador y verifica que se reajusten

---

### 🔧 PASO 2: Preparación del Servidor (cPanel)

#### 2.1 Hacer respaldo de la base de datos
1. Accede a **cPanel**
2. Ve a **phpMyAdmin**
3. Selecciona tu base de datos
4. Haz clic en **Exportar**
5. Selecciona **Método rápido** o **Personalizado** (recomendado)
6. Descarga el archivo `.sql`
7. **Guárdalo en un lugar seguro** con fecha (ej: `backup_2026_02_09.sql`)

#### 2.2 Verificar horario de baja actividad
- Identifica una ventana de mantenimiento (ej: madrugada, fin de semana)
- Notifica a usuarios si es necesario

---

### 📤 PASO 3: Subir Cambios al Servidor

#### 3.1 Subir archivos vía Git (si tienes acceso SSH)
```bash
# Conectar al servidor
ssh tu_usuario@tu_servidor.com

# Ir al directorio del proyecto
cd /home/tu_usuario/public_html

# Hacer pull de los cambios
git pull origin dev2
```

#### 3.2 Subir archivos vía FTP/File Manager (alternativa)
Si no tienes Git en el servidor, sube manualmente estos archivos:

**Archivos nuevos:**
- `database/migrations/2026_02_09_000000_add_normalized_coords_to_device_table.php`
- `app/Console/Commands/BackfillDeviceNormalizedCoordinates.php`

**Archivos modificados:**
- `app/Models/Device.php`
- `app/Http/Controllers/FloorPlansController.php`
- `resources/views/floorplans/edit/devices.blade.php`

---

### 🗄️ PASO 4: Ejecutar Migración en Producción

#### 4.1 Vía SSH (recomendado)
```bash
# Conectar al servidor
ssh tu_usuario@tu_servidor.com

# Ir al directorio del proyecto
cd /home/tu_usuario/public_html

# Ejecutar migraciones
php artisan migrate
```

#### 4.2 Vía cPanel Terminal (si está disponible)
1. Accede a **cPanel**
2. Ve a **Terminal** (si está habilitado)
3. Ejecuta:
```bash
cd public_html
php artisan migrate
```

#### 4.3 Vía ejecución manual SQL (última opción)
Si no tienes acceso a terminal:
1. Abre **phpMyAdmin**
2. Selecciona tu base de datos
3. Ve a **SQL**
4. Ejecuta manualmente:
```sql
ALTER TABLE `device` 
ADD COLUMN `x_norm` DOUBLE(15,8) NULL AFTER `map_y`,
ADD COLUMN `y_norm` DOUBLE(15,8) NULL AFTER `x_norm`;
```

---

### 🔄 PASO 5: Ejecutar Backfill de Datos Existentes

#### 5.1 Prueba en seco (DRY RUN)
```bash
# SSH o Terminal de cPanel
php artisan devices:backfill-normalized-coords --dry-run
```

Esto te mostrará:
- Cuántos dispositivos se actualizarán
- Si hay errores potenciales
- **No hace cambios reales**

#### 5.2 Ejecutar el backfill real
```bash
php artisan devices:backfill-normalized-coords
```

**Salida esperada:**
```
Starting backfill process...
Found 245 devices to update
[████████████████████████████] 100%

✅ Backfill completed: 245 devices updated successfully
```

---

### ✅ PASO 6: Verificación Post-Implementación

#### 6.1 Verificar en base de datos
```sql
-- Ver algunos registros actualizados
SELECT id, nplan, map_x, map_y, x_norm, y_norm, img_tamx, img_tamy 
FROM device 
WHERE x_norm IS NOT NULL 
LIMIT 10;

-- Verificar que no haya valores fuera de rango
SELECT COUNT(*) as fuera_rango
FROM device
WHERE x_norm IS NOT NULL 
AND (x_norm < 0 OR x_norm > 1 OR y_norm < 0 OR y_norm > 1);
-- Debe retornar: 0
```

#### 6.2 Verificar en la aplicación
1. Accede al sistema en producción
2. Abre un plano existente que tenga dispositivos
3. Verifica que los puntos estén correctamente posicionados
4. Cambia el tamaño de la ventana del navegador
5. Verifica que los puntos se reajusten proporcionalmente
6. Crea un nuevo punto y guarda
7. Recarga la página y verifica que el punto se mantenga en su posición

#### 6.3 Probar con diferentes planos
- Plano horizontal (más ancho que alto)
- Plano vertical (más alto que ancho)
- Plano con muchos dispositivos
- Plano con pocos dispositivos

---

### 🔥 PASO 7: Plan de Rollback (Por Si Algo Sale Mal)

#### Si necesitas revertir los cambios:

**Opción 1: Restaurar desde backup**
```bash
# SSH o Terminal
mysql -u usuario -p nombre_base_datos < backup_2026_02_09.sql
```

**Opción 2: Revertir migración**
```bash
php artisan migrate:rollback --step=1
```

Esto eliminará las columnas `x_norm` y `y_norm` de la tabla `device`.

**Opción 3: Revertir código**
```bash
git revert HEAD
git push origin dev2
```

---

## 🧪 Pruebas Recomendadas

### Escenarios de Prueba

1. **Cargar plano existente**
   - ✅ Los puntos deben aparecer en las posiciones correctas
   
2. **Redimensionar navegador**
   - ✅ Los puntos deben reajustarse proporcionalmente

3. **Agregar nuevo punto**
   - ✅ El punto debe guardarse con coordenadas normalizadas
   - ✅ Al recargar debe aparecer en la misma posición

4. **Mover punto existente**
   - ✅ Las coordenadas normalizadas deben actualizarse
   - ✅ Al recargar debe mantener la nueva posición

5. **Cambiar imagen del plano**
   - ✅ Los puntos deben reajustarse al nuevo tamaño
   - ✅ Deben mantener su posición relativa

---

## 📊 Monitoreo Post-Implementación

### Primeras 24 horas
- [ ] Verificar logs de errores: `storage/logs/laravel.log`
- [ ] Monitorear reportes de usuarios
- [ ] Revisar métricas de uso de planos

### Primera semana
- [ ] Verificar que nuevos dispositivos se guarden con coordenadas normalizadas
- [ ] Confirmar que no hay quejas de puntos desalineados
- [ ] Revisar performance (no debería haber impacto significativo)

---

## 🔍 Comandos Útiles

```bash
# Ver cantidad de dispositivos con coordenadas normalizadas
php artisan tinker
>>> \App\Models\Device::whereNotNull('x_norm')->count();

# Ver cantidad de dispositivos sin coordenadas normalizadas
>>> \App\Models\Device::whereNull('x_norm')->count();

# Ver ejemplo de dispositivo actualizado
>>> \App\Models\Device::whereNotNull('x_norm')->first(['id','map_x','map_y','x_norm','y_norm','img_tamx','img_tamy']);

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📝 Notas Importantes

1. **Backup obligatorio**: Nunca ejecutes la migración sin un backup reciente.

2. **Horario**: Implementa en horario de baja actividad para minimizar impacto.

3. **Validación**: Los valores normalizados deben estar entre 0 y 1. El comando de backfill valida esto automáticamente.

4. **Compatibilidad**: Los dispositivos antiguos sin coordenadas normalizadas seguirán funcionando hasta que se ejecute el backfill.

5. **Performance**: El backfill procesa en chunks de 100 registros para evitar problemas de memoria.

---

## ❓ Troubleshooting

### Problema: "SQLSTATE[42S21]: Column already exists: x_norm"
**Solución:** Las columnas ya existen. No es necesario ejecutar la migración nuevamente.

### Problema: "Class 'Device' not found"
**Solución:** Ejecuta `composer dump-autoload` para regenerar el autoload.

### Problema: Puntos aparecen fuera de lugar después del backfill
**Solución:** 
1. Verifica que `img_tamx` e `img_tamy` sean correctos
2. Revisa los valores de `x_norm` y `y_norm` (deben estar entre 0 y 1)
3. Si es necesario, restaura el backup y revisa la lógica

### Problema: El comando de backfill no encuentra dispositivos
**Solución:** Verifica que existan dispositivos con `map_x`, `map_y`, `img_tamx` e `img_tamy` válidos.

---

## 📞 Soporte

Si encuentras algún problema durante la implementación:
1. Verifica los logs: `storage/logs/laravel.log`
2. Revisa este documento
3. Contacta al equipo de desarrollo

---

## ✅ Checklist de Implementación

- [ ] Backup de base de datos realizado
- [ ] Cambios de código subidos al servidor
- [ ] Migración ejecutada exitosamente
- [ ] Backfill ejecutado con éxito
- [ ] Verificación en base de datos completada
- [ ] Pruebas en aplicación realizadas
- [ ] Monitoreo activo configurado
- [ ] Equipo notificado de la implementación

---

**Fecha de creación:** 9 de febrero de 2026  
**Versión:** 1.0  
**Autor:** Sistema de Desarrollo
