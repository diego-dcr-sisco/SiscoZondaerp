# Paleta de Colores - SISCO ZONDA ERP

Esta es la paleta de colores oficial utilizada en las gráficas del sistema CRM.

## Colores Principales

| Nombre               | Código Hex | RGB                  | RGBA (opacidad 0.2)         | Uso Principal                    |
|----------------------|------------|----------------------|-----------------------------|----------------------------------|
| Deep Space Blue      | `#012640`  | `1, 38, 64`          | `rgba(1, 38, 64, 0.2)`      | Color base, fondos oscuros       |
| Deep Navy            | `#02265A`  | `2, 38, 90`          | `rgba(2, 38, 90, 0.2)`      | Acentos, elementos secundarios   |
| True Cobalt          | `#0A2986`  | `10, 41, 134`        | `rgba(10, 41, 134, 0.2)`    | **Domésticos** - Tipo de servicio|
| Indigo Velvet        | `#512A87`  | `81, 42, 135`        | `rgba(81, 42, 135, 0.2)`    | **Comerciales** - Tipo de servicio|
| Velvet Purple        | `#773774`  | `119, 55, 116`       | `rgba(119, 55, 116, 0.2)`   | Seguimientos, elementos terciarios|
| Dusty Mauve          | `#B74453`  | `183, 68, 83`        | `rgba(183, 68, 83, 0.2)`    | Estados de alerta, pendientes    |
| Fiery Terracotta     | `#DE523B`  | `222, 82, 59`        | `rgba(222, 82, 59, 0.2)`    | **Industrial** - Tipo de servicio|

## Aplicación en Gráficas

### Tipos de Servicio (Estándar en todo el sistema)

- **Domésticos**: True Cobalt (`#0A2986`)
- **Comerciales**: Indigo Velvet (`#512A87`)
- **Industrial/Planta**: Fiery Terracotta (`#DE523B`)

### Estados de Órdenes

- **Pendientes**: Dusty Mauve (`#B74453`)
- **Finalizadas**: True Cobalt (`#0A2986`)
- **Aprovadas**: Indigo Velvet (`#512A87`)

### Gráficas Dinámicas

Para gráficas con múltiples elementos (plagas, servicios programados, etc.), se utiliza la paleta completa en orden:

```javascript
const colors = [
    '#012640', // Deep Space Blue
    '#02265A', // Deep Navy
    '#0A2986', // True Cobalt
    '#512A87', // Indigo Velvet
    '#773774', // Velvet Purple
    '#B74453', // Dusty Mauve
    '#DE523B', // Fiery Terracotta
];
```

## Archivos Modificados

### Backend (PHP)
- `app/Http/Controllers/GraphicController.php` - Propiedades `$colors` y `$service_colors`
- `app/Http/Controllers/CRMController.php` - Método `generateChartColors()`

### Frontend (Blade/JavaScript)
- `resources/views/crm/charts/comercial/yearly-customers.blade.php`
- `resources/views/crm/charts/comercial/yearly-leads.blade.php`
- `resources/views/crm/charts/comercial/services-programmed.blade.php`
- `resources/views/crm/charts/comercial/pests-donut.blade.php`
- `resources/views/crm/charts/comercial/trackings-by-month.blade.php`

## Uso Correcto

### En Chart.js (JavaScript)
```javascript
{
    label: 'Domésticos',
    data: data.domestics,
    borderColor: '#0A2986',
    backgroundColor: 'rgba(10, 41, 134, 0.2)',
    fill: true
}
```

### En Laravel Charts (PHP)
```php
$chart->dataset('Domésticos', 'line', $domestics)
    ->backgroundColor('rgba(10, 41, 134, 0.2)')
    ->color('#0A2986');
```

## Notas

- La opacidad estándar para fondos es `0.2`
- Los bordes usan el color completo sin opacidad
- Mantener consistencia en todos los gráficos del sistema
- Para nuevas gráficas, usar esta paleta como referencia

---

**Fecha de implementación**: Febrero 2026  
**Versión**: 1.0
