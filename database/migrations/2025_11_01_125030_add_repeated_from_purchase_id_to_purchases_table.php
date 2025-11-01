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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('repeated_from_purchase_id')->nullable()->after('paid_by_user_id');
            $table->foreign('repeated_from_purchase_id')->references('id')->on('purchases')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['repeated_from_purchase_id']);
            $table->dropColumn('repeated_from_purchase_id');
        });
    }
};
