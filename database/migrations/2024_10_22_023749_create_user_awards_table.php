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
    Schema::create('user_awards', function (Blueprint $table) {
      $table->id();


      $table->unsignedBigInteger('purchase_id');
      $table->unsignedBigInteger('user_id');

      $table->float('amount')->nullable();

      $table->foreign('purchase_id')->references('id')->on('purchases');
      $table->foreign('user_id')->references('id')->on('users');

      $table->enum('status', ['PAID', 'PENDING', 'REVOKED',]);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_awards');
  }
};
