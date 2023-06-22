<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;

class Department extends Model
{
    use HasFactory;

    protected $connection="webapp";

    public function contacts()
{
    return $this->hasMany(Contact::class);
}

public function ProjectStatus()
{
    return $this->hasOne(ProjectStatusLabel::class);
}

}
