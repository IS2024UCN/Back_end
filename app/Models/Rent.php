<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    use HasFactory;

    protected $fillable = [
        'state',
        'totalCost',
        'requestDate',
        'startDate',
        'endDate',
        'product_id',
        'user_id'
    ];

    protected $casts = [
        'requestDate' => 'datetime',
        'startDate' => 'date',
        'endDate' => 'date',
        'totalCost' => 'decimal:2'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
