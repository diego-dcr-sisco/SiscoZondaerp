<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceConcept extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_key',
        'name',
        'description',
        'amount',
        'tax_rate',
        'tax_object',
        'unit_code',
        'identification_number'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'tax_rate' => 'float',
    ];

    /**
     * The attributes with default values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'tax_rate' => 0.16,
        'tax_object' => '02',
    ];

    /**
     * Get the tax amount for the concept.
     */
    public function getTaxAmountAttribute(): float
    {
        return $this->amount * $this->tax_rate;
    }

    /**
     * Get the total amount including tax.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount + $this->tax_amount;
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get the formatted tax amount.
     */
    public function getFormattedTaxAmountAttribute(): string
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get the tax rate as percentage.
     */
    public function getTaxRatePercentageAttribute(): string
    {
        return ($this->tax_rate * 100) . '%';
    }

    /**
     * Scope a query to only include concepts with tax.
     */
    public function scopeWithTax($query)
    {
        return $query->where('tax_rate', '>', 0);
    }

    /**
     * Scope a query to only include tax-free concepts.
     */
    public function scopeTaxFree($query)
    {
        return $query->where('tax_rate', 0);
    }

    /**
     * Scope a query to only include concepts by product key.
     */
    public function scopeByProductKey($query, string $productKey)
    {
        return $query->where('product_key', $productKey);
    }

    /**
     * Scope a query to only include concepts that require tax breakdown.
     */
    public function scopeRequiresTaxBreakdown($query)
    {
        return $query->where('tax_object', '02');
    }

    /**
     * Get the unit name from the SAT catalog.
     */
    public function getUnitNameAttribute(): string
    {
        $unidadesSAT = [
            'H87' => 'Pieza',
            'EA' => 'Elemento',
            'KGM' => 'Kilogramo',
            'MTR' => 'Metro',
            'LTR' => 'Litro',
            'DAY' => 'Día',
            'HR' => 'Hora',
            // Agregar más códigos SAT según necesites
        ];

        return $unidadesSAT[$this->unit_code] ?? $this->unit_code;
    }

    /**
     * Get the product key description from SAT catalog.
     */
    public function getProductKeyDescriptionAttribute(): string
    {
        $clavesProductoSAT = [
            '01010101' => 'No existe en el catálogo',
            '81111500' => 'Servicios de desarrollo de software',
            '43201500' => 'Equipo de procesamiento de datos',
            // Agregar más claves SAT según necesites
        ];

        return $clavesProductoSAT[$this->product_key] ?? 'Clave genérica';
    }

    /**
     * Create a new concept from array data.
     */
    public static function createFromArray(array $data): self
    {
        return self::create([
            'product_key' => $data['product_key'] ?? '01010101',
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'tax_rate' => $data['tax_rate'] ?? 0.16,
            'tax_object' => $data['tax_object'] ?? '02',
            'unit_code' => $data['unit_code'] ?? 'H87',
        ]);
    }

    /**
     * Convert the model to an array for CFDI.
     */
    public function toCfdiArray(): array
    {
        return [
            'ClaveProdServ' => $this->product_key,
            'NoIdentificacion' => null,
            'Cantidad' => 1, // Por defecto 1, ajustar según necesidad
            'ClaveUnidad' => $this->unit_code,
            'Unidad' => $this->unit_name,
            'Descripcion' => $this->name,
            'ValorUnitario' => $this->amount,
            'Importe' => $this->amount,
            'Descuento' => null,
            'ObjetoImp' => $this->tax_object,
        ];
    }
}