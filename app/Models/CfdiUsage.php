<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/CfdiUsage.php
class CfdiUsage extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'description', 'type', 'applicable_to', 'active'];
    
    protected $casts = [
        'active' => 'boolean',
    ];
    
    // Relación con clientes
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    
    // Scopes útiles
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    
    public function scopeForPhysical($query)
    {
        return $query->whereIn('applicable_to', ['physical', 'both']);
    }
    
    public function scopeForMoral($query)
    {
        return $query->whereIn('applicable_to', ['moral', 'both']);
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}