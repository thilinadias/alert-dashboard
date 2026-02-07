<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = ['name', 'response_time_minutes', 'resolution_time_minutes'];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
