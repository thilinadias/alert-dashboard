<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationRule extends Model
{
    protected $fillable = ['priority', 'rule_type', 'keyword', 'target_severity', 'target_client_id'];

    public function targetClient()
    {
        return $this->belongsTo(Client::class, 'target_client_id');
    }
}
