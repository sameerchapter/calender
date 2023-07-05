<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'id','user_id','token','model'
    ];


}
