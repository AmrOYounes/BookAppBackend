<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookfile extends Model
{
    protected $fillable = ['Book_path','Book_id'];
    use HasFactory;
}
