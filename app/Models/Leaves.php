<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaves extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'id','user_id','user_name','from_date','to_date','user_type'
    ];


}
