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

      $table->unsignedBigInteger('game_id');
      $table->unsignedBigInteger('user_id');

      $table->unsignedBigInteger('purchase_id')->nullable();
      $table->unsignedBigInteger('game_award_id');
      $table->unsignedBigInteger('game_history_id')->nullable();

      $table->float('amount')->nullable();

      $table->integer('points')->default(0);

      $table->integer('round')->default(1);

      $table->foreign('game_id')->references('id')->on('games');
      $table->foreign('user_id')->references('id')->on('users');
      $table->foreign('game_award_id')->references('id')->on('game_awards');
      $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
      $table->foreign('game_history_id')->references('id')->on('game_histories')->onDelete('set null');

      $table->enum('status', ['PAID', 'PENDING', 'REVOKED',])->default('PENDING');

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
