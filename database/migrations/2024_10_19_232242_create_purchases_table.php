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

      $table->enum('status', ['PAID', 'PENDING', 'CANCELED', 'FINISHED']);

      $table->integer('quantity');
      $table->float('price', 8, 2);

      $table->integer('round')->default(1);

      $table->integer('points')->default(0);

      $table->string('identifier')->unique()->nullable();

      $table->boolean('imported')->default(false);

      $table->unsignedBigInteger('game_id');
      $table->unsignedBigInteger('user_id');
      $table->unsignedBigInteger('seller_id');
      $table->unsignedBigInteger('paid_by_user_id')->nullable();


      $table->foreign('game_id')->references('id')->on('games');
      $table->foreign('user_id')->references('id')->on('users');
      $table->foreign('seller_id')->references('id')->on('users');
      $table->foreign('paid_by_user_id')->references('id')->on('users');
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
