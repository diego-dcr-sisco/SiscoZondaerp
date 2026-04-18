<?php

namespace App\Imports;

use App\Enums\DailyTrackingClosed;
use App\Enums\DailyTrackingContactMethod;
use App\Enums\DailyTrackingCustomerType;
use App\Enums\DailyTrackingInvoice;
use App\Enums\DailyTrackingPaymentMethod;
use App\Enums\DailyTrackingQuoted;
use App\Enums\DailyTrackingStatus;
use App\Models\DailyTracking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Importador de DailyTracking desde Excel (CRM Comercial.xlsx).
 *
 * Soporta dos layouts de columnas en la misma hoja:
 *   - Tabla CRM        : columnas principales históricas
 *   - Tabla Prospección: columnas adicionales (Cotizacion, Fecha programada,
 *                        CERRO O MOTIVO DE NO CIERRE)
 *
 * La detección de qué columnas existen es automática en map().
 * Los campos sin equivalente en el Excel se calculan con lógica de negocio.
 *
 * Pipeline de Laravel Excel:
 *   Excel::read → WithHeadingRow (fila 1) → map() → WithValidation (rules)
 *   → model() → WithUpserts (INSERT OR UPDATE por customer_name + service_date)
 *   → WithChunkReading (200 filas por chunk)
 *   → SkipsOnFailure + onFailure() (filas inválidas se omiten sin detener el import)
 */
