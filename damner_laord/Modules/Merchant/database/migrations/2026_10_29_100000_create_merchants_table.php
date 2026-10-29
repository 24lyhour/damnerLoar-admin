<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('type', 32)->index();            // MerchantType
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable()->index();
            $table->string('logo')->nullable();
            // Basis points: 1200 = 12.00 %. Overrides the platform default.
            $table->unsignedSmallInteger('commission_rate');
            $table->string('status', 32)->default('pending')->index(); // MerchantStatus
            $table->text('suspended_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            // Admins are deactivated, never hard-deleted, so keep the approver.
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
