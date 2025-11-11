<?php

namespace App\Enums;

enum PayrollPerception: string
{
    case SUELDOS_SALARIOS_RAYAS_JORNALES = '001';
    case GRATIFICACION_ANUAL_AGUINALDO = '002';
    case PARTICIPACION_TRABAJADORES_UTILIDADES_PTU = '003';
    case REEMBOLSO_GASTOS_MEDICOS_DENTALES_HOSPITALARIOS = '004';
    case FONDO_AHORRO = '005';
    case CAJA_AHORRO = '006';
    case CONTRIBUCIONES_CARGO_TRABAJADOR_PAGADAS_PATRON = '009';
    case PREMIOS_PUNTUALIDAD = '010';
    case PRIMA_SEGURO_VIDA = '011';
    case SEGURO_GASTOS_MEDICOS_MAYORES = '012';
    case CUOTAS_SINDICALES_PAGADAS_PATRON = '013';
    case SUBSIDIOS_INCAPACIDAD = '014';
    case BECAS_TRABAJADORES_HIJOS = '015';
    case HORAS_EXTRA = '019';
    case PRIMA_DOMINICAL = '020';
    case PRIMA_VACACIONAL = '021';
    case PRIMA_ANTIGUEDAD = '022';
    case PAGOS_SEPARACION = '023';
    case SEGURO_RETIRO = '024';
    case INDEMNIZACIONES = '025';
    case REEMBOLSO_FUNERAL = '026';
    case CUOTAS_SEGURIDAD_SOCIAL_PAGADAS_PATRON = '027';
    case COMISIONES = '028';
    case VALES_DESPENSA = '029';
    case VALES_RESTAURANTE = '030';
    case VALES_GASOLINA = '031';
    case VALES_ROPA = '032';
    case AYUDA_RENTA = '033';
    case AYUDA_ARTICULOS_ESCOLARES = '034';
    case AYUDA_ANTEOJOS = '035';
    case AYUDA_TRANSPORTE = '036';
    case AYUDA_GASTOS_FUNERAL = '037';
    case OTROS_INGRESOS_SALARIOS = '038';
    case JUBILACIONES_PENSIONES_HABERES_RETIRO = '039';
    case JUBILACIONES_PENSIONES_HABERES_RETIRO_PARCIALIDADES = '044';
    case INGRESOS_ACCIONES_TITULOS_VALOR = '045';
    case INGRESOS_ASIMILADOS_SALARIOS = '046';
    case ALIMENTACION = '047';
    case HABITACION = '048';
    case PREMIOS_ASISTENCIA = '049';
    case VIATICOS = '050';
    case PAGOS_GRATIFICACIONES_PRIMAS_COMPENSACIONES_EXTRABAJADORES_JUBILACION_PARCIALIDADES = '051';
    case PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_RESOLUCION_JUDICIAL = '052';
    case PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_RESOLUCION_JUDICIAL = '053';

