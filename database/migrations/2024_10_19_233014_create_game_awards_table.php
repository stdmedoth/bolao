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

      $table->string("name");
      $table->enum('condition_type', ['EXACT_POINT', 'LOWEST_POINT', 'WINNER']);

      $table->integer('exact_point_value')->nullable();
      $table->integer('winner_point_value')->nullable();

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
