<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidencePhoto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'service_id',
        'evidence_data',
        'filename',
        'filetype',
        'description',
        'area',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'evidence_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that owns the evidence photo.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the service that owns the evidence photo.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope a query to only include evidence for a specific order.
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to only include evidence for a specific service.
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope a query to only include evidence for a specific area.
     */
    public function scopeForArea($query, $area)
    {
        return $query->where('area', $area);
    }

    /**
     * Get the image base64 data from evidence_data.
     */
    public function getImageBase64Attribute(): string
    {
        return $this->evidence_data['image'] ?? '';
    }

    /**
     * Get the image data without the data URL prefix.
     */
    public function getImageDataAttribute(): string
    {
        $base64 = $this->image_base64;
        if (preg_match('/^data:image\/\w+;base64,/', $base64)) {
            return substr($base64, strpos($base64, ',') + 1);
        }
        return $base64;
    }

    /**
     * Get the image MIME type.
     */
    public function getImageMimeTypeAttribute(): string
    {
        $base64 = $this->image_base64;
        if (preg_match('/^data:(image\/\w+);base64,/', $base64, $matches)) {
            return $matches[1];
        }
        return $this->filetype;
    }

    /**
     * Check if the evidence has a valid image.
     */
    public function getHasValidImageAttribute(): bool
    {
        return !empty($this->image_base64) && 
               preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $this->image_base64);
    }
}