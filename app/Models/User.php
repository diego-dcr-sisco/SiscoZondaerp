<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Administrative;
use App\Models\Technician;
use App\Models\UserFile;
use App\Models\UserLocation;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'user';
    protected $fillable = [
        'id',
        'name',
        'nickname',
        'username',
        'email',
        'password',
        'role_id',
        'type_id',
        'status_id',
        'work_department_id',
        'user_file_id',
        'session_token',
        'remember_token',
        'last_latitude',
        'last_longitude',
        'last_location_accuracy',
        'last_location_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_latitude' => 'decimal:8',
        'last_longitude' => 'decimal:8',
        'last_location_accuracy' => 'decimal:2',
        'last_location_at' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function simpleRole()
    {
        return $this->belongsTo(SimpleRole::class, 'role_id');
    }

    public function roleData()
    {
        $model = ($this->role_id != 3) ? Administrative::class : Technician::class;
        return $this->belongsTo($model, 'id', 'user_id');
    }

    public function workDepartment()
    {
        return $this->belongsTo(WorkDepartment::class, 'work_department_id');
    }

    public function contracts()
    {
        return $this->hasMany(UserContract::class, 'user_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(UserFile::class, 'user_id', 'id');
    }

    public function locations()
    {
        return $this->hasMany(UserLocation::class, 'user_id', 'id');
    }

    public function lastLocation()
    {
        return $this->hasOne(UserLocation::class, 'user_id', 'id')
            ->orderBy('recorded_at', 'desc');
    }

    public function directories()
    {
        return $this->hasMany(DirectoryPermission::class, 'user_id', 'id');
    }

    public function dirManagement(string $path) {
        $dir_mgmt = DirectoryManagement::where('path', $path)->first();
        return $dir_mgmt ? $dir_mgmt->is_visible : true;
    }
    
    public function hasDirectory(string $path)
    {
        return $this->directories()->where('path', $path)->exists();
    }

    public function hasDirectoryInside($childPath)
    {
        $parent = $this->directories()->first();
        if ($parent) {
            $parentPath = $parent->path;
            $parentPath = rtrim($parentPath, '/') . '/';
            $childPath = rtrim($childPath, '/') . '/';
            return strpos($childPath, $parentPath) === 0;
        }
        return false;
    }

    public function hasDirectoryPath($path)
    {
        return $this->directories()->exists();
    }

    public function customers()
    {
        return $this->hasManyThrough(
            Customer::class,
            UserCustomer::class,
            'user_id',
            'id',
            'id',
            'customer_id'
        );
    }

    public function hasCustomer($customer_id)
    {
        return $this->customers()->where('customer.id', $customer_id)->exists();
    }

    public function customersControl()
    {
        return $this->hasMany(Customer::class, 'administrative_id', 'id');
    }
    
    public function hasPathInside($pathToCheck)
    {
        // Normalizar la ruta a verificar
        $normalizedPath = Str::finish($pathToCheck, '/');
        
        // Verificar contra cada directorio del usuario
        foreach ($this->directories as $directory) {
            $normalizedBase = Str::finish($directory->path, '/');
            
            if (Str::startsWith($normalizedPath, $normalizedBase)) {
                return true;
            }
        }
        
        return false;
    }
}
