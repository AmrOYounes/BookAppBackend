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
        'publisher_Id',
        'Publish_date',
        'author_Id',
        'Available_units',
        'Unit_price',
        ];
    use HasFactory;

    public function author(){
        return $this->belongsTo(Author::class,'author_Id');
    }

    public function publisher(){
        return $this->belongsTo(Publisher::class,'publisher_Id');
    }

    public function tag(){
        return $this->hasMany(Tag::class,'Book_id');
    }


}
