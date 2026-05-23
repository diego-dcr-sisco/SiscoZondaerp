# DailyTracking — Configuración de Importación

Especificación del mapeo entre el modelo Laravel `DailyTracking`, los headers de los archivos de datos y los tipos de respuesta esperados.

> **Convención:** `header_archivo` => `campo_modelo`

---

## Tabla 1 — CRM

| Campo (Modelo) | Header en Archivo | Valores / Tipos de Respuesta |
|---|---|---|
| `id` | — | — |
| `service_id` | — | — |
| `customer_name` | `Cliente/Empresa` | — |
| `phone` | `Telefono` | — |
| `customer_type` | `Tipo de Servicio` | `Domestico (1)` · `Comercial (2)` · `Industrial / Planta (3)` |
| `state` | `Estado / Ciudad` | — |
| `city` | — | — |
| `address` | `DOMICILIO` | — |
| `contact_method` | `Medio de contacto` | `GOOGLE` · `FB` · `PAGINA` · `INSTAGRAM` · `LLAMADA` · `RECOMENDACION` |
| `status` | `Estatus` | `PEN` · `N/C` · `CERRADO` · `ESPERA` · `LEVANTAMIENTO` · `NO REQUIERE` · `SIN COBERTURA` |
| `responded` | `¿Contestó?` | — |
| `quoted` | `¿Se cotizó?` | — |
| `closed` | `¿Se cerró el servicio?` | — |
| `has_coverage` | — | — |
| `quoted_amount` | `Monto cotizado` | — |
| `billed_amount` | `Monto facturado` | — |
| `payment_method` | `Metodo Pago` | `Efectivo` · `Transferencia` · `Ambas` · `No confirmo` |
| `invoice` | `Factura` | — |
| `service_date` | `Fecha` | — |
| `quote_sent_date` | — | — |
| `close_date` | `FECHA CIERRE` | — |
| `payment_date` | `FECHA RECIBI PAGO SERVICIO` | — |
| `follow_up_date` | — | — |
| `service_time` | `HORA` | — |
| `focused_pest` | `PLAGA` | — |
| `notes` | `OBSERVACIONES` | — |
| `status_updated_at` | — | — |
| `status_updated_by` | — | — |
| `created_at` | `Fecha` | — |
| `updated_at` | — | — |

---

## Tabla 2 — Prospeccion

| Campo (Modelo) | Header en Archivo | Valores / Tipos de Respuesta |
|---|---|---|
| `id` | — | — |
| `service_id` | — | — |
| `customer_name` | — | — |
| `phone` | — | — |
| `customer_type` | — | — |
| `state` | — | — |
| `city` | — | — |
| `address` | — | — |
| `contact_method` | `Medio de contacto` | — |
| `status` | — | — |
| `responded` | — | — |
| `quoted` | — | — |
| `closed` | — | `TRUE` si `CERRO O MOTIVO DE NO CIERRE` = `SI`, de lo contrario `FALSE` |
| `has_coverage` | — | — |
| `quoted_amount` | — | — |
| `billed_amount` | `Cotizacion` | — |
| `payment_method` | — | — |
| `invoice` | — | — |
| `service_date` | `Fecha programada` | — |
| `quote_sent_date` | — | — |
| `close_date` | — | — |
| `payment_date` | — | — |
| `follow_up_date` | — | — |
| `service_time` | — | — |
| `focused_pest` | — | — |
| `notes` | `CERRO O MOTIVO DE NO CIERRE` | — |
| `status_updated_at` | — | — |
| `status_updated_by` | — | — |
| `created_at` | `Fecha` | — |
| `updated_at` | — | — |

---

## Detalle de Campos con Tipos Definidos

### `customer_type` — Tipo de Servicio *(CRM)*
| Valor en Archivo | Valor en Modelo |
|---|---|
| Domestico | `1` |
| Comercial | `2` |
| Industrial / Planta | `3` |

### `contact_method` — Medio de contacto *(CRM y Prospeccion)*
`GOOGLE` · `FB` · `PAGINA` · `INSTAGRAM` · `LLAMADA` · `RECOMENDACION`

### `status` — Estatus *(CRM)*
| Clave | Descripción |
|---|---|
| `PEN` | Pendiente |
| `N/C` | No Contactado |
| `CERRADO` | Servicio cerrado |
| `ESPERA` | En espera |
| `LEVANTAMIENTO` | En levantamiento |
| `NO REQUIERE` | No requiere el servicio |
| `SIN COBERTURA` | Sin cobertura en la zona |

### `payment_method` — Método de Pago *(CRM)*
`Efectivo` · `Transferencia` · `Ambas` · `No confirmo`

### `closed` — Lógica de cierre *(Prospeccion)*
El campo `closed` se deriva del valor de `CERRO O MOTIVO DE NO CIERRE`:
- Si el valor es `SI` → `closed = TRUE`
- Cualquier otro valor → `closed = FALSE`
