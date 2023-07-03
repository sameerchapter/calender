<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSchedule extends Model
{
    use HasFactory;

    protected $casts = [
        'staff_id' => 'array'
    ];

    public function foreman()
    {
        $newResource = clone $this;
        return $newResource->setConnection('webapp')->belongsTo('App\Models\User','foreman_id');
    }
}
