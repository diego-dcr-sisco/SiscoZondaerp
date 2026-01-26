<?php

namespace App\Enums;

enum PerceptionType: string
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
    case PAGOS_GRATIFICACIONES_PRIMAS_EXTRABAJADORES_JUBILACION_PARCIALIDADES = '051';
    case PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_RESOLUCION = '052';
    case PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_RESOLUCION = '053';

    public function label(): string
    {
        return match($this) {
            self::SUELDOS_SALARIOS_RAYAS_JORNALES => 'Sueldos, Salarios  Rayas y Jornales',
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
            self::PAGOS_GRATIFICACIONES_PRIMAS_EXTRABAJADORES_JUBILACION_PARCIALIDADES => 'Pagos por gratificaciones, primas, compensaciones, recompensas u otros a extrabajadores derivados de jubilación en parcialidades',
            self::PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_RESOLUCION => 'Pagos que se realicen a extrabajadores que obtengan una jubilación en parcialidades derivados de la ejecución de resoluciones judicial o de un laudo',
            self::PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_RESOLUCION => 'Pagos que se realicen a extrabajadores que obtengan una jubilación en una sola exhibición derivados de la ejecución de resoluciones judicial o de un laudo',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }

    public static function getLabelByValue(string $value): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->label();
            }
        }
        return null;
    }

    public static function getCaseByValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return null;
    }
}