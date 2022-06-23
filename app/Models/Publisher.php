<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $fillable = ['Publisher_name','Establish_date','Is_working'];
    use HasFactory;
}
