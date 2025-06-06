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
    Schema::create('game_histories', function (Blueprint $table) {
      $table->id();

      $table->string('description');
      $table->enum('type', ['OPENED', 'ADDING_NUMBER', 'CLOSED', 'FINISHED']);

      $table->string('result_numbers')->nullable();
      $table->string('numbers')->nullable();

      $table->integer('round')->default(1);

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
    Schema::dropIfExists('game_histories');
  }
};
