<?php

namespace App\Enums;

enum PayrollDeduction: string
{
    case SEGURIDAD_SOCIAL = '001';
    case ISR = '002';
    case APORTACIONES_RETIRO_CESANTIA_VEJEZ = '003';
    case OTROS = '004';
    case APORTACIONES_FONDO_VIVIENDA = '005';
    case DESCUENTO_INCAPACIDAD = '006';
    case PENSION_ALIMENTICIA = '007';
    case RENTA = '008';
    case PRESTAMOS_FONAVI = '009';
    case PAGO_CREDITO_VIVIENDA = '010';
    case PAGO_ABONOS_INFONACOT = '011';
    case ANTICIPO_SALARIOS = '012';
    case PAGOS_EXCESO_TRABAJADOR = '013';
    case ERRORES = '014';
    case PERDIDAS = '015';
    case AVERIAS = '016';
    case ADQUISICION_ARTICULOS_EMPRESA = '017';
    case CUOTAS_SOCIEDADES_COOPERATIVAS_CAJAS_AHORRO = '018';
    case CUOTAS_SINDICALES = '019';
    case AUSENCIA_AUSENTISMO = '020';
    case CUOTAS_OBRERO_PATRONALES = '021';
    case IMPUESTOS_LOCALES = '022';
    case APORTACIONES_VOLUNTARIAS = '023';
    case AJUSTE_AGUINALDO_EXENTO = '024';
    case AJUSTE_AGUINALDO_GRAVADO = '025';
    case AJUSTE_PTU_EXENTO = '026';
    case AJUSTE_PTU_GRAVADO = '027';
    case AJUSTE_REEMBOLSO_MEDICO_EXENTO = '028';
    case AJUSTE_FONDO_AHORRO_EXENTO = '029';
    case AJUSTE_CAJA_AHORRO_EXENTO = '030';
    case AJUSTE_CONTRIBUCIONES_PATRON_EXENTO = '031';
    case AJUSTE_PREMIOS_PUNTUALIDAD_GRAVADO = '032';
    case AJUSTE_PRIMA_SEGURO_VIDA_EXENTO = '033';
    case AJUSTE_SEGURO_GASTOS_MEDICOS_MAYORES_EXENTO = '034';
    case AJUSTE_CUOTAS_SINDICALES_PATRON_EXENTO = '035';
    case AJUSTE_SUBSIDIOS_INCAPACIDAD_EXENTO = '036';
    case AJUSTE_BECAS_EXENTO = '037';
    case AJUSTE_HORAS_EXTRA_EXENTO = '038';
    case AJUSTE_HORAS_EXTRA_GRAVADO = '039';
    case AJUSTE_PRIMA_DOMINICAL_EXENTO = '040';
    case AJUSTE_PRIMA_DOMINICAL_GRAVADO = '041';
    case AJUSTE_PRIMA_VACACIONAL_EXENTO = '042';
    case AJUSTE_PRIMA_VACACIONAL_GRAVADO = '043';
    case AJUSTE_PRIMA_ANTIGUEDAD_EXENTO = '044';
    case AJUSTE_PRIMA_ANTIGUEDAD_GRAVADO = '045';
    case AJUSTE_PAGOS_SEPARACION_EXENTO = '046';
    case AJUSTE_PAGOS_SEPARACION_GRAVADO = '047';
    case AJUSTE_SEGURO_RETIRO_EXENTO = '048';
    case AJUSTE_INDEMNIZACIONES_EXENTO = '049';
    case AJUSTE_INDEMNIZACIONES_GRAVADO = '050';
    case AJUSTE_REEMBOLSO_FUNERAL_EXENTO = '051';
    case AJUSTE_CUOTAS_SEGURIDAD_SOCIAL_PATRON_EXENTO = '052';
    case AJUSTE_COMISIONES_GRAVADO = '053';
    case AJUSTE_VALES_DESPENSA_EXENTO = '054';
    case AJUSTE_VALES_RESTAURANTE_EXENTO = '055';
    case AJUSTE_VALES_GASOLINA_EXENTO = '056';
    case AJUSTE_VALES_ROPA_EXENTO = '057';
    case AJUSTE_AYUDA_RENTA_EXENTO = '058';
    case AJUSTE_AYUDA_ARTICULOS_ESCOLARES_EXENTO = '059';
    case AJUSTE_AYUDA_ANTEOJOS_EXENTO = '060';
    case AJUSTE_AYUDA_TRANSPORTE_EXENTO = '061';
    case AJUSTE_AYUDA_GASTOS_FUNERAL_EXENTO = '062';
    case AJUSTE_OTROS_INGRESOS_SALARIOS_EXENTO = '063';
    case AJUSTE_OTROS_INGRESOS_SALARIOS_GRAVADO = '064';
    case AJUSTE_JUBILACION_EXHIBICION_EXENTO = '065';
    case AJUSTE_JUBILACION_EXHIBICION_GRAVADO = '066';
    case AJUSTE_PAGOS_SEPARACION_ACUMULABLE = '067';
    case AJUSTE_PAGOS_SEPARACION_NO_ACUMULABLE = '068';
    case AJUSTE_JUBILACION_PARCIALIDADES_EXENTO = '069';
    case AJUSTE_JUBILACION_PARCIALIDADES_GRAVADO = '070';
    case AJUSTE_SUBSIDIO_EMPLEO = '071';
    case AJUSTE_INGRESOS_ACCIONES_EXENTO = '072';
    case AJUSTE_INGRESOS_ACCIONES_GRAVADO = '073';
    case AJUSTE_ALIMENTACION_EXENTO = '074';
    case AJUSTE_ALIMENTACION_GRAVADO = '075';
    case AJUSTE_HABITACION_EXENTO = '076';
    case AJUSTE_HABITACION_GRAVADO = '077';
    case AJUSTE_PREMIOS_ASISTENCIA = '078';
    case AJUSTE_PAGOS_DISTINTOS = '079';
    case AJUSTE_VIATICOS_GRAVADOS = '080';
    case AJUSTE_VIATICOS_ENTREGADOS = '081';
    case AJUSTE_FONDO_AHORRO_GRAVADO = '082';
    case AJUSTE_CAJA_AHORRO_GRAVADO = '083';
    case AJUSTE_PRIMA_SEGURO_VIDA_GRAVADO = '084';
    case AJUSTE_SEGURO_GASTOS_MEDICOS_MAYORES_GRAVADO = '085';
    case AJUSTE_SUBSIDIOS_INCAPACIDAD_GRAVADO = '086';
    case AJUSTE_BECAS_GRAVADO = '087';
    case AJUSTE_SEGURO_RETIRO_GRAVADO = '088';
    case AJUSTE_VALES_DESPENSA_GRAVADO = '089';
    case AJUSTE_VALES_RESTAURANTE_GRAVADO = '090';
    case AJUSTE_VALES_GASOLINA_GRAVADO = '091';
    case AJUSTE_VALES_ROPA_GRAVADO = '092';
    case AJUSTE_AYUDA_RENTA_GRAVADO = '093';
    case AJUSTE_AYUDA_ARTICULOS_ESCOLARES_GRAVADO = '094';
    case AJUSTE_AYUDA_ANTEOJOS_GRAVADO = '095';
    case AJUSTE_AYUDA_TRANSPORTE_GRAVADO = '096';
    case AJUSTE_AYUDA_GASTOS_FUNERAL_GRAVADO = '097';
    case AJUSTE_INGRESOS_ASIMILADOS_GRAVADOS = '098';
    case AJUSTE_SUELDOS_SALARIOS_GRAVADOS = '099';
    case AJUSTE_VIATICOS_EXENTOS = '100';
    case ISR_RETENIDO_EJERCICIO_ANTERIOR = '101';
    case AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_GRAVADO = '102';
    case AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_JUDICIAL_GRAVADO = '103';
    case AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_JUDICIAL_EXENTO = '104';
    case AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_JUDICIAL_GRAVADO = '105';
    case AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_JUDICIAL_EXENTO = '106';
    case AJUSTE_SUBSIDIO_CAUSADO = '107';