    public function name(): string
    {
        return match ($this) {
            self::SUELDOS_SALARIOS_RAYAS_JORNALES => 'Sueldos, Salarios Rayas y Jornales',
            self::GRATIFICACION_ANUAL_AGUINALDO => 'Gratificación Anual (Aguinaldo)',
            self::PARTICIPACION_TRABAJADORES_UTILIDADES_PTU => 'Participación de los Trabajadores en las Utilidades PTU',
            self::REEMBOLSO_GASTOS_MEDICOS_DENTALES_HOSPITALARIOS => 'Reembolso de Gastos Médicos Dentales y Hospitalarios',
            self::FONDO_AHORRO => 'Fondo de Ahorro',
            self::CAJA_AHORRO => 'Caja de ahorro',
            self::CONTRIBUCIONES_CARGO_TRABAJADOR_PAGADAS_PATRON => 'Contribuciones a Cargo del Trabajador Pagadas por el Patrón',
            self::PREMIOS_PUNTUALIDAD => 'Premios por puntualidad',
            self::PRIMA_SEGURO_VIDA => 'Prima de Seguro de vida',
            self::SEGURO_GASTOS_MEDICOS_MAYORES => 'Seguro de Gastos Médicos Mayores',
            self::CUOTAS_SINDICALES_PAGADAS_PATRON => 'Cuotas Sindicales Pagadas por el Patrón',
            self::SUBSIDIOS_INCAPACIDAD => 'Subsidios por incapacidad',
            self::BECAS_TRABAJADORES_HIJOS => 'Becas para trabajadores y/o hijos',
            self::HORAS_EXTRA => 'Horas extra',
            self::PRIMA_DOMINICAL => 'Prima dominical',
            self::PRIMA_VACACIONAL => 'Prima vacacional',
            self::PRIMA_ANTIGUEDAD => 'Prima por antigüedad',
            self::PAGOS_SEPARACION => 'Pagos por separación',
            self::SEGURO_RETIRO => 'Seguro de retiro',
            self::INDEMNIZACIONES => 'Indemnizaciones',
            self::REEMBOLSO_FUNERAL => 'Reembolso por funeral',
            self::CUOTAS_SEGURIDAD_SOCIAL_PAGADAS_PATRON => 'Cuotas de seguridad social pagadas por el patrón',
            self::COMISIONES => 'Comisiones',
            self::VALES_DESPENSA => 'Vales de despensa',
            self::VALES_RESTAURANTE => 'Vales de restaurante',
            self::VALES_GASOLINA => 'Vales de gasolina',
            self::VALES_ROPA => 'Vales de ropa',
            self::AYUDA_RENTA => 'Ayuda para renta',
            self::AYUDA_ARTICULOS_ESCOLARES => 'Ayuda para artículos escolares',
            self::AYUDA_ANTEOJOS => 'Ayuda para anteojos',
            self::AYUDA_TRANSPORTE => 'Ayuda para transporte',
            self::AYUDA_GASTOS_FUNERAL => 'Ayuda para gastos de funeral',
            self::OTROS_INGRESOS_SALARIOS => 'Otros ingresos por salarios',
            self::JUBILACIONES_PENSIONES_HABERES_RETIRO => 'Jubilaciones, pensiones o haberes de retiro',
            self::JUBILACIONES_PENSIONES_HABERES_RETIRO_PARCIALIDADES => 'Jubilaciones, pensiones o haberes de retiro en parcialidades',
            self::INGRESOS_ACCIONES_TITULOS_VALOR => 'Ingresos en acciones o títulos valor que representan bienes',
            self::INGRESOS_ASIMILADOS_SALARIOS => 'Ingresos asimilados a salarios',
            self::ALIMENTACION => 'Alimentación',
            self::HABITACION => 'Habitación',
            self::PREMIOS_ASISTENCIA => 'Premios por asistencia',
            self::VIATICOS => 'Viáticos',
            self::PAGOS_GRATIFICACIONES_PRIMAS_COMPENSACIONES_EXTRABAJADORES_JUBILACION_PARCIALIDADES => 'Pagos por gratificaciones, primas, compensaciones, recompensas u otros a extrabajadores derivados de jubilación en parcialidades',
            self::PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_RESOLUCION_JUDICIAL => 'Pagos que se realicen a extrabajadores que obtengan una jubilación en parcialidades derivados de la ejecución de resoluciones judicial o de un laudo',
            self::PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_RESOLUCION_JUDICIAL => 'Pagos que se realicen a extrabajadores que obtengan una jubilación en una sola exhibición derivados de la ejecución de resoluciones judicial o de un laudo',
        };
    }

