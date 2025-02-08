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
    Schema::create('role_users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->enum('level_id', ['admin', 'seller', 'gambler']);
      $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('document')->nullable();
      $table->string('email');
      $table->string('phone');

      $table->float('game_credit')->default(0);
      $table->float('balance')->default(0);
      $table->float('comission_percent')->default(0);

      $table->string('cc_name')->nullable();
      $table->string('cc_number')->nullable();
      $table->string('cc_expiry_month')->nullable();
      $table->string('cc_expiry_year')->nullable();
      $table->string('cc_ccv')->nullable();

      $table->string('postal_code')->nullable();
      $table->string('address_number')->nullable();

      $table->string('pix_key')->nullable();
      $table->enum('pix_key_type', [
        "CPF",
        "CNPJ",
        "EMAIL",
        "PHONE",
        "EVP",
      ])->nullable();

      $table->string('external_finnancial_id')->nullable();

      $table->unsignedBigInteger('role_user_id');
      $table->foreign('role_user_id')->references('id')->on('role_users');

      $table->unsignedBigInteger('invited_by_id')->nullable();

      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
      $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
      $table->string('email')->primary();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->foreignId('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->longText('payload');
      $table->integer('last_activity')->index();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
    Schema::dropIfExists('role_users');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('sessions');
  }
};
