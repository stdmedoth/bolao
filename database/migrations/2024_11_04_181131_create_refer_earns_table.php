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
    Schema::create('refer_earns', function (Blueprint $table) {
      $table->id();

      $table->unsignedBigInteger('refer_user_id');
      $table->unsignedBigInteger('invited_user_id');
      $table->boolean('invited_user_bought')->default(false);
      $table->boolean('earn_paid')->default(false);

      $table->foreign('refer_user_id')->references('id')->on('users');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('refer_earns');
  }
};
