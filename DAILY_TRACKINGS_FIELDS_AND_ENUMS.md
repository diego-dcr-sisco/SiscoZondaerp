# Daily Trackings: campos y enums permitidos

Fuente principal:
- Migracion: `database/migrations/2026_04_15_000001_create_daily_trackings_table.php`
- Migracion: `database/migrations/2026_04_16_120000_add_focused_pest_to_daily_trackings_table.php`
- Migracion: `database/migrations/2026_04_16_130000_drop_service_type_from_daily_trackings_table.php`
- Migracion: `database/migrations/2026_04_16_140000_add_is_recurrent_to_daily_trackings_table.php`
- Migracion: `database/migrations/2026_04_17_110000_add_customer_category_to_daily_trackings_table.php`
- Enums: `app/Enums/*`

## Campos de la tabla `daily_trackings`

1. `id`
2. `service_id`
3. `customer_name`
4. `phone`
5. `customer_type`
6. `customer_category`
7. `state`
8. `city`
9. `address`
10. `contact_method`
11. `status`
12. `responded`
13. `is_recurrent`
14. `quoted`
15. `closed`
16. `has_coverage`
17. `quoted_amount`
18. `billed_amount`
19. `payment_method`
20. `invoice`
21. `service_date`
22. `quote_sent_date`
23. `close_date`
24. `payment_date`
25. `follow_up_date`
26. `service_time`
27. `focused_pest`
28. `notes`
29. `status_updated_at`
30. `status_updated_by`
31. `created_at`
32. `updated_at`

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
- `is_recurrent`: `true | false`
- `has_coverage`: `true | false`

## Notas para importacion
- Si un campo enum recibe un valor fuera de esta lista, Laravel lanzara error de enum invalido.
- Conviene normalizar mayusculas, acentos y variantes antes de guardar (ejemplo: `FB` -> `pagina`, `Indutrial / Planta` -> `industrial`).
- `service_type` y `tipo_de_servicio` ya no forman parte de `daily_trackings`.
