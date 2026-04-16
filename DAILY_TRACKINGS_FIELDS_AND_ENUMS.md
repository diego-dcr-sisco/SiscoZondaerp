# Daily Trackings: campos y enums permitidos

Fuente principal:
- Migracion: `database/migrations/2026_04_15_000001_create_daily_trackings_table.php`
- Enums: `app/Enums/*`

## Campos de la tabla `daily_trackings`

1. `id`
2. `service_id`
3. `customer_name`
4. `phone`
5. `customer_type`
6. `state`
7. `city`
8. `address`
9. `contact_method`
10. `status`
11. `service_type`
12. `responded`
13. `quoted`
14. `closed`
15. `has_coverage`
16. `quoted_amount`
17. `billed_amount`
18. `payment_method`
19. `invoice`
20. `service_date`
21. `quote_sent_date`
22. `close_date`
23. `payment_date`
24. `follow_up_date`
25. `service_time`
26. `notes`
27. `status_updated_at`
28. `status_updated_by`
29. `created_at`
30. `updated_at`

## Campos con enum (valores permitidos)

### `customer_type` (DailyTrackingCustomerType)
- `domestico`
- `comercial`
- `industrial`

### `contact_method` (DailyTrackingContactMethod)
- `google`
- `pagina`
- `llamada`
- `cambaceo`

### `status` (DailyTrackingStatus)
- `no_requiere`
- `survey`
- `closed`

### `service_type` (DailyTrackingServiceType)
- `industrial`
- `comercial`

### `quoted` (DailyTrackingQuoted)
- `yes`
- `pending`

### `closed` (DailyTrackingClosed)
- `yes`
- `no`
- `pending`

### `payment_method` (DailyTrackingPaymentMethod)
- `cash`
- `transfer`
- `check`
- `other`

### `invoice` (DailyTrackingInvoice)
- `yes`
- `no`
- `not_applicable`

## Campos booleanos
- `responded`: `true | false`
- `has_coverage`: `true | false`

## Notas para importacion
- Si un campo enum recibe un valor fuera de esta lista, Laravel lanzara error de enum invalido.
- Conviene normalizar mayusculas, acentos y variantes antes de guardar (ejemplo: `FB` -> `pagina`, `Indutrial / Planta` -> `industrial`).