class DailyTrackingImport implements
    ToModel,
    WithHeadingRow,
    WithMapping,
    WithValidation,
    WithChunkReading,
    WithUpserts,
    SkipsOnFailure
{
    use Importable;

    // ──────────────────────────────────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Estados con cobertura confirmada para derivar has_coverage cuando
     * el Excel no trae esa columna explícita.
     */
    private const COVERED_STATES = [
        'CDMX', 'CIUDAD DE MEXICO', 'CIUDAD DE MÉXICO',
        'ESTADO DE MEXICO', 'ESTADO DE MÉXICO', 'EDO MEX', 'EDO. MEX.',
        'JALISCO', 'NUEVO LEON', 'NUEVO LEÓN',
        'PUEBLA', 'QUERETARO', 'QUERÉTARO',
    ];

    /**
     * Mapa de header normalizado → campo del modelo (o alias interno _raw).
     *
     * Los alias internos se procesan en map() antes de asignarse al campo
     * definitivo; no llegan a rules() ni a model().
     *
     * Fuente: DAILY_TRACKING_CONFIG_IMPORT.md — Tabla 1 (CRM) y Tabla 2 (Prospección).
     */
    private const HEADER_MAP = [
        // ── Tabla CRM ─────────────────────────────────────────────────────────
        'cliente_empresa'             => 'customer_name',
        'nombre_cliente'              => 'customer_name',
        'telefono'                    => 'phone',
        // customer_type — normalizeCustomerTypeCode() lo convierte a int 1|2|3
        'tipo_de_servicio'            => 'customer_type',
        'tipo_servicio'               => 'customer_type',
        // customer_category (texto libre, puede venir o calcularse)
        'customer_category'           => 'customer_category',
        'categoria_de_cliente'        => 'customer_category',
        'categoria_cliente'           => 'customer_category',
        // "Estado / Ciudad" como columna fusionada; se parte en map()
        'estado_ciudad'               => 'state_city',
        'estado'                      => 'state',
        'ciudad'                      => 'city',
        'domicilio'                   => 'address',
        'direccion'                   => 'address',
        // contact_method — se normaliza a mayúsculas para WithValidation
        'medio_de_contacto'           => 'contact_method',
        'medio_contacto'              => 'contact_method',
        // status — se normaliza a mayúsculas para WithValidation
        'estatus'                     => 'status',
        'status'                      => 'status',
        'contesto'                    => 'responded',
        'respondio'                   => 'responded',
        'es_recurrente'               => 'is_recurrent',
        'recurrente'                  => 'is_recurrent',
        'se_cotizo'                   => 'quoted',
        'cotizo'                      => 'quoted',
        'se_cerro_el_servicio'        => 'closed',
        'tiene_cobertura'             => 'has_coverage',
        'cobertura'                   => 'has_coverage',
        'monto_cotizado'              => 'quoted_amount',
        'monto_facturado'             => 'billed_amount',
        // payment_method — se normaliza a mayúsculas para WithValidation
        'metodo_pago'                 => 'payment_method',
        'metodo_de_pago'              => 'payment_method',
        'factura'                     => 'invoice',
        // "Fecha" es ambiguo: se trata como service_date (created_at usa fecha_registro)
        'fecha'                       => 'service_date',
        'fecha_servicio'              => 'service_date',
        'fecha_cierre'                => 'close_date',
        'fecha_recibi_pago_servicio'  => 'payment_date',
        'fecha_pago'                  => 'payment_date',
        'fecha_cotizacion'            => 'quote_sent_date',
        'fecha_seguimiento'           => 'follow_up_date',
        'fecha_registro'              => 'created_at',
        'hora'                        => 'service_time',
        'plaga'                       => 'focused_pest',
        'plaga_objetivo'              => 'focused_pest',
        'observaciones'               => 'notes',
        'notas'                       => 'notes',
        // ── Tabla Prospección (misma hoja; columnas adicionales) ──────────────
        // "Cotizacion" puede sobreescribir quoted_amount cuando esté vacío de CRM
        'cotizacion'                  => 'cotizacion_raw',
        // "Fecha programada" sobreescribe service_date cuando existe
        'fecha_programada'            => 'fecha_programada_raw',
        // "CERRO O MOTIVO DE NO CIERRE" → closed=true si empieza con "SI"
        // el texto adicional se concatena a notes
        'cerro_o_motivo_de_no_cierre' => 'cerro_motivo_raw',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Internal state
    // ──────────────────────────────────────────────────────────────────────────

    /** Caché del id del primer Service; evita N+1 durante el import. */
    private ?int $cachedServiceId = null;

    /** Fila ya mapeada por map(); compartida con model() en el mismo invocation. */
    private array $mappedRow = [];

    private array $errors        = [];
    private int   $insertedCount = 0;
    private int   $skippedCount  = 0;

    // ──────────────────────────────────────────────────────────────────────────
    // Maatwebsite concern configuration
    // ──────────────────────────────────────────────────────────────────────────

    public function headingRow(): int
    {
        return 1;
    }

    /** Procesa 200 filas por chunk para no saturar memoria con archivos grandes. */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * Clave única para el upsert.
     * Filas con mismo customer_name + service_date se actualizan, no se duplican.
     *
     * @return string|array<int, string>
     */
    public function uniqueBy(): string|array
    {
        return ['customer_name', 'service_date'];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // map() — normalización de headers y casting de valores crudos del Excel
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Transforma la fila cruda en un array keyed por nombre de campo del modelo.
     * Se ejecuta ANTES de WithValidation, por lo que rules() valida valores ya limpios.
     *
     * @param  array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function map($row): array
    {
        $normalized = [];

        // ── Paso 1: mapear headers reconocidos → campo (first-writer wins) ────
        foreach ($row as $header => $value) {
            $key = $this->normalizeHeader((string) $header);
            if (! isset(self::HEADER_MAP[$key])) {
                continue;
            }
            $field = self::HEADER_MAP[$key];
            if (! array_key_exists($field, $normalized)) {
                $normalized[$field] = $value;
            }
        }

        // ── Paso 2: resolver columnas de Prospección (alias _raw) ─────────────

        // "Cotizacion" (Prospección) rellena quoted_amount sólo si aún está vacío.
        if (isset($normalized['cotizacion_raw'])) {
            $normalized['quoted_amount'] ??= $normalized['cotizacion_raw'];
            unset($normalized['cotizacion_raw']);
        }

        // "Fecha programada" (Prospección) sobreescribe service_date.
        if (isset($normalized['fecha_programada_raw'])) {
            $normalized['service_date'] = $normalized['fecha_programada_raw'];
            unset($normalized['fecha_programada_raw']);
        }

        // "CERRO O MOTIVO DE NO CIERRE" → closed (bool) + concatenar a notes.
        if (isset($normalized['cerro_motivo_raw'])) {
            $raw = trim((string) $normalized['cerro_motivo_raw']);
            if ($raw !== '') {
                // Si el valor comienza con "SI" → closed = true.
                $normalized['closed'] = (bool) preg_match('/^si\b/i', $raw);

                // Texto adicional tras "SI - …" se añade a notes.
                $motivo = trim((string) preg_replace('/^si\s*[-–:]\s*/i', '', $raw));
                if ($motivo !== '' && mb_strtoupper($motivo, 'UTF-8') !== 'SI') {
                    $existing            = trim((string) ($normalized['notes'] ?? ''));
                    $normalized['notes'] = $existing !== ''
                        ? $existing . ' | ' . $motivo
                        : $motivo;
                }
            }
            unset($normalized['cerro_motivo_raw']);
        }

        // ── Paso 3: partir "Estado / Ciudad" fusionado ────────────────────────
        if (isset($normalized['state_city'])) {
            $parts = preg_split('/\s*[\/,\-|]\s*/', trim((string) $normalized['state_city']), 2);
            $normalized['state'] ??= $this->asString($parts[0] ?? null);
            $normalized['city']  ??= $this->asString($parts[1] ?? null);
            unset($normalized['state_city']);
        }

        // ── Paso 4: casting de cada campo a su tipo PHP ───────────────────────

        // Strings
        $normalized['customer_name']     = $this->asString($normalized['customer_name'] ?? null);
        $normalized['phone']             = $this->asString($normalized['phone'] ?? null);
        $normalized['customer_category'] = $this->asString($normalized['customer_category'] ?? null);
        $normalized['address']           = $this->asString($normalized['address'] ?? null);
        $normalized['focused_pest']      = $this->asString($normalized['focused_pest'] ?? null);
        $normalized['notes']             = $this->asString($normalized['notes'] ?? null);
        $normalized['state']             = $this->asString($normalized['state'] ?? null);
        $normalized['city']              = $this->asString($normalized['city'] ?? null);

        // Enums intermedios — uppercase string para WithValidation
        // customer_type se convierte a int 1|2|3 para Rule::in([1,2,3])
        $normalized['customer_type']  = $this->normalizeCustomerTypeCode($normalized['customer_type'] ?? null);
        $normalized['contact_method'] = $this->normalizeToUpper($normalized['contact_method'] ?? null);
        $normalized['status']         = $this->normalizeToUpper($normalized['status'] ?? null);
        $normalized['payment_method'] = $this->normalizeToUpper($normalized['payment_method'] ?? null);

        // Booleans
        $normalized['responded']    = $this->toBool($normalized['responded'] ?? null);
        $normalized['is_recurrent'] = $this->toBool($normalized['is_recurrent'] ?? null);
        $normalized['quoted']       = $this->toBool($normalized['quoted'] ?? null);
        $normalized['closed']       = $this->toBool($normalized['closed'] ?? null);
        $normalized['has_coverage'] = $this->toBool($normalized['has_coverage'] ?? null);

        // Decimals (limpia $, comas de miles)
        $normalized['quoted_amount'] = $this->toDecimal($normalized['quoted_amount'] ?? null);
        $normalized['billed_amount'] = $this->toDecimal($normalized['billed_amount'] ?? null);

        // Fechas
        $normalized['service_date']    = $this->toDate($normalized['service_date'] ?? null);
        $normalized['quote_sent_date'] = $this->toDate($normalized['quote_sent_date'] ?? null);
        $normalized['close_date']      = $this->toDate($normalized['close_date'] ?? null);
        $normalized['payment_date']    = $this->toDate($normalized['payment_date'] ?? null);
        $normalized['follow_up_date']  = $this->toDate($normalized['follow_up_date'] ?? null);
        $normalized['created_at']      = $this->toDateTime($normalized['created_at'] ?? null);

        // Hora (string HH:MM o null)
        $normalized['service_time'] = $this->toTime($normalized['service_time'] ?? null);

        $this->mappedRow = $normalized;

        return $normalized;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // model() — lógica de negocio y construcción del modelo
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Construye un DailyTracking con todos los campos calculados.
     * WithUpserts hará el INSERT OR UPDATE usando uniqueBy().
     *
     * @param  array<string, mixed> $row  (ya procesado por map())
     */
    public function model(array $row): ?DailyTracking
    {
        $m = $this->mappedRow ?: $this->map($row);

        // Si Cliente/Empresa viene vacío en el Excel, se conserva la fila
        // y se asigna un nombre genérico para evitar perder información.
        if (empty($m['customer_name'])) {
            $m['customer_name'] = 'Nombre desconocido';
        }

        // service_id es FK a la tabla services.
        // Se usa el primer Service registrado en BD (cacheado para el import completo).
        $serviceId = $this->resolveServiceId();
        if ($serviceId === null) {
            $this->errors[] = 'No existe un servicio predeterminado en la base de datos.';
            $this->skippedCount++;
            return null;
        }

        $serviceDate = $m['service_date'] ?? now()->toDateString();
        $statusRaw   = $m['status'];

        // ── Regla: separar estado/ciudad si aún viene fusionado ───────────────
        [$state, $city] = $this->splitStateCity($m['state'], $m['city'] ?? null);

        // ── Regla: customer_category — derivar si no vino del Excel ───────────
        $customerCategory = $m['customer_category']
            ?: $this->deriveCustomerCategory($m['customer_type'], $m['quoted_amount']);

        // ── Regla: is_recurrent — desde BD si no vino del Excel ───────────────
        // true si ya hay otros registros con ese nombre de cliente.
        $isRecurrent = $m['is_recurrent']
            || DailyTracking::where('customer_name', $m['customer_name'])->exists();

        // ── Regla: has_coverage — desde estado si no vino del Excel ───────────
        $hasCoverage = $m['has_coverage'] || $this->deriveCoverage($state);

        // ── Regla: quote_sent_date — si cotizó y no hay fecha: service_date -2d
        $quoteSentDate = $m['quote_sent_date'];
        if ($m['quoted'] && ! $quoteSentDate && $serviceDate) {
            $quoteSentDate = Carbon::parse($serviceDate)->subDays(2)->toDateString();
        }

        // ── Regla: follow_up_date — si estado es pendiente/espera: service_date -1d
        $followUpDate = $m['follow_up_date'];
        if (! $followUpDate && $serviceDate
            && in_array($statusRaw, ['PEN', 'ESPERA', 'LEVANTAMIENTO'], true)
        ) {
            $followUpDate = Carbon::parse($serviceDate)->subDay()->toDateString();
        }

        // ── Mapeo de valores raw → string de enum para Eloquent ──────────────
        $customerTypeEnum  = $this->mapCustomerTypeEnum($m['customer_type']);
        $contactMethodEnum = $this->mapContactMethodEnum($m['contact_method']);
        $statusEnum        = $this->mapStatusEnum($statusRaw);
        $quotedEnum        = $m['quoted'] ? DailyTrackingQuoted::YES->value     : DailyTrackingQuoted::PENDING->value;
        $closedEnum        = $m['closed'] ? DailyTrackingClosed::YES->value     : DailyTrackingClosed::NO->value;
        $paymentMethodEnum = $this->mapPaymentMethodEnum($m['payment_method']);
        $invoiceEnum       = $this->mapInvoiceEnum($m['invoice'] ?? null);

        $data = [
            'service_id'        => $serviceId,
            'customer_name'     => $m['customer_name'],
            'phone'             => $m['phone'],
            'customer_type'     => $customerTypeEnum,
            'customer_category' => $customerCategory,
            'state'             => $state,
            'city'              => $city,
            'address'           => $m['address'],
            'contact_method'    => $contactMethodEnum,
            'status'            => $statusEnum,
            'responded'         => $m['responded'],
            'is_recurrent'      => $isRecurrent,
            'quoted'            => $quotedEnum,
            'closed'            => $closedEnum,
            'has_coverage'      => $hasCoverage,
            'quoted_amount'     => $m['quoted_amount'],
            'billed_amount'     => $m['billed_amount'],
            'payment_method'    => $paymentMethodEnum,
            'invoice'           => $invoiceEnum,
            'service_date'      => $serviceDate,
            'quote_sent_date'   => $quoteSentDate,
            'close_date'        => $m['close_date'],
            'payment_date'      => $m['payment_date'],
            'follow_up_date'    => $followUpDate,
            'service_time'      => $m['service_time'],
            'focused_pest'      => $m['focused_pest'],
            'notes'             => $m['notes'],
            // Asignados automáticamente en cada import
            'status_updated_at' => now()->toDateTimeString(),
            'status_updated_by' => Auth::id(),
            // created_at del Excel si existe; si no, timestamp actual
            'created_at'        => $m['created_at'] ?? now()->toDateTimeString(),
            'updated_at'        => now()->toDateTimeString(),
        ];

        $this->insertedCount++;

        // WithUpserts realiza el INSERT OR UPDATE en BD usando uniqueBy().
        return new DailyTracking($data);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // rules() — validación (se ejecuta DESPUÉS de map(), ANTES de model())
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Todos los valores ya están normalizados por map():
     *   customer_type  → int 1|2|3
     *   contact_method → string MAYÚSCULAS
     *   status         → string MAYÚSCULAS
     *   payment_method → string MAYÚSCULAS
     *   booleans       → bool PHP
     *   fechas         → string Y-m-d o null
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_name'     => ['required', 'string', 'max:255'],
            'customer_type'     => ['required', Rule::in([1, 2, 3])],
            'customer_category' => ['nullable', 'string', 'max:255'],
            'contact_method'    => ['nullable', Rule::in(['GOOGLE', 'FB', 'PAGINA', 'INSTAGRAM', 'LLAMADA', 'RECOMENDACION'])],
            'status'            => ['nullable', Rule::in(['PEN', 'N/C', 'CERRADO', 'ESPERA', 'LEVANTAMIENTO', 'NO REQUIERE', 'SIN COBERTURA'])],
            'payment_method'    => ['nullable', Rule::in(['EFECTIVO', 'TRANSFERENCIA', 'AMBAS', 'NO CONFIRMO'])],
            'quoted_amount'     => ['nullable', 'numeric', 'min:0'],
            'billed_amount'     => ['nullable', 'numeric', 'min:0'],
            'service_date'      => ['nullable', 'date'],
            'quote_sent_date'   => ['nullable', 'date'],
            'close_date'        => ['nullable', 'date'],
            'payment_date'      => ['nullable', 'date'],
            'follow_up_date'    => ['nullable', 'date'],
            'service_time'      => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'created_at'        => ['nullable', 'date'],
            'responded'         => ['nullable', 'boolean'],
            'is_recurrent'      => ['nullable', 'boolean'],
            'quoted'            => ['nullable', 'boolean'],
            'closed'            => ['nullable', 'boolean'],
            'has_coverage'      => ['nullable', 'boolean'],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // onFailure() — registrar y omitir filas inválidas
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Cada fila que no pasa WithValidation llega aquí.
     * Se loguea con detalle y se omite sin detener el import completo.
     *
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $message = sprintf(
                '[DailyTrackingImport] Fila %d — campo "%s": %s',
                $failure->row(),
                $failure->attribute(),
                implode('; ', $failure->errors())
            );

            $this->errors[] = $message;
            $this->skippedCount++;

            Log::warning($message, ['values' => $failure->values()]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accessors de resultado
    // ──────────────────────────────────────────────────────────────────────────

    /** @return array<int, string> */
    public function getErrors(): array      { return $this->errors; }
    public function getInsertedCount(): int { return $this->insertedCount; }
    public function getSkippedCount(): int  { return $this->skippedCount; }

    // ──────────────────────────────────────────────────────────────────────────
    // Lógica de negocio
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Retorna el id del primer Service registrado en BD.
     * Se cachea durante la vida del import para evitar N+1.
     *
     * service_id es una FK entera; el formato "SVC-{timestamp}" no aplica
     * porque la columna es de tipo unsigned integer.
     */
    private function resolveServiceId(): ?int
    {
        if ($this->cachedServiceId === null) {
            $this->cachedServiceId = Service::query()->value('id');
        }

        return $this->cachedServiceId;
    }

    /**
     * Termina de separar estado y ciudad cuando map() no pudo hacerlo.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function splitStateCity(?string $rawState, ?string $rawCity): array
    {
        if ($rawCity !== null) {
            return [$rawState, $rawCity];
        }

        if ($rawState === null) {
            return [null, null];
        }

        // Separar por coma, diagonal o guión entre espacios.
        $parts = preg_split('/\s*[,\/\-]\s*/', $rawState, 2);

        if (count($parts) === 2) {
            return [
                $this->asString($parts[0]),
                $this->asString($parts[1]),
            ];
        }

        return [$rawState, null];
    }

    /**
     * Deriva customer_category cuando no viene del Excel.
     *
     * Combina tipo con nivel por monto cotizado:
     *   >= 5000 → Premium
     *   >= 2000 → Medio
     *   <  2000 → Basico
     */
    private function deriveCustomerCategory(?int $typeCode, ?float $amount): ?string
    {
        $typeLabel = match ($typeCode) {
            1 => 'Domestico',
            2 => 'Comercial',
            3 => 'Industrial',
            default => null,
        };

        if ($typeLabel === null) {
            return null;
        }

        if ($amount === null) {
            return $typeLabel;
        }

        $tier = match (true) {
            $amount >= 5000 => 'Premium',
            $amount >= 2000 => 'Medio',
            default         => 'Basico',
        };

        return "{$typeLabel} - {$tier}";
    }

    /**
     * Deriva has_coverage verificando si el estado está en la lista cubierta.
     */
    private function deriveCoverage(?string $state): bool
    {
        if ($state === null) {
            return false;
        }

        $normalized = mb_strtoupper(trim($state), 'UTF-8');

        foreach (self::COVERED_STATES as $covered) {
            if (str_contains($normalized, $covered)) {
                return true;
            }
        }

        return false;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Mapeo raw → valor de enum (string para Eloquent)
    // ──────────────────────────────────────────────────────────────────────────

    /** int 1|2|3 → DailyTrackingCustomerType->value */
    private function mapCustomerTypeEnum(?int $code): string
    {
        return match ($code) {
            1       => DailyTrackingCustomerType::DOMESTICO->value,
            2       => DailyTrackingCustomerType::COMERCIAL->value,
            3       => DailyTrackingCustomerType::INDUSTRIAL->value,
            default => DailyTrackingCustomerType::COMERCIAL->value,
        };
    }

    /**
     * Raw uppercase → DailyTrackingContactMethod->value.
     *
     * FB e INSTAGRAM → 'pagina' (el enum no tiene variantes por red social).
     * RECOMENDACION  → 'cambaceo' (boca a boca / referidos).
     */
    private function mapContactMethodEnum(?string $raw): string
    {
        return match ($raw) {
            'GOOGLE'                    => DailyTrackingContactMethod::GOOGLE->value,
            'FB', 'PAGINA', 'INSTAGRAM' => DailyTrackingContactMethod::PAGINA->value,
            'RECOMENDACION'             => DailyTrackingContactMethod::CAMBACEO->value,
            default                     => DailyTrackingContactMethod::LLAMADA->value,
        };
    }

    /**
     * Raw uppercase → DailyTrackingStatus->value.
     *
     * PEN / ESPERA / LEVANTAMIENTO → survey  (pendientes / en proceso)
     * N/C / NO REQUIERE / SIN COBERTURA → no_requiere
     * CERRADO → closed
     */
    private function mapStatusEnum(?string $raw): string
    {
        return match ($raw) {
            'CERRADO'                               => DailyTrackingStatus::CLOSED->value,
            'N/C', 'NO REQUIERE', 'SIN COBERTURA'  => DailyTrackingStatus::NO_REQUIERE->value,
            default                                 => DailyTrackingStatus::SURVEY->value,
        };
    }

    /**
     * Raw uppercase → DailyTrackingPaymentMethod->value.
     *
     * AMBAS / NO CONFIRMO → 'other' (sin equivalente directo en el enum).
     */
    private function mapPaymentMethodEnum(?string $raw): ?string
    {
        return match ($raw) {
            'EFECTIVO'             => DailyTrackingPaymentMethod::CASH->value,
            'TRANSFERENCIA'        => DailyTrackingPaymentMethod::TRANSFER->value,
            'AMBAS', 'NO CONFIRMO' => DailyTrackingPaymentMethod::OTHER->value,
            default                => null,
        };
    }

    /** Celda de factura → DailyTrackingInvoice->value */
    private function mapInvoiceEnum(mixed $value): string
    {
        $raw = mb_strtoupper(trim((string) ($value ?? '')), 'UTF-8');

        return match ($raw) {
            'SI', 'SÍ', 'YES', '1', 'TRUE' => DailyTrackingInvoice::YES->value,
            'NO', '0', 'FALSE'              => DailyTrackingInvoice::NO->value,
            default                         => DailyTrackingInvoice::NOT_APPLICABLE->value,
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de casting / normalización
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Normaliza un header de Excel a snake_case ASCII sin tildes ni especiales.
     *
     * Ejemplos:
     *   "¿Contestó?"                    → "contesto"
     *   "Estado / Ciudad"               → "estado_ciudad"
     *   "CERRO O MOTIVO DE NO CIERRE"   → "cerro_o_motivo_de_no_cierre"
     *   "FECHA RECIBI PAGO SERVICIO"    → "fecha_recibi_pago_servicio"
     */
    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header), 'UTF-8');

        // Eliminar tildes y diacríticos vía transliteración ASCII.
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header);
        if ($ascii !== false && $ascii !== '') {
            $header = $ascii;
        }

        // Reemplazar caracteres especiales y separadores por espacio.
        $header = str_replace(['¿', '¡', '?', '!', '/', '-', '.', ':'], ' ', $header);

        // Colapsar múltiples espacios a guión bajo.
        $header = (string) preg_replace('/\s+/', '_', $header);

        return trim($header, '_');
    }

    /**
     * Convierte a string trimming whitespace. Retorna null si queda vacío.
     */
    private function asString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        return $s === '' ? null : $s;
    }

    /**
     * Normaliza a string MAYÚSCULAS para enums intermedios validados.
     * Retorna null si el valor es vacío.
     */
    private function normalizeToUpper(mixed $value): ?string
    {
        $raw = mb_strtoupper(trim((string) ($value ?? '')), 'UTF-8');
        return $raw === '' ? null : $raw;
    }

    /**
     * Mapea el label del Excel a int 1|2|3 para WithValidation (Rule::in([1,2,3])).
     *
     * Acepta variantes: "Domestico", "Doméstico", "Comercial", "Industrial / Planta",
     * "PLANTA", o directamente "1", "2", "3".
     * Retorna null si no reconoce el valor (WithValidation rechazará la fila).
     */
    private function normalizeCustomerTypeCode(mixed $value): ?int
    {
        $raw = mb_strtoupper(trim((string) ($value ?? '')), 'UTF-8');

        return match (true) {
            str_contains($raw, 'DOMESTICO') || str_contains($raw, 'DOMÉSTICO') => 1,
            str_contains($raw, 'COMERCIAL')                                     => 2,
            str_contains($raw, 'INDUSTRIAL') || str_contains($raw, 'PLANTA')   => 3,
            $raw === '1'                                                         => 1,
            $raw === '2'                                                         => 2,
            $raw === '3'                                                         => 3,
            default                                                              => null,
        };
    }

    /**
     * Convierte valores truthy/falsy comunes en español e inglés a bool.
     *
     * Truthy: SI, SÍ, YES, TRUE, 1, X, S
     * Falsy:  cualquier otra cosa (incluyendo null y cadena vacía)
     */
    private function toBool(mixed $value): bool
    {
        $raw = mb_strtoupper(trim((string) ($value ?? '')), 'UTF-8');
        return in_array($raw, ['SI', 'SÍ', 'YES', 'TRUE', '1', 'X', 'S'], true);
    }

    /**
     * Convierte a float limpiando símbolo $, comas de miles y espacios.
     * Retorna null para celdas vacías o no numéricas.
     */
    private function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Eliminar $, comas de separador de miles y espacios.
        $clean = (string) preg_replace('/[$,\s]/', '', (string) $value);

        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * Parsea una fecha a string Y-m-d.
     *
     * Soporta:
     *   - Número serial de Excel (días desde 1899-12-30)
     *   - String en cualquier formato reconocible por Carbon
     */
    private function toDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                // Excel serial: días desde 1899-12-30 → UNIX timestamp
                $unix = ((int) $value - 25569) * 86400;
                return Carbon::createFromTimestampUTC($unix)->toDateString();
            }

            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parsea una fecha-hora a string Y-m-d H:i:s.
     * Misma lógica que toDate() pero retorna datetime completo.
     */
    private function toDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $unix = ((int) $value - 25569) * 86400;
                return Carbon::createFromTimestampUTC($unix)->toDateTimeString();
            }

            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parsea una hora a string HH:MM.
     *
     * Soporta:
     *   - Fracción decimal de Excel (0.5 = 12:00, 0.75 = 18:00)
     *   - String de hora reconocible por Carbon ("14:30", "2:30 PM", etc.)
     */
    private function toTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // Excel almacena time como fracción del día (0.0 a <1.0)
            if (is_numeric($value) && (float) $value < 1) {
                $totalSeconds = (int) round((float) $value * 86400);
                return sprintf('%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60));
            }

            return Carbon::parse((string) $value)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
