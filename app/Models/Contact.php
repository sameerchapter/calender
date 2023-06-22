<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection="webapp";

    protected $fillable = [
        'title','company','email','notes','contact','department_id',
    ];
    public function departments()
{
    return $this->hasMany('Department');
}
}
