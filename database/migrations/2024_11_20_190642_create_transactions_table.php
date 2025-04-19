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
    Schema::create('transactions', function (Blueprint $table) {
      $table->id();

      $table->enum('type', [
        'DEPOSIT',
        'WITHDRAWAL',
        'DEPOSIT_REVERSAL',
        'WITHDRAWAL_REVERSAL',
        'REFER_EARN',
        'REFER_EARN_REVERSAL',
        'PAY_PURCHASE',
        'PAY_PURCHASE_WITHDRAWAL',
        'PAY_AWARD',
        'PAY_AWARD_WITHDRAWAL',
        'PAY_PURCHASE_COMISSION',
        'PAY_PURCHASE_COMISSION_WITHDRAWAL'
      ]);

      $table->float('amount');

      $table->string('external_id')->nullable();

      $table->unsignedBigInteger('user_id');
      $table->foreign('user_id')->references('id')->on('users');

      $table->unsignedBigInteger('purchase_id')->nullable();
      $table->foreign('purchase_id')->references('id')->on('purchases');

      $table->unsignedBigInteger('game_id')->nullable();
      $table->foreign('game_id')->references('id')->on('games');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