    public function name(): string
    {
        return match($this) {
            self::SEGURIDAD_SOCIAL => 'Seguridad social',
            self::ISR => 'ISR',
            self::APORTACIONES_RETIRO_CESANTIA_VEJEZ => 'Aportaciones a retiro, cesantía en edad avanzada y vejez.',
            self::OTROS => 'Otros',
            self::APORTACIONES_FONDO_VIVIENDA => 'Aportaciones a Fondo de vivienda',
            self::DESCUENTO_INCAPACIDAD => 'Descuento por incapacidad',
            self::PENSION_ALIMENTICIA => 'Pensión alimenticia',
            self::RENTA => 'Renta',
            self::PRESTAMOS_FONAVI => 'Préstamos provenientes del Fondo Nacional de la Vivienda para los Trabajadores',
            self::PAGO_CREDITO_VIVIENDA => 'Pago por crédito de vivienda',
            self::PAGO_ABONOS_INFONACOT => 'Pago de abonos INFONACOT',
            self::ANTICIPO_SALARIOS => 'Anticipo de salarios',
            self::PAGOS_EXCESO_TRABAJADOR => 'Pagos hechos con exceso al trabajador',
            self::ERRORES => 'Errores',
            self::PERDIDAS => 'Pérdidas',
            self::AVERIAS => 'Averías',
            self::ADQUISICION_ARTICULOS_EMPRESA => 'Adquisición de artículos producidos por la empresa o establecimiento',
            self::CUOTAS_SOCIEDADES_COOPERATIVAS_CAJAS_AHORRO => 'Cuotas para la constitución y fomento de sociedades cooperativas y de cajas de ahorro',
            self::CUOTAS_SINDICALES => 'Cuotas sindicales',
            self::AUSENCIA_AUSENTISMO => 'Ausencia (Ausentismo)',
            self::CUOTAS_OBRERO_PATRONALES => 'Cuotas obrero patronales',
            self::IMPUESTOS_LOCALES => 'Impuestos Locales',
            self::APORTACIONES_VOLUNTARIAS => 'Aportaciones voluntarias',
            self::AJUSTE_AGUINALDO_EXENTO => 'Ajuste en Gratificación Anual (Aguinaldo) Exento',
            self::AJUSTE_AGUINALDO_GRAVADO => 'Ajuste en Gratificación Anual (Aguinaldo) Gravado',
            self::AJUSTE_PTU_EXENTO => 'Ajuste en Participación de los Trabajadores en las Utilidades PTU Exento',
            self::AJUSTE_PTU_GRAVADO => 'Ajuste en Participación de los Trabajadores en las Utilidades PTU Gravado',
            self::AJUSTE_REEMBOLSO_MEDICO_EXENTO => 'Ajuste en Reembolso de Gastos Médicos Dentales y Hospitalarios Exento',
            self::AJUSTE_FONDO_AHORRO_EXENTO => 'Ajuste en Fondo de ahorro Exento',
            self::AJUSTE_CAJA_AHORRO_EXENTO => 'Ajuste en Caja de ahorro Exento',
            self::AJUSTE_CONTRIBUCIONES_PATRON_EXENTO => 'Ajuste en Contribuciones a Cargo del Trabajador Pagadas por el Patrón Exento',
            self::AJUSTE_PREMIOS_PUNTUALIDAD_GRAVADO => 'Ajuste en Premios por puntualidad Gravado',
            self::AJUSTE_PRIMA_SEGURO_VIDA_EXENTO => 'Ajuste en Prima de Seguro de vida Exento',
            self::AJUSTE_SEGURO_GASTOS_MEDICOS_MAYORES_EXENTO => 'Ajuste en Seguro de Gastos Médicos Mayores Exento',
            self::AJUSTE_CUOTAS_SINDICALES_PATRON_EXENTO => 'Ajuste en Cuotas Sindicales Pagadas por el Patrón Exento',
            self::AJUSTE_SUBSIDIOS_INCAPACIDAD_EXENTO => 'Ajuste en Subsidios por incapacidad Exento',
            self::AJUSTE_BECAS_EXENTO => 'Ajuste en Becas para trabajadores y/o hijos Exento',
            self::AJUSTE_HORAS_EXTRA_EXENTO => 'Ajuste en Horas extra Exento',
            self::AJUSTE_HORAS_EXTRA_GRAVADO => 'Ajuste en Horas extra Gravado',
            self::AJUSTE_PRIMA_DOMINICAL_EXENTO => 'Ajuste en Prima dominical Exento',
            self::AJUSTE_PRIMA_DOMINICAL_GRAVADO => 'Ajuste en Prima dominical Gravado',
            self::AJUSTE_PRIMA_VACACIONAL_EXENTO => 'Ajuste en Prima vacacional Exento',
            self::AJUSTE_PRIMA_VACACIONAL_GRAVADO => 'Ajuste en Prima vacacional Gravado',
            self::AJUSTE_PRIMA_ANTIGUEDAD_EXENTO => 'Ajuste en Prima por antigüedad Exento',
            self::AJUSTE_PRIMA_ANTIGUEDAD_GRAVADO => 'Ajuste en Prima por antigüedad Gravado',
            self::AJUSTE_PAGOS_SEPARACION_EXENTO => 'Ajuste en Pagos por separación Exento',
            self::AJUSTE_PAGOS_SEPARACION_GRAVADO => 'Ajuste en Pagos por separación Gravado',
            self::AJUSTE_SEGURO_RETIRO_EXENTO => 'Ajuste en Seguro de retiro Exento',
            self::AJUSTE_INDEMNIZACIONES_EXENTO => 'Ajuste en Indemnizaciones Exento',
            self::AJUSTE_INDEMNIZACIONES_GRAVADO => 'Ajuste en Indemnizaciones Gravado',
            self::AJUSTE_REEMBOLSO_FUNERAL_EXENTO => 'Ajuste en Reembolso por funeral Exento',
            self::AJUSTE_CUOTAS_SEGURIDAD_SOCIAL_PATRON_EXENTO => 'Ajuste en Cuotas de seguridad social pagadas por el patrón Exento',
            self::AJUSTE_COMISIONES_GRAVADO => 'Ajuste en Comisiones Gravado',
            self::AJUSTE_VALES_DESPENSA_EXENTO => 'Ajuste en Vales de despensa Exento',
            self::AJUSTE_VALES_RESTAURANTE_EXENTO => 'Ajuste en Vales de restaurante Exento',
            self::AJUSTE_VALES_GASOLINA_EXENTO => 'Ajuste en Vales de gasolina Exento',
            self::AJUSTE_VALES_ROPA_EXENTO => 'Ajuste en Vales de ropa Exento',
            self::AJUSTE_AYUDA_RENTA_EXENTO => 'Ajuste en Ayuda para renta Exento',
            self::AJUSTE_AYUDA_ARTICULOS_ESCOLARES_EXENTO => 'Ajuste en Ayuda para artículos escolares Exento',
            self::AJUSTE_AYUDA_ANTEOJOS_EXENTO => 'Ajuste en Ayuda para anteojos Exento',
            self::AJUSTE_AYUDA_TRANSPORTE_EXENTO => 'Ajuste en Ayuda para transporte Exento',
            self::AJUSTE_AYUDA_GASTOS_FUNERAL_EXENTO => 'Ajuste en Ayuda para gastos de funeral Exento',
            self::AJUSTE_OTROS_INGRESOS_SALARIOS_EXENTO => 'Ajuste en Otros ingresos por salarios Exento',
            self::AJUSTE_OTROS_INGRESOS_SALARIOS_GRAVADO => 'Ajuste en Otros ingresos por salarios Gravado',
            self::AJUSTE_JUBILACION_EXHIBICION_EXENTO => 'Ajuste en Jubilaciones, pensiones o haberes de retiro en una sola exhibición Exento',
            self::AJUSTE_JUBILACION_EXHIBICION_GRAVADO => 'Ajuste en Jubilaciones, pensiones o haberes de retiro en una sola exhibición Gravado',
            self::AJUSTE_PAGOS_SEPARACION_ACUMULABLE => 'Ajuste en Pagos por separación Acumulable',
            self::AJUSTE_PAGOS_SEPARACION_NO_ACUMULABLE => 'Ajuste en Pagos por separación No acumulable',
            self::AJUSTE_JUBILACION_PARCIALIDADES_EXENTO => 'Ajuste en Jubilaciones, pensiones o haberes de retiro en parcialidades Exento',
            self::AJUSTE_JUBILACION_PARCIALIDADES_GRAVADO => 'Ajuste en Jubilaciones, pensiones o haberes de retiro en parcialidades Gravado',
            self::AJUSTE_SUBSIDIO_EMPLEO => 'Ajuste en Subsidio para el empleo (efectivamente entregado al trabajador)',
            self::AJUSTE_INGRESOS_ACCIONES_EXENTO => 'Ajuste en Ingresos en acciones o títulos valor que representan bienes Exento',
            self::AJUSTE_INGRESOS_ACCIONES_GRAVADO => 'Ajuste en Ingresos en acciones o títulos valor que representan bienes Gravado',
            self::AJUSTE_ALIMENTACION_EXENTO => 'Ajuste en Alimentación Exento',
            self::AJUSTE_ALIMENTACION_GRAVADO => 'Ajuste en Alimentación Gravado',
            self::AJUSTE_HABITACION_EXENTO => 'Ajuste en Habitación Exento',
            self::AJUSTE_HABITACION_GRAVADO => 'Ajuste en Habitación Gravado',
            self::AJUSTE_PREMIOS_ASISTENCIA => 'Ajuste en Premios por asistencia',
            self::AJUSTE_PAGOS_DISTINTOS => 'Ajuste en Pagos distintos a los listados y que no deben considerarse como ingreso por sueldos, salarios o ingresos asimilados.',
            self::AJUSTE_VIATICOS_GRAVADOS => 'Ajuste en Viáticos gravados',
            self::AJUSTE_VIATICOS_ENTREGADOS => 'Ajuste en Viáticos (entregados al trabajador)',
            self::AJUSTE_FONDO_AHORRO_GRAVADO => 'Ajuste en Fondo de ahorro Gravado',
            self::AJUSTE_CAJA_AHORRO_GRAVADO => 'Ajuste en Caja de ahorro Gravado',
            self::AJUSTE_PRIMA_SEGURO_VIDA_GRAVADO => 'Ajuste en Prima de Seguro de vida Gravado',
            self::AJUSTE_SEGURO_GASTOS_MEDICOS_MAYORES_GRAVADO => 'Ajuste en Seguro de Gastos Médicos Mayores Gravado',
            self::AJUSTE_SUBSIDIOS_INCAPACIDAD_GRAVADO => 'Ajuste en Subsidios por incapacidad Gravado',
            self::AJUSTE_BECAS_GRAVADO => 'Ajuste en Becas para trabajadores y/o hijos Gravado',
            self::AJUSTE_SEGURO_RETIRO_GRAVADO => 'Ajuste en Seguro de retiro Gravado',
            self::AJUSTE_VALES_DESPENSA_GRAVADO => 'Ajuste en Vales de despensa Gravado',
            self::AJUSTE_VALES_RESTAURANTE_GRAVADO => 'Ajuste en Vales de restaurante Gravado',
            self::AJUSTE_VALES_GASOLINA_GRAVADO => 'Ajuste en Vales de gasolina Gravado',
            self::AJUSTE_VALES_ROPA_GRAVADO => 'Ajuste en Vales de ropa Gravado',
            self::AJUSTE_AYUDA_RENTA_GRAVADO => 'Ajuste en Ayuda para renta Gravado',
            self::AJUSTE_AYUDA_ARTICULOS_ESCOLARES_GRAVADO => 'Ajuste en Ayuda para artículos escolares Gravado',
            self::AJUSTE_AYUDA_ANTEOJOS_GRAVADO => 'Ajuste en Ayuda para anteojos Gravado',
            self::AJUSTE_AYUDA_TRANSPORTE_GRAVADO => 'Ajuste en Ayuda para transporte Gravado',
            self::AJUSTE_AYUDA_GASTOS_FUNERAL_GRAVADO => 'Ajuste en Ayuda para gastos de funeral Gravado',
            self::AJUSTE_INGRESOS_ASIMILADOS_GRAVADOS => 'Ajuste a ingresos asimilados a salarios gravados',
            self::AJUSTE_SUELDOS_SALARIOS_GRAVADOS => 'Ajuste a ingresos por sueldos y salarios gravados',
            self::AJUSTE_VIATICOS_EXENTOS => 'Ajuste en Viáticos exentos',
            self::ISR_RETENIDO_EJERCICIO_ANTERIOR => 'ISR Retenido de ejercicio anterior',
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_GRAVADO => 'Ajuste a pagos por gratificaciones, primas, compensaciones, recompensas u otros a extrabajadores derivados de jubilación en parcialidades, gravados',
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_JUDICIAL_GRAVADO => 'Ajuste a pagos que se realicen a extrabajadores que obtengan una jubilación en parcialidades derivados de la ejecución de una resolución judicial o de un laudo gravados',
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_JUDICIAL_EXENTO => 'Ajuste a pagos que se realicen a extrabajadores que obtengan una jubilación en parcialidades derivados de la ejecución de una resolución judicial o de un laudo exentos',
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_JUDICIAL_GRAVADO => 'Ajuste a pagos que se realicen a extrabajadores que obtengan una jubilación en una sola exhibición derivados de la ejecución de una resolución judicial o de un laudo gravados',
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_JUDICIAL_EXENTO => 'Ajuste a pagos que se realicen a extrabajadores que obtengan una jubilación en una sola exhibición derivados de la ejecución de una resolución judicial o de un laudo exentos',
            self::AJUSTE_SUBSIDIO_CAUSADO => 'Ajuste al Subsidio Causado',
        };
    }

