<?php

namespace App\Models;

use App\Enums\PayrollType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facturama_token',
        'uuid',
        'folio',
        'expedition_place',
        'payment_date',
        'payment_method',
        'payroll_type',
        'cfdi_type',
        'cfdi_use',
        'employer_registration',
        'employee_name',
        'employee_rfc',
        'employee_curp',
        'employee_nss',
        'employee_zip_code',
        'employee_daily_salary',
        'employee_number',
        'initial_payment_date',
        'final_payment_date',
        'month',
        'days_paid',
        'position_risk',
        'contract_type',
        'tax_regime',
        'frequency_payment',
        'employee_number',
        'department',
        'position',
        'start_date_labor_relations',
        'total_perceptions',
        'total_deductions',
        'total_other_payments',
        'total',
        'status',
        'stamped_at',
        'created_at',
        'updated_at',
    ];

   public function getPayrollTypeLabelAttribute(): string
    {
        return PayrollType::from($this->payroll_type)->name();
    }

    public function perceptions()
    {
        return $this->hasMany(PayrollPerception::class);
    }

    public function deductions()
    {
        return $this->hasMany(PayrollDeduction::class);
    }

    public function otherPayments()
    {
        return $this->hasMany(PayrollOtherPayment::class);
    }

    public function buildPayrollJson()
    {
        return [
            "NameId" => $this->name_id,
            "ExpeditionPlace" => $this->expedition_place,
            "Folio" => (int)$this->folio,
            "CfdiType" => $this->cfdi_type,
            "PaymentMethod" => $this->payment_method,
            "Receiver" => [
                "Rfc" => $this->receiver_rfc,
                "CfdiUse" => $this->receiver_cfdi_use,
                "Name" => $this->receiver_name,
                "FiscalRegime" => $this->receiver_fiscal_regime,
                "TaxZipCode" => $this->receiver_tax_zip_code
            ],
            "Complemento" => [
                "Payroll" => [
                    "Type" => $this->payroll_type,
                    "DailySalary" => (float)$this->daily_salary,
                    "BaseSalary" => (float)$this->base_salary,
                    "PaymentDate" => $this->payment_date->format('Y-m-d'),
                    "InitialPaymentDate" => $this->initial_payment_date->format('Y-m-d'),
                    "FinalPaymentDate" => $this->final_payment_date->format('Y-m-d'),
                    "DaysPaid" => $this->days_paid,
                    "Issuer" => [
                        "EmployerRegistration" => $this->employer_registration
                    ],
                    "Employee" => [
                        "Curp" => $this->employee_curp,
                        "SocialSecurityNumber" => $this->employee_social_security_number,
                        "PositionRisk" => $this->position_risk,
                        "ContractType" => $this->contract_type,
                        "RegimeType" => $this->regime_type,
                        "Unionized" => $this->unionized,
                        "TypeOfJourney" => $this->type_of_journey,
                        "EmployeeNumber" => $this->employee_number,
                        "Department" => $this->department,
                        "Position" => $this->position,
                        "FrequencyPayment" => $this->frequency_payment,
                        "FederalEntityKey" => $this->federal_entity_key,
                        "DailySalary" => (float)$this->employee_daily_salary,
                        "StartDateLaborRelations" => $this->start_date_labor_relations->format('Y-m-d')
                    ],
                    "Perceptions" => [
                        "Details" => $this->perceptions->map(function($perception) {
                            return [
                                "PerceptionType" => $perception->perception_type,
                                "Code" => $perception->code,
                                "Description" => $perception->description,
                                "TaxedAmount" => (float)$perception->taxed_amount,
                                "ExemptAmount" => (float)$perception->exempt_amount
                            ];
                        })->toArray()
                    ],
                    "Deductions" => [
                        "Details" => $this->deductions->map(function($deduction) {
                            return [
                                "DeduccionType" => $deduction->deduction_type,
                                "Code" => $deduction->code,
                                "Description" => $deduction->description,
                                "Amount" => (float)$deduction->amount
                            ];
                        })->toArray()
                    ],
                    "OtherPayments" => $this->otherPayments->map(function($otherPayment) {
                        $payment = [
                            "OtherPaymentType" => $otherPayment->other_payment_type,
                            "Code" => $otherPayment->code,
                            "Description" => $otherPayment->description,
                            "Amount" => (float)$otherPayment->amount
                        ];
                        
                        if ($otherPayment->employment_subsidy_amount) {
                            $payment["EmploymentSubsidy"] = [
                                "Amount" => (string)$otherPayment->employment_subsidy_amount
                            ];
                        }
                        
                        return $payment;
                    })->toArray()
                ]
            ]
        ];
    }
}