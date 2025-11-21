<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
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
            'PAY_PURCHASE_COMISSION_WITHDRAWAL',
            'GAME_CREDIT',
            'GAME_CREDIT_REVERSAL',
            'CUSTOM_INCOME',
            'CUSTOM_OUTCOME'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
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
            'PAY_PURCHASE_COMISSION_WITHDRAWAL',
            'GAME_CREDIT',
            'GAME_CREDIT_REVERSAL'
        )");
    }
};