    public function category(): string
    {
        return match($this) {
            self::SEGURIDAD_SOCIAL,
            self::APORTACIONES_RETIRO_CESANTIA_VEJEZ,
            self::APORTACIONES_FONDO_VIVIENDA,
            self::CUOTAS_OBRERO_PATRONALES => 'obligatorias_ley',

            self::ISR,
            self::IMPUESTOS_LOCALES,
            self::ISR_RETENIDO_EJERCICIO_ANTERIOR => 'impuestos',

            self::PENSION_ALIMENTICIA,
            self::RENTA,
            self::PRESTAMOS_FONAVI,
            self::PAGO_CREDITO_VIVIENDA,
            self::PAGO_ABONOS_INFONACOT => 'obligaciones_personales',

            self::ANTICIPO_SALARIOS,
            self::PAGOS_EXCESO_TRABAJADOR,
            self::DESCUENTO_INCAPACIDAD,
            self::AUSENCIA_AUSENTISMO => 'anticipos_descuentos',

            self::CUOTAS_SINDICALES,
            self::CUOTAS_SOCIEDADES_COOPERATIVAS_CAJAS_AHORRO,
            self::APORTACIONES_VOLUNTARIAS => 'cuotas_aportaciones',

            self::ERRORES,
            self::PERDIDAS,
            self::AVERIAS,
            self::ADQUISICION_ARTICULOS_EMPRESA => 'errores_perdidas',

            self::AJUSTE_AGUINALDO_EXENTO,
            self::AJUSTE_AGUINALDO_GRAVADO,
            self::AJUSTE_PTU_EXENTO,
            self::AJUSTE_PTU_GRAVADO,
            self::AJUSTE_REEMBOLSO_MEDICO_EXENTO,
            self::AJUSTE_FONDO_AHORRO_EXENTO,
            self::AJUSTE_CAJA_AHORRO_EXENTO,
            self::AJUSTE_CONTRIBUCIONES_PATRON_EXENTO,
            self::AJUSTE_PREMIOS_PUNTUALIDAD_GRAVADO,
            self::AJUSTE_PRIMA_SEGURO_VIDA_EXENTO,
            self::AJUSTE_SEGURO_GASTOS_MEDICOS_MAYORES_EXENTO,
            self::AJUSTE_CUOTAS_SINDICALES_PATRON_EXENTO,
            self::AJUSTE_SUBSIDIOS_INCAPACIDAD_EXENTO,
            self::AJUSTE_BECAS_EXENTO,
            self::AJUSTE_HORAS_EXTRA_EXENTO,
            self::AJUSTE_HORAS_EXTRA_GRAVADO,
            self::AJUSTE_PRIMA_DOMINICAL_EXENTO,
            self::AJUSTE_PRIMA_DOMINICAL_GRAVADO,
            self::AJUSTE_PRIMA_VACACIONAL_EXENTO,
            self::AJUSTE_PRIMA_VACACIONAL_GRAVADO,
            self::AJUSTE_PRIMA_ANTIGUEDAD_EXENTO,
            self::AJUSTE_PRIMA_ANTIGUEDAD_GRAVADO,
            self::AJUSTE_PAGOS_SEPARACION_EXENTO,
            self::AJUSTE_PAGOS_SEPARACION_GRAVADO,
            self::AJUSTE_SEGURO_RETIRO_EXENTO,
            self::AJUSTE_INDEMNIZACIONES_EXENTO,
            self::AJUSTE_INDEMNIZACIONES_GRAVADO,
            self::AJUSTE_REEMBOLSO_FUNERAL_EXENTO,
            self::AJUSTE_CUOTAS_SEGURIDAD_SOCIAL_PATRON_EXENTO,
            self::AJUSTE_COMISIONES_GRAVADO,
            self::AJUSTE_VALES_DESPENSA_EXENTO,
            self::AJUSTE_VALES_RESTAURANTE_EXENTO,
            self::AJUSTE_VALES_GASOLINA_EXENTO,
            self::AJUSTE_VALES_ROPA_EXENTO,
            self::AJUSTE_AYUDA_RENTA_EXENTO,
            self::AJUSTE_AYUDA_ARTICULOS_ESCOLARES_EXENTO,
            self::AJUSTE_AYUDA_ANTEOJOS_EXENTO,
            self::AJUSTE_AYUDA_TRANSPORTE_EXENTO,
            self::AJUSTE_AYUDA_GASTOS_FUNERAL_EXENTO,
            self::AJUSTE_OTROS_INGRESOS_SALARIOS_EXENTO,
            self::AJUSTE_OTROS_INGRESOS_SALARIOS_GRAVADO,
            self::AJUSTE_JUBILACION_EXHIBICION_EXENTO,
            self::AJUSTE_JUBILACION_EXHIBICION_GRAVADO,
            self::AJUSTE_PAGOS_SEPARACION_ACUMULABLE,
            self::AJUSTE_PAGOS_SEPARACION_NO_ACUMULABLE,
            self::AJUSTE_JUBILACION_PARCIALIDADES_EXENTO,
            self::AJUSTE_JUBILACION_PARCIALIDADES_GRAVADO,
            self::AJUSTE_SUBSIDIO_EMPLEO,
            self::AJUSTE_INGRESOS_ACCIONES_EXENTO,
            self::AJUSTE_INGRESOS_ACCIONES_GRAVADO,
            self::AJUSTE_ALIMENTACION_EXENTO,
            self::AJUSTE_ALIMENTACION_GRAVADO,
            self::AJUSTE_HABITACION_EXENTO,
            self::AJUSTE_HABITACION_GRAVADO,
            self::AJUSTE_PREMIOS_ASISTENCIA,
            self::AJUSTE_PAGOS_DISTINTOS,
            self::AJUSTE_VIATICOS_GRAVADOS,
            self::AJUSTE_VIATICOS_ENTREGADOS,
            self::AJUSTE_FONDO_AHORRO_GRAVADO,
            self::AJUSTE_CAJA_AHORRO_GRAVADO,
            self::AJUSTE_PRIMA_SEGURO_VIDA_GRAVADO,
            self::AJUSTE_SEGURO_GASTOS_MEDICOS_MAYORES_GRAVADO,
            self::AJUSTE_SUBSIDIOS_INCAPACIDAD_GRAVADO,
            self::AJUSTE_BECAS_GRAVADO,
            self::AJUSTE_SEGURO_RETIRO_GRAVADO,
            self::AJUSTE_VALES_DESPENSA_GRAVADO,
            self::AJUSTE_VALES_RESTAURANTE_GRAVADO,
            self::AJUSTE_VALES_GASOLINA_GRAVADO,
            self::AJUSTE_VALES_ROPA_GRAVADO,
            self::AJUSTE_AYUDA_RENTA_GRAVADO,
            self::AJUSTE_AYUDA_ARTICULOS_ESCOLARES_GRAVADO,
            self::AJUSTE_AYUDA_ANTEOJOS_GRAVADO,
            self::AJUSTE_AYUDA_TRANSPORTE_GRAVADO,
            self::AJUSTE_AYUDA_GASTOS_FUNERAL_GRAVADO,
            self::AJUSTE_INGRESOS_ASIMILADOS_GRAVADOS,
            self::AJUSTE_SUELDOS_SALARIOS_GRAVADOS,
            self::AJUSTE_VIATICOS_EXENTOS,
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_GRAVADO,
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_JUDICIAL_GRAVADO,
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_PARCIALIDADES_JUDICIAL_EXENTO,
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_JUDICIAL_GRAVADO,
            self::AJUSTE_PAGOS_EXTRABAJADORES_JUBILACION_EXHIBICION_JUDICIAL_EXENTO,
            self::AJUSTE_SUBSIDIO_CAUSADO => 'ajustes',

            self::OTROS => 'otros',
        };
    }

