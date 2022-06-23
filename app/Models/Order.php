<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
     protected  $fillable = [
         'Book_id',
         'numberOfUnits',
         'buyerName',
         'buyerAdress',
         'phone',
         'purchaseDate',
         'nationalId',
         'paymentMethod',
         'totalPrice',
     ];
    use HasFactory;
    public function  book(){
        return $this->belongsTo(Book::class,'Book_id','Book_id');
    }
}
