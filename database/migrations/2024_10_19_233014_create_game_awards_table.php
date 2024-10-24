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
    Schema::create('game_awards', function (Blueprint $table) {
      $table->id();

      $table->enum('condition_type', ['MINIMUM_POINT', 'EXACT_POINT']);
      $table->integer('minimum_point_value')->nullable();

      $table->float('amount')->nullable();

      $table->unsignedBigInteger('game_id');

      $table->foreign('game_id')->references('id')->on('games');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('game_awards');
  }
};
