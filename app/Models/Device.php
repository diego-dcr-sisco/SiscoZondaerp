<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Device extends Model
{
    use HasFactory;

    protected $table = 'device';
    protected $fillable = [
        'id',
        'type_control_point_id',
        'floorplan_id',
        'application_area_id',
        'product_id',
        'qr',
        'itemnumber',
        'nplan',
        'version',
        'latitude',
        'longitude',
        'map_x',
        'map_y',
        'x_norm',
        'y_norm',
        'img_tamx',
        'img_tamy',
        'color',
        'code',
        'size',
        'created_at',
        'updated_at',
    ];

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    public function products(string $orderId)
    {
        return $this->hasMany(DeviceProduct::class, 'device_id', 'id')->where('order_id', $orderId)->get();
    }

    public function controlPoint()
    {
        return $this->belongsTo(ControlPoint::class, 'type_control_point_id');
    }

    public function floorplan()
    {
        return $this->belongsTo(FloorPlans::class, 'floorplan_id');
    }

    public function applicationArea()
    {
        return $this->belongsTo(ApplicationArea::class, 'application_area_id');
    }

    public function incidents()
    {
        return $this->hasMany(OrderIncidents::class, 'device_id', 'id');
    }

    public function questions()
    {
        return $this->hasManyThrough(
            Question::class,
            ControlPointQuestion::class,
            'control_point_id',
            'id',
            'type_control_point_id',
            'question_id'
        );
    }

    public function hasQuestion($questionId)
    {
        return $this->questions()->where('question.id', $questionId)->exists();
    }

    public function pests($orderId)
    {
        return DevicePest::where('device_id', $this->id)->where('order_id', $orderId)->get();
    }

    public function states($order_id)
    {
        return $this->hasOne(DeviceStates::class, 'device_id')->where('order_id', $order_id);
    }

    public function incidentsByOrder($order_id)
    {
        return $this->incidents()->where('order_id', $order_id)->get();
    }

    public function incident($order_id, $question_id)
    {
        return $this->incidents()->where('order_id', $order_id)->where('question_id', $question_id)->first();
    }

    public function detections()
    {
        return $this->hasMany(DevicePest::class, 'device_id');
    }

    public function deviceProducts()
    {
        return $this->hasMany(DeviceProduct::class, 'device_id');
    }

    public function devicePests()
    {
        return $this->hasMany(DevicePest::class, 'device_id');
    }

    public function deviceStates()
    {
        return $this->hasMany(DeviceStates::class, 'device_id', 'id');
    }

    public function statesForOrder($order_id)
    {
        return $this->deviceStates()->where('order_id', $order_id);
    }
}
