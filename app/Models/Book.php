<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

    protected $primaryKey = 'Book_id';
    protected $hidden = [
        'id',
        'Book_path',

    ];
    protected $fillable = [
        'Book_id',
        'Book_title',
        'Book_publisher',
        'Publish_date',
        'Book_author',
        'Book_path',
        'Available_units',
        'Unit_price',
        ];
    use HasFactory;

    public function author(){
        return $this->hasOne(Author::class);
    }

    public function publisher(){
        return $this->hasOne(Publisher::class);
    }

    public function tag(){
        return $this->hasOne(Tag::class,'Book_id','Book_id');
    }


}
