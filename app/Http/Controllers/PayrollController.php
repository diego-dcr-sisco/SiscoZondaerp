<?php

namespace App\Http\Controllers;

use App\Enums\DeductionType;
use App\Enums\PerceptionType;
use App\Models\InvoiceCustomer;
use App\Models\Payroll;
use App\Models\PayrollPerception;
use App\Models\PayrollDeduction;
use App\Models\PayrollOtherPayment;

use App\Services\FacturamaService as FacturamaService;

use App\Enums\Periodicity;
use App\Enums\TaxRegime;
use App\Enums\Month;
use App\Enums\CfdiUsage;
use App\Enums\PositionRisks;
use App\Enums\PaymentForm;
use App\Enums\PayrollType;
use App\Enums\PaymentMethod;

use Carbon\Carbon;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PayrollController extends Controller
{
    protected $navigation;
    protected $contract_types;

    protected $facturama_user = 'DiegoChaconRivera';

    protected $facturama_password = 's1sc0.d3vp';

    private function getFiscalData()
    {
        $data = [];
        //$users = User::with('roleData')->get();
        /*foreach ($users as $user) {
            $worker_data = $user->roleData;
            $model = ($user->role_id != 3) ? Administrative::class : Technician::class;

            $data[] = [
                'model' => $model,
                'id' => $user->id,
                'rfc' => $worker_data->rfc ?? '',
                'curp' => $worker_data->curp ?? '',
                'nss' => $worker_data->nss ?? '',
                'salary' => $worker_data->salary ?? '',
                'name' => $user->name,
                'regime' => '',
                'department' => '',
                'position' => '',
            ];
        }*/

        $taxpayers = InvoiceCustomer::where('type', 'worker')->get();
        foreach ($taxpayers as $taxpayer) {
            $data[] = [
                'model' => InvoiceCustomer::class,
                'id' => $taxpayer->id,
                'rfc' => $taxpayer->rfc ?? '',
                'curp' => $taxpayer->curp ?? '',
                'nss' => $taxpayer->nss ?? '',
                'salary' => $taxpayer->salary_daily ?? '',
                'name' => $taxpayer->name,
                'tax_regime' => $taxpayer->tax_system,
                'department' => $taxpayer->department ?? '',
                'position' => $taxpayer->position ?? '',
                'zip_code' => $taxpayer->zip_code ?? '',
            ];
        }

        return collect($data);
    }

    public function __construct()
    {
        $this->navigation = [
            'Dashboard' => route('invoices.dashboard'),
            'Contribuyentes' => route('invoices.customers'),
            'Conceptos' => route('invoices.concepts'),
            'Facturas' => route('invoices.index'),
            'Notas de credito' => route('invoices.credit-notes.index'),
            'Complementos de pago' => route('invoices.payments.index'),
            'Nomina' => route('payrolls.index'),
            'Ordenes de Servicio' => route('order.index'),
            'Contratos' => route('contract.index'),
        ];

        $this->contract_types = [
            '01' => 'Contrato de trabajo por tiempo indeterminado',
            '02' => 'Contrato de trabajo para obra determinada',
            '03' => 'Contrato de trabajo por tiempo determinado',
            '04' => 'Contrato de trabajo por temporada',
            '05' => 'Contrato de trabajo sujeto a prueba',
            '06' => 'Contrato de trabajo con capacitación inicial',
            '07' => 'Modalidad de contratación por pago de hora laborada',
            '08' => 'Modalidad de trabajo por comisión laboral',
            '09' => 'Modalidades de contratación que no se encuentran en la LFT',
            '10' => 'Jubilación, pensión, retiro',
        ];
    }

    public function index()
    {
        $payrolls = Payroll::all();

        $navigation = $this->navigation;
        //dd($payrolls);
        return view('invoices.payrolls.index', compact('payrolls', 'navigation'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sat_config = config('services.sat');
        
        $nextId = Payroll::max('id') + 1;
        $folio = str_pad($nextId, 6, '0', STR_PAD_LEFT);

        $periodicities = Periodicity::cases();
        $tax_regimes = TaxRegime::cases();
        $months = Month::cases();
        $cfdi_usage = CfdiUsage::cases();
        $position_risks = PositionRisks::cases();
        $perceptions = PerceptionType::options();
        $deductions = DeductionType::options();
        $payment_forms = PaymentForm::cases();
        $payroll_types = PayrollType::cases();
        $payment_methods = PaymentMethod::cases();
        $users = $this->getFiscalData();
        $contract_types = $this->contract_types;


        //dd($users);

        /*
        "model" => "App\Models\InvoiceCustomer"
      "id" => 2
      "rfc" => "JORG000000KOC"
      "curp" => "JORG000000HSLHVG9"
      "nss" => "123456789001"
      "salary" => ""
      "name" => "Jorge Mota"
      "tax_regime" => "612"
      "department" => "Sistemas"
      "position" => "Desarrollador"

      > employee_number
      > contract_type
      > frequency_payment
      > start_date_labor_relations -> start-date
        */

        return view(
            'invoices.payrolls.create',
            compact(
                'sat_config',
                'folio',
                'periodicities',
                'tax_regimes',
                'months',
                'cfdi_usage',
                'position_risks',
                'perceptions',
                'deductions',
                'users',
                'payment_forms',
                'payroll_types',
                'contract_types',
                'payment_methods'
                //'navigation'
            )
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): mixed
    {
        //dd($request->all());
        // Validar que la fecha final sea mayor o igual a la inicial
        [$startDate, $endDate] = array_map(function ($d) {
            return Carbon::createFromFormat('d/m/Y', trim($d));
        }, explode(' - ', $request->input('date_range')));

        $initialDate = $startDate;
        $finalDate = $endDate;

        if ($finalDate->lt($initialDate)) {
            return back()->withErrors([
                'date_range' => 'La fecha final debe ser mayor o igual a la fecha inicial'
            ])->withInput();
        }

        // Validar que los días pagados sean consistentes con las fechas
        $calculatedDays = $initialDate->diffInDays($finalDate) + 1;
        if ($request->days_paid > $calculatedDays) {
            return back()->with('error', 'Los días pagados (' . $request->days_paid . ') exceden el rango de fechas (' . $calculatedDays . ' días)')->withInput();
        }

        try {
            DB::beginTransaction();

            // Procesar el JSON de nómina si viene del formulario
            $payrollData = [];
            if ($request->has('payroll_data') && $request->payroll_data) {
                $payrollData = json_decode($request->payroll_data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error al decodificar los datos de la nómina: ' . json_last_error_msg());
                }
            }

            // Calcular totales desde los datos del formulario o del JSON
            //$totals = $this->calculateTotals($request, $payrollData);

            // Crear la nómina con los campos del nuevo modelo
            $payroll = Payroll::create([
                'folio' => $request->folio,
                'expedition_place' => $request->expedition_place,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'status' => 'draft',
                'payroll_type' => $request->payroll_type,
                'cfdi_type' => 'N', // Siempre 'N' para nómina
                'cfdi_use' => $request->cfdi_use,

                // Employer
                'employer_registration' => $request->employer_registration ?? 'default123', // Ajusta según tu lógica

                // Employee
                'employee_name' => $request->employee_name,
                'employee_rfc' => $request->employee_rfc,
                'employee_curp' => $request->employee_curp,
                'employee_nss' => $request->employee_nss,
                'employee_zip_code' => $request->employee_zip_code,
                'employee_daily_salary' => $request->employee_daily_salary,

                // Fechas y periodo
                'initial_payment_date' => $initialDate->format('Y-m-d'),
                'final_payment_date' => $finalDate->format('Y-m-d'),
                'month' => $request->month,
                'days_paid' => $request->days_paid,

                // Datos laborales
                'position_risk' => $request->position_risk,
                'contract_type' => $request->contract_type,
                'tax_regime' => $request->tax_regime,
                'frequency_payment' => $request->frequency_payment,
                'employee_number' => $request->employee_number,
                'department' => $request->department,
                'position' => $request->position,
                'start_date_labor_relations' => $request->start_date_labor_relations,

                // Campos adicionales que podrías necesitar
                'facturama_token' => null, // Se llenará al timbrar
                'uuid' => null, // Se llenará al timbrar
            ]);

            $payroll_data = json_decode($request->payroll_data, true);
            //dd($payroll_data);

            // Guardar percepciones, deducciones y otros pagos
            $this->savePerceptions($payroll, $payroll_data['perceptions'] ?? []);
            $this->saveDeductions($payroll, $payroll_data['deductions'] ?? []);
            $this->saveOtherPayments($payroll, $payroll_data['other_payments'] ?? []);

            DB::commit();

            return back()->with('success', 'Nómina guardada correctamente como borrador.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar nómina: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except('payroll_data')
            ]);

            return back()->with('error', 'Error al guardar la nómina: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Calcular totales de la nómina
     */


    /**
     * Guardar percepciones
     */
    private function savePerceptions(Payroll $payroll, array $perceptions = [])
    {
        $existingIds = [];

        foreach ($perceptions as $item) {
            $pp = PayrollPerception::updateOrCreate(
                [
                    'payroll_id' => $payroll->id,
                    'code' => $item['code'] // Clave única para buscar
                ],
                [
                    'perception_type' => $item['type'],
                    'description' => $item['description'],
                    'taxed_amount' => $item['taxed_amount'] ?? 0,
                    'exempt_amount' => $item['exempt_amount'] ?? 0,
                ]
            );

            $existingIds[] = $pp->id;
        }

        PayrollPerception::where('payroll_id', $payroll->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    /**
     * Guardar deducciones
     */
    private function saveDeductions(Payroll $payroll, array $deductions = [])
    {
        $existingIds = [];
        foreach ($deductions as $item) {
            $pd = PayrollDeduction::updateOrCreate(
                [
                    'payroll_id' => $payroll->id,
                    'code' => $item['code'] // Clave única para buscar
                ],
                [
                    'deduction_type' => $item['type'],
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]
            );

            $existingIds[] = $pd->id;
        }

        PayrollDeduction::where('payroll_id', $payroll->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    /**
     * Guardar otros pagos
     */
    private function saveOtherPayments(Payroll $payroll, array $otherPayments = []): void
    {
        $existingIds = [];

        foreach ($otherPayments as $item) {
            $other = PayrollOtherPayment::updateOrCreate(
                [
                    'payroll_id' => $payroll->id,
                    'code' => $item['key'] // Clave única para buscar
                ],
                [
                    'other_payment_type' => $item['type'],
                    'description' => $item['concept'],
                    'amount' => $item['amount'],
                    'employment_subsidy_amount' => $item['subsidy'] ?? null,
                ]
            );

            $existingIds[] = $other->id;
        }
        PayrollOtherPayment::where('payroll_id', $payroll->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payroll = Payroll::with(['perceptions', 'deductions', 'otherPayments'])->findOrFail($id);

        // Formatear el rango de fechas
        $date_range = [
            Carbon::parse($payroll->initial_payment_date)->format('d/m/Y'),
            Carbon::parse($payroll->final_payment_date)->format('d/m/Y')
        ];

        $date_range = implode(' - ', $date_range);

        return view('invoices.payrolls.show', [
            'payroll' => $payroll,
            'date_range' => $date_range,
            'perceptions' => $payroll->perceptions ?? [],
            'deductions' => $payroll->deductions ?? [],
            'otherPayments' => $payroll->otherPayments ?? [],
            'payroll_types' => PayrollType::cases(),
            'cfdi_usage' => CfdiUsage::cases(),
            'months' => Month::cases(),
            'position_risks' => PositionRisks::cases(),
            'tax_regimes' => TaxRegime::cases(),
            'periodicities' => Periodicity::cases(),
            'contract_types' => [
                '01' => 'Contrato de trabajo por tiempo indeterminado',
                '02' => 'Contrato de trabajo para obra determinada',
                '03' => 'Contrato de trabajo por tiempo determinado',
                '04' => 'Contrato de trabajo por temporada',
                '05' => 'Contrato de trabajo sujeto a prueba',
                '06' => 'Contrato de trabajo con capacitación inicial',
                '07' => 'Modalidad de contratación por pago de hora laborada',
                '08' => 'Modalidad de trabajo por comisión laboral',
                '09' => 'Modalidades de contratación donde no existe relación de trabajo',
                '10' => 'Jubilación, pensión, retiro',
                '99' => 'Otro contrato',
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $payroll = Payroll::with(['perceptions', 'deductions', 'otherPayments'])->findOrFail($id);

        $periodicities = Periodicity::cases();
        $tax_regimes = TaxRegime::cases();
        $months = Month::cases();
        $cfdi_usage = CfdiUsage::cases();
        $position_risks = PositionRisks::cases();
        $perceptionsData = PerceptionType::options();
        $deductionsData = DeductionType::options();
        $payment_forms = PaymentForm::cases();
        $payroll_types = PayrollType::cases();
        $payment_methods = PaymentMethod::cases();

        $users = $this->getFiscalData();
        $contract_types = $this->contract_types;

        $perceptions = $payroll->getPerceptionsArrayAttribute();
        $deductions = $payroll->getDeductionsArrayAttribute();
        $otherPayments = $payroll->getOtherPaymentsArrayAttribute();

        $date_range = [
            Carbon::parse($payroll->initial_payment_date)->format('d/m/Y'),
            Carbon::parse($payroll->final_payment_date)->format('d/m/Y')
        ];

        $date_range = implode(' - ', $date_range);

        return view('invoices.payrolls.edit', compact(
            'payroll',
            'periodicities',
            'tax_regimes',
            'months',
            'cfdi_usage',
            'position_risks',
            'perceptionsData',
            'deductionsData',
            'payment_forms',
            'payroll_types',
            'users',
            'contract_types',
            'perceptions',
            'deductions',
            'otherPayments',
            'date_range',
            'payment_methods'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //dd($request->all());
        // Validar que la fecha final sea mayor o igual a la inicial
        [$startDate, $endDate] = array_map(function ($d) {
            return Carbon::createFromFormat('d/m/Y', trim($d));
        }, explode(' - ', $request->input('date_range')));

        $initialDate = $startDate;
        $finalDate = $endDate;

        if ($finalDate->lt($initialDate)) {
            return back()->withErrors([
                'date_range' => 'La fecha final debe ser mayor o igual a la fecha inicial'
            ])->withInput();
        }

        // Validar que los días pagados sean consistentes con las fechas
        $calculatedDays = $initialDate->diffInDays($finalDate) + 1;
        if ($request->days_paid > $calculatedDays) {
            return back()->with('error', 'Los días pagados (' . $request->days_paid . ') exceden el rango de fechas (' . $calculatedDays . ' días)')->withInput();
        }

        try {
            DB::beginTransaction();

            // Procesar el JSON de nómina si viene del formulario
            $payrollData = [];
            if ($request->has('payroll_data') && $request->payroll_data) {
                $payrollData = json_decode($request->payroll_data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error al decodificar los datos de la nómina: ' . json_last_error_msg());
                }
            }

            // Calcular totales desde los datos del formulario o del JSON
            //$totals = $this->calculateTotals($request, $payrollData);

            // Crear la nómina con los campos del nuevo modelo
            $payroll = Payroll::updateOrCreate(
                [
                    'id' => $id,
                ],
                [
                    'folio' => $request->folio,
                    'expedition_place' => $request->expedition_place,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'status' => 'draft',
                    'payroll_type' => $request->payroll_type,
                    'cfdi_type' => 'N', // Siempre 'N' para nómina
                    'cfdi_use' => $request->cfdi_use,

                    // Employer
                    'employer_registration' => $request->employer_registration ?? 'default123', // Ajusta según tu lógica

                    // Employee
                    'employee_name' => $request->employee_name,
                    'employee_rfc' => $request->employee_rfc,
                    'employee_curp' => $request->employee_curp,
                    'employee_nss' => $request->employee_nss,
                    'employee_zip_code' => $request->employee_zip_code,
                    'employee_daily_salary' => $request->employee_daily_salary,

                    // Fechas y periodo
                    'initial_payment_date' => $initialDate->format('Y-m-d'),
                    'final_payment_date' => $finalDate->format('Y-m-d'),
                    'month' => $request->month,
                    'days_paid' => $request->days_paid,

                    // Datos laborales
                    'position_risk' => $request->position_risk,
                    'contract_type' => $request->contract_type,
                    'tax_regime' => $request->tax_regime,
                    'frequency_payment' => $request->frequency_payment,
                    'employee_number' => $request->employee_number,
                    'department' => $request->department,
                    'position' => $request->position,
                    'start_date_labor_relations' => $request->start_date_labor_relations,

                    // Campos adicionales que podrías necesitar
                    'facturama_token' => null, // Se llenará al timbrar
                    'uuid' => null, // Se llenará al timbrar
                ]
            );

            $payroll_data = json_decode($request->payroll_data, true);
            //dd($payroll_data);

            // Guardar percepciones, deducciones y otros pagos
            $this->savePerceptions($payroll, $payroll_data['perceptions'] ?? []);
            $this->saveDeductions($payroll, $payroll_data['deductions'] ?? []);
            $this->saveOtherPayments($payroll, $payroll_data['other_payments'] ?? []);

            DB::commit();

            return back()->with('success', 'Nómina ' . $payroll->folio . ' actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar nómina: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except('payroll_data')
            ]);

            return back()->with('error', 'Error al guardar la nómina: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function stampPayroll($id)
    {
        try {
            $fac_service = new FacturamaService($this->facturama_user, $this->facturama_password);
            $response = $fac_service->createPayroll($id);

            if ($response['success'] && $response['data'] != null) {
                $data = $response['data'];

                $payroll = Payroll::find($id);
                $payroll->update([
                    'status' => 'stamped',
                    'facturama_token' => $data->Id,
                    'uuid' => $data->Complement->TaxStamp->Uuid,
                    //'stamped_date' => $data->Complement->TaxStamp->Date,
                    //'cfdi_sign' => $data->Complement->TaxStamp->CfdiSign,
                    //'sat_cert_number' => $data->Complement->TaxStamp->SatCertNumber,
                    //'sat_sign' => $data->Complement->TaxStamp->SatSign,
                    //'rfc_prov_cert' => $data->Complement->TaxStamp->RfcProvCertif,
                    //'csd_serial_number' => $data->CertNumber
                ]);
                return back()->with('success', $response['message']);
            } else {
                return back()->with('error', $response['message']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return back()->with('error', 'Error al timbrar la factura: ' . $e->getMessage());
        }
    }
}