    public function isAdjustment(): bool
    {
        return str_starts_with($this->value, '0') && (int)$this->value >= 24;
    }

    public function isRegular(): bool
    {
        return !$this->isAdjustment();
    }

    public function isTaxRelated(): bool
    {
        return in_array($this, [
            self::ISR,
            self::IMPUESTOS_LOCALES,
            self::ISR_RETENIDO_EJERCICIO_ANTERIOR,
        ]);
    }

    public function isSocialSecurity(): bool
    {
        return in_array($this, [
            self::SEGURIDAD_SOCIAL,
            self::APORTACIONES_RETIRO_CESANTIA_VEJEZ,
            self::APORTACIONES_FONDO_VIVIENDA,
            self::CUOTAS_OBRERO_PATRONALES,
        ]);
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

    public static function getRegularDeductions(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isRegular();
        });
    }

    public static function getAdjustments(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isAdjustment();
        });
    }

    public static function getTaxRelated(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isTaxRelated();
        });
    }

    public static function getSocialSecurity(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isSocialSecurity();
        });
    }

    public static function isValid(string $code): bool
    {
        return !is_null(self::tryFrom($code));
    }

    public static function getDescriptionByCode(string $code): ?string
    {
        $deduction = self::tryFrom($code);
        return $deduction ? $deduction->name() : null;
    }

    public static function getCategories(): array
    {
        return [
            'obligatorias_ley' => 'Deducciones Obligatorias por Ley',
            'impuestos' => 'Impuestos',
            'obligaciones_personales' => 'Obligaciones Personales',
            'anticipos_descuentos' => 'Anticipos y Descuentos',
            'cuotas_aportaciones' => 'Cuotas y Aportaciones',
            'errores_perdidas' => 'Errores y Pérdidas',
            'ajustes' => 'Ajustes',
            'otros' => 'Otros',
        ];
    }

    public static function getDeductionTypes(): array
    {
        $deductionTypes = [];
        foreach (self::cases() as $case) {
            $deductionTypes[$case->value] = $case->name();
        }
        return $deductionTypes;
    }
}