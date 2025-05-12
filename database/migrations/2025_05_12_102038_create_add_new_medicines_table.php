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
        Schema::create('add_new_medicines', function (Blueprint $table) {
            $table->id();
            $table->string('Name');
            $table->string('category');
            $table->string('manufacturer');
            $table->integer('stock');
            $table->decimal('price',8,2);
            $table->date('expiry_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_new_medicines');
    }
};
