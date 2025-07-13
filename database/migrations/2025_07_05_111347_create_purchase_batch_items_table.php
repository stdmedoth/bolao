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
    Schema::create('purchase_batch_items', function (Blueprint $table) {
      $table->id();
      $table->string('gambler_name')->nullable();
      $table->string('gambler_phone')->nullable();
      $table->string('numbers')->nullable();
      $table->string('quantity')->nullable();
      $table->string('status')->nullable();
      $table->string('round')->nullable();
      $table->string('user_id')->nullable();

      $table->string('identifier')->nullable();
      $table->string('price')->nullable();
      $table->string('game_id')->nullable();
      $table->string('seller_id')->nullable();
      $table->string('paid_by_user_id')->nullable();

      // Foreign key to purchase_batches table
      $table->foreignId('purchase_batch_id')->constrained('purchase_batches')->onDelete('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchase_batch_items');
  }
};
