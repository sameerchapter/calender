<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForemanStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'foreman_id',
        'staff_id',
    ];
    

}
