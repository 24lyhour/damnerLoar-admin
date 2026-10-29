<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            // Same module, so cascading from the merchant is fine.
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);                       // MerchantDocumentType
            $table->string('status', 32)->default('missing'); // MerchantDocumentStatus
            $table->string('file_path')->nullable();           // private storage
            $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size')->nullable();  // bytes
            $table->date('expires_at')->nullable();
            $table->string('note')->nullable();                // e.g. "account ending 3301 · name matches"
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            // One checklist row per document type per merchant.
            $table->unique(['merchant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_documents');
    }
};
