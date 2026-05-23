<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCatalog extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'product_catalog';

    protected $fillable = [
        'id',
        'biocide_id',
        'purpose_id',
        'linebusiness_id',
        'presentation_id',
        'toxicity_categ_id',
        'metric_id',
        'files_id',
        'image_path',
        'name',
        'business_name',
        'bar_code',
        'description',
        'execution_indications',
        'manufacturer',
        'register_number',
        'validity_date',
        'active_ingredient',
        'per_active_ingredient',
        'dosage',
        'safety_period',
        'residual_effect',
        'purchase_price',
        'selling_price',
        'min_purchase_unit',
        'mult_purchase',
        'subaccount_purchases',
        'subaccount_sales',
        'supplier_name',
        'supplier_phone',
        'supplier_email',
        'is_obsolete',
        'is_toxic',
        'is_selling'
    ];

    public function lineBusiness()
    {
        return $this->belongsTo(LineBusiness::class, 'linebusiness_id');
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }

    public function economicData()
    {
        return $this->belongsTo(EconomicDataProduct::class, 'economic_data_id');
    }

    public function applicationMethods()
    {
        return $this->hasManyThrough(
            ApplicationMethod::class,        // El modelo intermedio (Method)
            Dosage::class,        // El modelo final al que deseas acceder (Dosage)
            'prod_id', // Clave foránea en la tabla intermedia (Method) que apunta al modelo actual (ApplicationMethod)
            'id',          // Clave foránea en la tabla final (Dosage) que apunta al modelo intermedio (Method)
            'id',                 // Clave local en el modelo actual (ApplicationMethod)
            'methd_id'                  // Clave local en el modelo intermedio (Method)
        )->select('application_method.*', 'dosage.prod_id');
    }

    public function hasAppMethod($appMethodId)
    {
        return $this->applicationMethods->contains('id', $appMethodId);
    }

    public function presentation()
    {
        return $this->belongsTo(Presentation::class, 'presentation_id');
    }

    public function toxicityCategory()
    {
        return $this->belongsTo(ToxicityCategories::class, 'toxicity_categ_id');
    }
    public function orderProduct()
    {
        return $this->hasOne(OrderProduct::class, 'product_id');
    }
    public function pests()
    {
        return $this->hasManyThrough(
            PestCatalog::class,
            ProductPest::class,
            'product_id',
            'id',
            'id',
            'pest_id'
        );
    }

    public function hasPest($pestID)
    {
        return $this->pests->contains('id', $pestID);
    }

    public function categories() {
        $categoryIds = $this->pests()->pluck('pest_category_id');
        //$pest_categories = PestCategory::whereIn('id', $categoryIds)->get();
        return PestCategory::whereIn('id', $categoryIds)->get();
    }

    public function files() {
        return $this->hasMany(ProductFile::class, 'product_id');
    }

    public function file($filenameId) {
        return $this->files()->where('filename_id', $filenameId)->first();
    }

    public function lots() {
        return $this->hasMany(Lot::class, 'product_id', 'id');
    }

    public function selectedLots($date) {
        return $this->lots()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get();
    }

    public function metric() {
        return $this->belongsTo(Metric::class, 'metric_id');
    }

    public function biocide() {
        return $this->belongsTo(Biocide::class, 'biocide_id');
    }

    public function movementProducts()
{
    return $this->hasMany(MovementProduct::class, 'product_id');
}
}
