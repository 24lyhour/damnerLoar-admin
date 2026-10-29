<?php

namespace Modules\Partners\Models;

use Illuminate\Database\Eloquent\Model;

class Merchants extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'tenant_type',
        'tenant_id',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'status',
        'logo',
        'latitude',
        'longitude',
        'user_id',
        'hotel_id',
        'transport_id',
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function hotel()
    {
        return $this->hasMany(Hotel::class);
    }

    public function transport()
    {
        return $this->hasMany(Transport::class);
    }
}
