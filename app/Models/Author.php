<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = ['First_name','Middle_name','Last_name','Birth_date','Country_of_residence','Death_date','Offical_website'];
    use HasFactory;
}
