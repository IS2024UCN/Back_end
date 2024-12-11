<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'creator',
        'ISBN_books',
        'ISBN_movies',
        'publisher',
        'release_date',
        'rental_price',
        'initial_stock',
        'available_stock',
        'type',
        'is_enabled'
    ];

    protected $casts = [
        'release_date' => 'date',
        'rental_price' => 'decimal:2',
        'initial_stock' => 'integer',
        'available_stock' => 'integer',
        'is_enabled' => 'boolean'
    ];
}
