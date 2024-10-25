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
    Schema::create('purchases', function (Blueprint $table) {
      $table->id();

      $table->string('numbers');

      $table->string('gambler_phone')->nullable();
      $table->string('gambler_name')->nullable();

      $table->enum('status', ['PAID', 'PENDING', 'CANCELED',]);

      $table->integer('quantity');
      $table->float('price', 8, 2);

      $table->unsignedBigInteger('game_id');
      $table->unsignedBigInteger('user_id');


      $table->foreign('game_id')->references('id')->on('games');
      $table->foreign('user_id')->references('id')->on('users');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchases');
  }
};