    public function shortName(): string
    {
        return match ($this) {
            self::SUELDOS_SALARIOS_RAYAS_JORNALES => 'Sueldos y Salarios',
            self::GRATIFICACION_ANUAL_AGUINALDO => 'Aguinaldo',
            self::PARTICIPACION_TRABAJADORES_UTILIDADES_PTU => 'PTU',
            self::REEMBOLSO_GASTOS_MEDICOS_DENTALES_HOSPITALARIOS => 'Reembolso Médico',
            self::FONDO_AHORRO => 'Fondo Ahorro',
            self::CAJA_AHORRO => 'Caja Ahorro',
            self::CONTRIBUCIONES_CARGO_TRABAJADOR_PAGADAS_PATRON => 'Contribuciones Patrón',
            self::PREMIOS_PUNTUALIDAD => 'Premio Puntualidad',
            self::PRIMA_SEGURO_VIDA => 'Seguro Vida',
            self::SEGURO_GASTOS_MEDICOS_MAYORES => 'Gastos Médicos Mayores',
            self::CUOTAS_SINDICALES_PAGADAS_PATRON => 'Cuotas Sindicales',
            self::SUBSIDIOS_INCAPACIDAD => 'Subsidio Incapacidad',
            self::BECAS_TRABAJADORES_HIJOS => 'Becas',
            self::HORAS_EXTRA => 'Horas Extra',
            self::PRIMA_DOMINICAL => 'Prima Dominical',
            self::PRIMA_VACACIONAL => 'Prima Vacacional',
            self::PRIMA_ANTIGUEDAD => 'Prima Antigüedad',
            self::PAGOS_SEPARACION => 'Pagos Separación',
            self::SEGURO_RETIRO => 'Seguro Retiro',
            self::INDEMNIZACIONES => 'Indemnizaciones',
            self::REEMBOLSO_FUNERAL => 'Reembolso Funeral',
            self::CUOTAS_SEGURIDAD_SOCIAL_PAGADAS_PATRON => 'Cuotas IMSS Patrón',
            self::COMISIONES => 'Comisiones',
            self::VALES_DESPENSA => 'Vales Despensa',
            self::VALES_RESTAURANTE => 'Vales Restaurante',
            self::VALES_GASOLINA => 'Vales Gasolina',
            self::VALES_ROPA => 'Vales Ropa',
            self::AYUDA_RENTA => 'Ayuda Renta',
            self::AYUDA_ARTICULOS_ESCOLARES => 'Ayuda Escolar',
            self::AYUDA_ANTEOJOS => 'Ayuda Anteojos',
            self::AYUDA_TRANSPORTE => 'Ayuda Transporte',
            self::AYUDA_GASTOS_FUNERAL => 'Ayuda Funeral',
            self::OTROS_INGRESOS_SALARIOS => 'Otros Ingresos',
            self::JUBILACIONES_PENSIONES_HABERES_RETIRO => 'Jubilación',
            self::JUBILACIONES_PENSIONES_HABERES_RETIRO_PARCIALIDADES => 'Jubilación Parcialidades',
            self::INGRESOS_ACCIONES_TITULOS_VALOR => 'Ingresos Acciones',
            self::INGRESOS_ASIMILADOS_SALARIOS => 'Ingresos Asimilados',
            self::ALIMENTACION => 'Alimentación',
            self::HABITACION => 'Habitación',
            self::PREMIOS_ASISTENCIA => 'Premio Asistencia',
            self::VIATICOS => 'Viáticos',
            self::PAGOS_GRATIFICACIONES_PRIMAS_COMPENSACIONES_EXTRABAJADORES_JUBILACION_PARCIALIDADES => 'Pagos Extrabajadores Parcialidades',
            self::PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_RESOLUCION_JUDICIAL => 'Pagos Extrabajadores Judicial Parcialidades',
            self::PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_RESOLUCION_JUDICIAL => 'Pagos Extrabajadores Judicial Exhibición',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::SUELDOS_SALARIOS_RAYAS_JORNALES,
            self::HORAS_EXTRA,
            self::COMISIONES => 'sueldos_basicos',

            self::GRATIFICACION_ANUAL_AGUINALDO,
            self::PARTICIPACION_TRABAJADORES_UTILIDADES_PTU,
            self::PREMIOS_PUNTUALIDAD,
            self::PREMIOS_ASISTENCIA => 'bonos_gratificaciones',

            self::PRIMA_VACACIONAL,
            self::PRIMA_DOMINICAL,
            self::PRIMA_ANTIGUEDAD => 'primas',

            self::VALES_DESPENSA,
            self::VALES_RESTAURANTE,
            self::VALES_GASOLINA,
            self::VALES_ROPA,
            self::ALIMENTACION,
            self::HABITACION => 'prestaciones_especie',

            self::AYUDA_RENTA,
            self::AYUDA_ARTICULOS_ESCOLARES,
            self::AYUDA_ANTEOJOS,
            self::AYUDA_TRANSPORTE,
            self::AYUDA_GASTOS_FUNERAL,
            self::BECAS_TRABAJADORES_HIJOS => 'ayudas_asistencias',

            self::FONDO_AHORRO,
            self::CAJA_AHORRO,
            self::PRIMA_SEGURO_VIDA,
            self::SEGURO_GASTOS_MEDICOS_MAYORES,
            self::SEGURO_RETIRO => 'ahorros_seguros',

            self::PAGOS_SEPARACION,
            self::INDEMNIZACIONES,
            self::JUBILACIONES_PENSIONES_HABERES_RETIRO,
            self::JUBILACIONES_PENSIONES_HABERES_RETIRO_PARCIALIDADES,
            self::PAGOS_GRATIFICACIONES_PRIMAS_COMPENSACIONES_EXTRABAJADORES_JUBILACION_PARCIALIDADES,
            self::PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_RESOLUCION_JUDICIAL,
            self::PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_RESOLUCION_JUDICIAL => 'liquidacion_jubilacion',

            self::REEMBOLSO_GASTOS_MEDICOS_DENTALES_HOSPITALARIOS,
            self::REEMBOLSO_FUNERAL,
            self::VIATICOS => 'reembolsos_viaticos',

            self::CONTRIBUCIONES_CARGO_TRABAJADOR_PAGADAS_PATRON,
            self::CUOTAS_SINDICALES_PAGADAS_PATRON,
            self::CUOTAS_SEGURIDAD_SOCIAL_PAGADAS_PATRON => 'contribuciones',

            self::SUBSIDIOS_INCAPACIDAD => 'subsidios',

            self::OTROS_INGRESOS_SALARIOS,
            self::INGRESOS_ACCIONES_TITULOS_VALOR,
            self::INGRESOS_ASIMILADOS_SALARIOS => 'otros_ingresos',
        };
    }

