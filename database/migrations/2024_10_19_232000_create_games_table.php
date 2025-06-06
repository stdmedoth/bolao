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
    Schema::create('games', function (Blueprint $table) {
      $table->id();

      $table->string('game_id');

      $table->string('name');

      $table->float('price', 8, 2);

      $table->datetime('open_at');
      $table->datetime('close_at');

      $table->enum('status', ['OPENED', 'CLOSED', 'FINISHED']);

      $table->integer('round')->default(1);

      $table->boolean('active');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('games');
  }
};
