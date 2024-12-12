<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('creator');
            $table->string('ISBN')->unique();
            $table->string('publisher')->nullable();
            $table->date('release_date')->nullable();
            $table->decimal('rental_price', 8, 2);
            $table->integer('initial_stock');
            $table->integer('available_stock'); 
            $table->boolean('is_enabled')->default(true); 
            $table->string('type'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