    public function isExempt(): bool
    {
        return match ($this) {
            self::VALES_DESPENSA,
            self::FONDO_AHORRO,
            self::CAJA_AHORRO,
            self::REEMBOLSO_GASTOS_MEDICOS_DENTALES_HOSPITALARIOS,
            self::SEGURO_GASTOS_MEDICOS_MAYORES => true,
            default => false,
        };
    }

    public function isTaxable(): bool
    {
        return !$this->isExempt();
    }

    public function isRegular(): bool
    {
        return match ($this) {
            self::SUELDOS_SALARIOS_RAYAS_JORNALES,
            self::COMISIONES => true,
            default => false,
        };
    }

    public function isIrregular(): bool
    {
        return !$this->isRegular();
    }

    public function requiresIMSS(): bool
    {
        return match ($this) {
            self::SUELDOS_SALARIOS_RAYAS_JORNALES,
            self::HORAS_EXTRA,
            self::PRIMA_DOMINICAL,
            self::PRIMA_VACACIONAL => true,
            default => false,
        };
    }

    public static function getDescriptions(): array
    {
        $descriptions = [];
        foreach (self::cases() as $case) {
            $descriptions[$case->value] = $case->name();
        }
        return $descriptions;
    }

    public static function getByCategory(string $category): array
    {
        return array_filter(self::cases(), function ($case) use ($category) {
            return $case->category() === $category;
        });
    }

    public static function getExempt(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isExempt();
        });
    }

    public static function getTaxable(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isTaxable();
        });
    }

    public static function getRegular(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isRegular();
        });
    }

    public static function isValid(string $code): bool
    {
        return !is_null(self::tryFrom($code));
    }

    public static function getDescriptionByCode(string $code): ?string
    {
        $perception = self::tryFrom($code);
        return $perception ? $perception->name() : null;
    }

    public static function getCategories(): array
    {
        return [
            'sueldos_basicos' => 'Sueldos y Básicos',
            'bonos_gratificaciones' => 'Bonos y Gratificaciones',
            'primas' => 'Primas',
            'prestaciones_especie' => 'Prestaciones en Especie',
            'ayudas_asistencias' => 'Ayudas y Asistencias',
            'ahorros_seguros' => 'Ahorros y Seguros',
            'liquidacion_jubilacion' => 'Liquidación y Jubilación',
            'reembolsos_viaticos' => 'Reembolsos y Viáticos',
            'contribuciones' => 'Contribuciones',
            'subsidios' => 'Subsidios',
            'otros_ingresos' => 'Otros Ingresos',
        ];
    }

    public static function getPerceptionTypes(): array
    {
        $perceptionTypes = [];
        foreach (self::cases() as $case) {
            $perceptionTypes[$case->value] = $case->name();
        }
        return $perceptionTypes;
    }
}