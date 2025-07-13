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
    Schema::create('purchase_batch_item_messages', function (Blueprint $table) {
      $table->id();

      $table->foreignId('purchase_batch_item_id')->constrained('purchase_batch_items')->onDelete('cascade');
      $table->text('message');
      $table->string('type')->default('error'); // Ex: 'error', 'warning', 'info'

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchase_batch_item_messages');
  }
};
