<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Internal notes on a merchant, visible to admins only (spec 03.2). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['merchant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_notes');
    }
};
